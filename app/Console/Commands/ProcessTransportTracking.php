<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Nfe;
use App\Models\NfeTrackingState;
use App\Jobs\RateLimitedTransportJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Services\Transportadoras\PatrusService;
use App\Services\Transportadoras\SaoMiguelService;
use App\Services\Transportadoras\AlfaService;

class ProcessTransportTracking extends Command
{
    protected $signature = 'tracking:process';
    protected $description = 'Enfileira NFEs para rastreamento automático';

    public function handle()
    {
        Log::info("🔍 Iniciando tracking:process");

        // ---------------------------------------------------------
        // 1 — Garante que todas as NFEs possuem trackingState ligado
        // ---------------------------------------------------------
        foreach (Nfe::doesntHave('trackingState')->cursor() as $nf) {
            NfeTrackingState::firstOrCreate(
                ['nfe_id' => $nf->id],
                ['stop_tracking' => false, 'next_check_at' => now()]
            );
        }

        // ---------------------------------------------------------
        // 2 — NFEs que precisam ser rastreadas agora
        // ---------------------------------------------------------
        $states = NfeTrackingState::where('stop_tracking', false)
            ->where('next_check_at', '<=', now())
            ->with('nfe')
            ->get();

        if ($states->isEmpty()) {
            return Command::SUCCESS;
        }

        // ---------------------------------------------------------
        // 3 — Normalizador universal de nomes
        // ---------------------------------------------------------
        $normalize = function ($text) {
            if (!$text) return '';

            $normalized = \Normalizer::normalize($text, \Normalizer::FORM_D);
            $normalized = preg_replace('/\p{Mn}/u', '', $normalized);
            $normalized = preg_replace('/[^a-zA-Z0-9 ]/', ' ', $normalized);
            $normalized = preg_replace('/\s+/', ' ', $normalized);

            return strtolower(trim($normalized));
        };

        // ---------------------------------------------------------
        // 4 — Identificação das transportadoras
        // ---------------------------------------------------------

        /** 🔵 São Miguel */
        $saoMiguel = $states->filter(function ($s) use ($normalize) {
            $t = $normalize($s->nfe->transportadora_nome);

            return str_contains($t, 'sao miguel')
                || str_contains($t, 'expresso sao miguel')
                || str_contains($t, 'exp sao miguel')
                || str_contains($t, 'miguel');
        });

        /** 🔵 Alfa Transportes */
        $alfa = $states->filter(function ($s) use ($normalize) {
            $t = $normalize($s->nfe->transportadora_nome);
            return str_contains($t, 'alfa');
        });

        /** 🔵 Patrus Transportes */
        $patrus = $states->filter(function ($s) use ($normalize) {
            $t = $normalize($s->nfe->transportadora_nome);

            return str_contains($t, 'patrus')
                || str_contains($t, 'ptrus')
                || str_contains($t, 'patrus transp')
                || str_contains($t, 'patrus log');
        });

        $total = 0;

        // ======================================================
        // 🚚 SÃO MIGUEL — Tem trava especial por Rate Limit
        // ======================================================
        if (!Cache::has('lock-sao-miguel')) {

            $batch = $saoMiguel->take(15);

            foreach ($batch->values() as $i => $state) {

                $state->update(['next_check_at' => now()->addMinutes(10)]);

                RateLimitedTransportJob::dispatch(
                    $state->nfe,
                    SaoMiguelService::class,
                    'sao-miguel',
                    'Sao Miguel'
                )->delay(now()->addSeconds($i * 4));

                Log::info("Job (SM) NF {$state->nfe->numero} delay {$i}x4s");
            }

            $total += $batch->count();
        }

        // ======================================================
        // 🚚 ALFA — 15 req/min (SEM LOCK)
        // ======================================================
        $batch = $alfa->take(15);

        foreach ($batch->values() as $i => $state) {

            $state->update(['next_check_at' => now()->addMinutes(10)]);

            RateLimitedTransportJob::dispatch(
                $state->nfe,
                AlfaService::class,
                'alfa',
                'Alfa'
            )->delay(now()->addSeconds($i * 4));

            Log::info("Job (ALFA) NF {$state->nfe->numero} delay {$i}x4s");
        }

        $total += $batch->count();

        // ======================================================
        // 🚚 PATRUS — 15 req/min (SEM LOCK inicialmente)
        // ======================================================
        $batch = $patrus->take(15);

        foreach ($batch->values() as $i => $state) {

            $state->update(['next_check_at' => now()->addMinutes(10)]);

            RateLimitedTransportJob::dispatch(
                $state->nfe,
                PatrusService::class,
                'patrus',
                'Patrus'
            )->delay(now()->addSeconds($i * 4));

            Log::info("Job (PATRUS) NF {$state->nfe->numero} delay {$i}x4s");
        }

        $total += $batch->count();

        // ---------------------------------------------------------
        // LOG FINAL
        // ---------------------------------------------------------
        Log::info("📦 Batch processado. Jobs enviados: {$total}");

        return Command::SUCCESS;
    }
}
