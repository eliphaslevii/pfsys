<?php

namespace App\Jobs;

use App\Models\AgendamentoLogistica;
use App\Services\Nfe\EspelhoNfeService;
use App\Mail\AgendamentoColetaMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class ProcessarAgendamentoLogistica implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public AgendamentoLogistica $agendamento,
        public string $emailTo,
        public array $emailCc = []
    ) {}

    public function handle(EspelhoNfeService $espelhoService)
    {
        // 1️⃣ Atualiza status
        $this->agendamento->update([
            'status' => 'processando'
        ]);

        // 2️⃣ Gera ZIP
        $nfesIds = $this->agendamento->nfes()->pluck('nfes.id')->toArray();

        $zipPath = $espelhoService
            ->gerarLotePdf($nfesIds, $this->agendamento);

        // 3️⃣ Envia e-mail
        Mail::to($this->emailTo)
            ->cc($this->emailCc)
            ->send(new AgendamentoColetaMail(
                $this->agendamento,
                $zipPath
            ));

        // 4️⃣ Finaliza
        $this->agendamento->update([
            'status' => 'enviado'
        ]);
    }

    public function failed(\Throwable $e)
    {
        $this->agendamento->update([
            'status' => 'erro'
        ]);

        logger()->error('Erro no agendamento', [
            'agendamento_id' => $this->agendamento->id,
            'exception' => $e
        ]);
    }
}
