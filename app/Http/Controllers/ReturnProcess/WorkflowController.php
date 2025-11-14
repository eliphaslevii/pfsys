<?php

namespace App\Http\Controllers\ReturnProcess;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\WorkflowTemplate;

class WorkflowController extends Controller
{
    public function index()
    {
        $templates = \App\Models\WorkflowTemplate::with([
            'workflows' => fn($q) => $q->orderBy('step_order'),
            'reasons'
        ])->get();

        $reasons = \App\Models\WorkflowReason::with('template')->latest()->get();
        $sectors = \App\Models\Sector::orderBy('name')->get();

        return view('returnProcess.workflows.adminIndex', compact('templates', 'sectors', 'reasons'));
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
            'workflow_template_id' => 'required|integer|exists:workflow_templates,id',
            'sector_id' => 'required|string', // Aqui vem o nome do setor selecionado
            'step_order' => 'nullable|integer|min:1',
        ]);

        \App\Models\ProcessWorkflow::create([
            'workflow_template_id' => $validated['workflow_template_id'],
            'name' => $validated['sector_id'], // 👈 Nome da etapa = nome do setor
            'step_order' => $validated['step_order'] ?? 1,
        ]);

        return back()->with('success', 'Etapa adicionada com sucesso!');
    }
    public function deleteReason($id)
    {
        $reason = \App\Models\WorkflowReason::findOrFail($id);
        $reason->delete();
        return redirect()->route('workflows.index')->with('success', 'Motivo excluído com sucesso!');
    }

}
