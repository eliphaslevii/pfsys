<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Queueable;

class AgendamentoColetaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $agendamento;
    public $zipPath;

    public function __construct($agendamento, $zipPath)
    {
        $this->agendamento = $agendamento;
        $this->zipPath = $zipPath;
    }

    public function build()
    {
        return $this->subject('Agendamento de Coleta')
            ->view('emails.agendamento')
            ->attach(storage_path("app/public/{$this->zipPath}"));
    }
}
