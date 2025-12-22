<?php

namespace App\Mail;

use App\Models\Process;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

// ✅ FIX 1 — MAIL (recomendado: mantém a view limpa)

class ProcessAdvancedMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $url;

    public function __construct(
        public Process $process,
        public ?string $nextStep,
        public string $event
    ) {
        $this->url = route('processes.index');
    }

    public function build()
    {
        $subjectMap = [
            'approved' => '✅ Processo aprovado',
            'advanced' => "🔔 Processo avançou — {$this->nextStep}",
            'rejected' => '❌ Processo rejeitado',
        ];

        return $this
            ->subject($subjectMap[$this->event] ?? '🔔 Atualização de processo')
            ->view('emails.process-advanced');
    }
}
