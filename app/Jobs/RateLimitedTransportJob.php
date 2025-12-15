<?php

namespace App\Jobs;

use App\Models\Nfe;
use App\Models\NfeTracking;
use App\Models\NfeTrackingState;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class RateLimitedTransportJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public Nfe $nf;
    public string $serviceClass;
    public string $rateLimiterBucket;
    public string $transportadoraNome;

    /**
     * tries = 0 → Laravel NÃO limita tentativas.
     * retryUntil decide quando o job morre.
     */
    public $tries = 0;

    public function __construct(Nfe $nf, string $serviceClass, string $rateLimiterBucket, string $transportadoraNome)
    {
        $this->nf = $nf;
        $this->serviceClass = $serviceClass;
        $this->rateLimiterBucket = $rateLimiterBucket;
        $this->transportadoraNome = $transportadoraNome;

        $this->onQueue('transport_slow_queue');
    }

    /**
     * Quanto tempo o job pode viver sendo reagendado.
     */
    public function retryUntil()
    {
        return now()->addMinutes(10);
    }

    /**
     * Apenas para erros reais (não rate limit)
     */
    public function backoff(): array
    {
        return [10, 20, 40];
    }

    public function handle(): void
    {
        $nfNumero = $this->nf->numero; // Número real da NF

        /**
         * RATE LIMIT MANUAL – seguro e previsível.
         * Se falhar, o job dura mais SEM contar tentativa.
         */
        if (! RateLimiter::attempt($this->rateLimiterBucket, 1, function () {}, 4)) {
            #Log::warning("⏳ RateLimit ativo para NF {$nfNumero} ({$this->transportadoraNome}), adiando...");
            #$this->release(12);
            return;
        }

        try {

            $service = app($this->serviceClass);
            $result  = $service->consultar($this->nf);

            $status  = $result['status'] ?? 'unknown';
            $message = $result['mensagem'] ?? ($result['message'] ?? null);
            $evento  = $result['data_evento'] ?? now();

            // Histórico
            NfeTracking::create([
                'nfe_id'         => $this->nf->id,
                'transportadora' => $this->transportadoraNome,
                'status'         => $status,
                'mensagem'       => $message,
                'data_evento'    => $evento,
            ]);

            // Próxima consulta adaptativa
            $nextCheck = match (true) {
                $status === 'delivered' => null,
                $status === 'in_transit' => now()->addMinutes(30),
                $status === 'not_found'  => now()->addMinutes(60),
                (stripos((string)$message, 'saiu para entrega') !== false) => now()->addMinutes(30),
                in_array($status, ['http_error', 'exception']) => now()->addMinutes(2),
                default => now()->addMinutes(5),
            };

            // Estado
            NfeTrackingState::updateOrCreate(
                ['nfe_id' => $this->nf->id],
                [
                    'last_status'   => $status,
                    'last_message'  => $message,
                    'stop_tracking' => ($status === 'delivered'),
                    'next_check_at' => $nextCheck
                ]
            );

            Log::info("✅ Tracking processado ({$this->transportadoraNome}) NF {$nfNumero}. Status: {$status}");

        } catch (\Throwable $e) {

            // Erros reais (não rate limit)
            $this->handleExceptionState($e);

            throw $e; // Worker aplica backoff
        }
    }

    private function handleExceptionState(\Throwable $e): void
    {
        $nfNumero = $this->nf->numero;

        Log::error("❌ Erro no Job ({$this->transportadoraNome}) NF {$nfNumero}: " . $e->getMessage());

        NfeTrackingState::updateOrCreate(
            ['nfe_id' => $this->nf->id],
            [
                'last_status'   => 'system_error',
                'last_message'  => substr($e->getMessage(), 0, 250),
                'next_check_at' => now()->addMinutes(5)
            ]
        );
    }

    public function failed(\Throwable $e): void
    {
        $nfNumero = $this->nf->numero;

        Log::error("💀 Job EXPIROU ({$this->transportadoraNome}) NF {$nfNumero}: " . $e->getMessage());
    }
}
