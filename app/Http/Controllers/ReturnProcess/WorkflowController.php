<?php

namespace App\Http\Controllers\ReturnProcess;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowReason;
use App\Models\Sector;
use App\Models\ProcessType;
use App\Models\Level;
use App\Models\WorkflowStep;

class WorkflowController extends Controller
{
    public function index()
    {
        return view('returnProcess.workflows.adminIndex', [

            // 🔹 Fluxos (templates de workflow)
            'templates' => WorkflowTemplate::with('reasons')->paginate(10),

            // 🔹 Motivos
            'reasons' => WorkflowReason::with('template')
                ->orderBy('created_at', 'desc')
                ->paginate(10),

            // 🔹 Setores (usado na criação de steps)
            'sectors' => Sector::orderBy('name')->get(),

            'processTypes' => ProcessType::orderBy('name')->get(),

        ]);
    }
    public function addReason(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'workflow_template_id' => 'required|exists:workflow_templates,id',
        ]);

        \App\Models\WorkflowReason::create($validated);

        // 🔹 Se for AJAX → retorna JSON
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Motivo criado com sucesso!'
            ]);
        }

        // 🔹 Se for form normal → redirect (fallback)
        return redirect()->route('workflows.index')->with('success', 'Motivo criado com sucesso!');
    }
    public function updateReason(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'workflow_template_id' => 'required|exists:workflow_templates,id',
        ]);

        $reason = \App\Models\WorkflowReason::findOrFail($id);
        $reason->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Motivo atualizado com sucesso!'
            ]);
        }

        return redirect()->route('workflows.index')->with('success', 'Motivo atualizado com sucesso!');
    }
    public function storeStep(Request $request)
    {
        $validated = $request->validate([
            'workflow_template_id' => 'required|exists:workflow_templates,id',
            'name' => 'required|string|max:100',
            'order' => 'required|integer|min:1',
            'sector_id' => 'required|exists:sectors,id',
            'required_level_id' => 'required|exists:levels,id',
            'auto_notify' => 'required|boolean',
        ]);

        $step = WorkflowStep::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Etapa criada com sucesso!',
            'step' => $step
        ]);
    }
    public function deleteReason($id)
    {
        $reason = WorkflowReason::find($id);

        if (!$reason) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Motivo já removido ou não encontrado.'
                ], 404);
            }

            return redirect()->route('workflows.index')
                ->with('error', 'Motivo já removido ou não encontrado.');
        }

        $reason->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Motivo excluído com sucesso!'
            ]);
        }

        return redirect()->route('workflows.index')
            ->with('success', 'Motivo excluído com sucesso!');
    }
    public function steps(Request $request, $templateId)
    {
        $template = WorkflowTemplate::with([
            'steps' => fn($q) => $q->orderBy('order'),
            'steps.sector',
            'steps.requiredLevel'
        ])->findOrFail($templateId);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'steps' => $template->steps->map(function ($s) {
                    return [
                        'id' => $s->id,
                        'name' => $s->name,
                        'order' => $s->order,

                        'sector_id' => $s->sector_id,
                        'sector_name' => $s->sector->name ?? '—',

                        'required_level_id' => $s->required_level_id,
                        'required_level_name' => $s->requiredLevel->name ?? '—',
                    ];
                }),
            ]);
        }

        return abort(404);
    }
    public function editStep($id)
    {
        $step = WorkflowStep::with(['sector', 'requiredLevel'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'step' => [
                'id' => $step->id,
                'workflow_template_id' => $step->workflow_template_id,
                'name' => $step->name,
                'order' => $step->order,
                'sector_id' => $step->sector_id,
                'required_level_id' => $step->required_level_id,
                'auto_notify' => $step->auto_notify,
                'next_step_id' => $step->next_step_id,
                'next_on_reject_step_id' => $step->next_on_reject_step_id,
            ]
        ]);
    }
    public function updateStep(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'order' => 'nullable|integer|min:1',
            'sector_id' => 'nullable|exists:sectors,id',
            'required_level_id' => 'nullable|exists:levels,id',
            'workflow_template_id' => 'required|exists:workflow_templates,id',
            'auto_notify' => 'required|boolean',
        ]);

        $step = WorkflowStep::findOrFail($id);
        $step->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Etapa atualizada com sucesso!',
            'step' => $step
        ]);
    }
    public function stepOptions($templateId)
    {
        return response()->json([
            'success' => true,
            'sectors' => Sector::orderBy('name')->get(['id', 'name']),
            'levels' => Level::with('sector')
                ->orderBy('authority_level')
                ->get()
                ->map(function ($l) {
                    return [
                        'id' => $l->id,
                        'name' => $l->name,
                        'sector_name' => $l->sector->name ?? '—',
                        'authority_level' => $l->authority_level
                    ];
                })
        ]);
    }
    public function storeTemplate(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'nullable|in:0,1',
            'process_type_id' => 'required|exists:process_types,id',

        ]);

        $template = WorkflowTemplate::create([
            'name' => $validated['name'],
            'is_active' => $validated['is_active'] ?? 1,
            'process_type_id' => $validated['process_type_id'],

        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Fluxo criado com sucesso!',
                'template' => $template
            ]);
        }

        return back()->with('success', 'Fluxo criado com sucesso!');
    }
    public function deleteTemplate($id)
    {
        $template = WorkflowTemplate::find($id);

        if (!$template) {
            // Se for AJAX (fetch)
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fluxo não encontrado.'
                ], 404);
            }

            return back()->with('error', 'Fluxo não encontrado.');
        }

        try {
            $template->delete();

            // Resposta AJAX
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Fluxo excluído com sucesso!'
                ]);
            }

            // Fallback HTML
            return back()->with('success', 'Fluxo excluído com sucesso!');

        } catch (\Exception $e) {

            // Resposta AJAX
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não foi possível excluir o fluxo.'
                ], 500);
            }

            return back()->with('error', 'Não foi possível excluir o fluxo.');
        }
    }
    public function updateTemplate(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'nullable|in:0,1'
        ]);

        $template = WorkflowTemplate::findOrFail($id);
        $template->update($validated);

        // Caso seja AJAX → retorna JSON
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Fluxo atualizado com sucesso!',
                'template' => $template
            ]);
        }

        // fallback (se alguém abrir direto sem AJAX)
        return redirect()
            ->route('workflows.index')
            ->with('success', 'Fluxo atualizado com sucesso!');
    }
    public function deleteStep($id)
    {
        $step = WorkflowStep::findOrFail($id);
        $step->delete();

        return response()->json([
            'success' => true,
            'message' => 'Etapa excluída com sucesso!'
        ]);
    }


}
