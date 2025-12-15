<?php

namespace App\Http\Controllers\Logistic;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Nfe;
use App\Models\NfeTrackingState;
use App\Jobs\RateLimitedTransportJob;
use App\Services\Transportadoras\SaoMiguelService;

class ApiTransportController extends Controller
{
    /**
     * Consulta da Transportadora São Miguel
     */
    public function checkSaoMiguel(Request $request)
    {
        $validated = $request->validate([
            'nNF'      => 'required|string|max:20',
            'destCnpj' => 'required|string|max:20',
        ]);

        $nfNumber = ltrim($validated['nNF'], '0');

        $nfe = Nfe::where('numero', $nfNumber)->first();

        if (! $nfe) {
            return response()->json([
                'status'  => 'error',
                'message' => 'NF não encontrada no banco.'
            ], 404);
        }

        if (! str_contains(strtoupper($nfe->transportadora_nome ?? ''), 'MIGUEL')) {
            return response()->json([
                'status'  => 'ignored',
                'message' => 'Transportadora não é São Miguel'
            ]);
        }

        /**
         * 1️⃣ Se já existe tracking recente, retorna direto
         */
        $last = $nfe->lastTracking;

        if ($last && $last->created_at->gt(now()->subHours(24))) {
            return response()->json([
                'status'  => $last->status,
                'message' => $last->mensagem,
                'date'    => optional($last->data_evento)->format('d/m/Y H:i')
            ]);
        }

        /**
         * 2️⃣ Evita múltiplos jobs da mesma NF
         *    (controle ANTES do dispatch)
         */
        $state = NfeTrackingState::where('nfe_id', $nfe->id)->first();

        if ($state && $state->next_check_at && $state->next_check_at->isFuture()) {
            return response()->json([
                'status'  => 'waiting',
                'message' => 'Consulta já agendada, aguarde processamento.'
            ]);
        }

        /**
         * 3️⃣ Marca próxima tentativa ANTES de enfileirar
         *    (bloqueia rajada)
         */
        NfeTrackingState::updateOrCreate(
            ['nfe_id' => $nfe->id],
            [
                'next_check_at' => now()->addMinutes(5),
                'stop_tracking' => false
            ]
        );

        /**
         * 4️⃣ Enfileira o job com segurança
         */
        RateLimitedTransportJob::dispatch(
            $nfe,
            SaoMiguelService::class,
            'sao-miguel-transportes',
            'Sao Miguel'
        );

        return response()->json([
            'status'  => 'loading',
            'message' => 'Consulta enviada para processamento.'
        ]);
    }
}
