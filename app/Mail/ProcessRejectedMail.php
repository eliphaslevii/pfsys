<?php

namespace App\Mail;

use App\Models\Process;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProcessRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Process $process
    ) {}

    public function build()
    {
        return $this
            ->subject('Processo recusado pelo Fiscal')
            ->view('emails.process-rejected', [
                'process' => $this->process,
            ]);
    }
}
