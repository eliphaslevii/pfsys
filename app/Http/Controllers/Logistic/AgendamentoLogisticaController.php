<?php

namespace App\Http\Controllers\Logistic;

use App\Http\Controllers\Controller;
use App\Models\AgendamentoLogistica;
use Illuminate\Http\Request;
use App\Models\Nfe;
use App\Models\Agendamento;
use App\Services\Nfe\EspelhoNfeService;
use App\Mail\AgendamentoColetaMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Jobs\ProcessarAgendamentoLogistica;

class AgendamentoLogisticaController extends Controller
{
    public function index(Request $request)
    {
        $query = AgendamentoLogistica::query()
            ->withCount('nfes')
            ->orderBy('created_at', 'desc');

        if ($request->search) {
            $s = $request->search;

            $query->where(function ($q) use ($s) {
                $q->where('transportadora_nome', 'like', "%$s%")
                    ->orWhere('status', 'like', "%$s%");
            });
        }

        $agendamentos = $query->paginate(20);

        // Se for AJAX, renderiza só o conteúdo
        if ($request->ajax()) {
            return response()->view('logistica.index', compact('agendamentos'));
        }

        return view('logistica.index', compact('agendamentos'));
    }
    public function create()
    {
        $transportadoras = Nfe::select('transportadora_nome')
            ->whereNotNull('transportadora_nome')
            ->groupBy('transportadora_nome')
            ->orderBy('transportadora_nome')
            ->pluck('transportadora_nome');

        return view('logistica.create', compact('transportadoras'));
    }
    public function fetchNfes(Request $request)
    {
        $tp = $request->transportadora;

        if (!$tp) {
            return response()->json([]);
        }

        $nfes = Nfe::where('transportadora_nome', $tp)
            ->whereDoesntHave('agendamentos') // não mostrar NFes já agendadas
            ->orderBy('created_at', 'desc')
            ->get();

        $result = $nfes->map(function ($nf) {

            // Badge padrão (por enquanto todas SEM status)
            $badge = [
                'class' => 'badge bg-secondary-lt',
                'text'  => 'AGENDAR',
                'icon'  => 'ti ti-calendar'
            ];

            return [
                'id'        => $nf->id,
                'numero'    => $nf->numero,
                'serie'     => $nf->serie,
                'dest'      => $nf->dest_nome,
                'cidade'    => $nf->dest_municipio,
                'uf'        => $nf->dest_uf,

                'valor'     => $nf->valor_total,

                'volumes'   => $nf->volume_quantidade,
                'especie'   => $nf->volume_especie,
                'peso_bruto'  => $nf->peso_bruto,
                'peso_liquido' => $nf->peso_liquido,

                'badgeClass' => $badge['class'],
                'badgeText'  => $badge['text'],
                'badgeIcon'  => $badge['icon'],

                'data'      => $nf->data_emissao,
            ];
        });

        return response()->json($result);
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'transportadora' => 'required|string',
            'nfes' => 'required|array|min:1',
            'nfes.*' => 'integer|exists:nfes,id',
            'email_to' => 'required|email',
            'email_cc' => 'array'
        ]);

        try {
            DB::transaction(function () use ($data) {

                $agendamento = AgendamentoLogistica::create([
                    'transportadora_nome' => $data['transportadora'],
                    'status' => 'pendente',
                ]);

                $agendamento->nfes()->attach($data['nfes']);

                ProcessarAgendamentoLogistica::dispatch(
                    $agendamento,
                    $data['email_to'],
                    $data['email_cc'] ?? []
                )->afterCommit();
            });

            return response()->json([
                'success' => true,
                'message' => 'Agendamento criado. O processamento foi enviado para a fila.'
            ]);
        } catch (\Throwable $e) {

            logger()->error($e);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar agendamento.',
            ], 500);
        }
    }
}
