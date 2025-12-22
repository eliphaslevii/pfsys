<?php

namespace App\Jobs;

use App\Mail\ProcessAdvancedMail;
use App\Mail\ProcessRejectedMail;
use App\Models\Process;
use App\Models\User;
use App\Models\WorkflowStep;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class NotifyNextSectorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Process $process,
        public ?WorkflowStep $nextStep,
        public string $event // approved | advanced | rejected
    ) {}

    public function handle(): void
    {
        /*
        |--------------------------------------------------------------------------
        | EVENTO: REJECTED (Fiscal)
        |--------------------------------------------------------------------------
        */
        if ($this->event === 'rejected') {

            $creator = User::find($this->process->created_by);

            $gestoresComerciais = User::whereHas(
                'sector',
                fn($q) => $q->where('name', 'Comercial')
            )
                ->whereHas(
                    'level',
                    fn($q) => $q->where('name', 'like', '%Gestor%')
                )
                ->where('active', true)
                ->pluck('email')
                ->all();

            if ($creator && $creator->active) {
                Mail::to($creator->email)
                    ->cc($gestoresComerciais)
                    ->send(new ProcessRejectedMail($this->process));
            }

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | EVENTO: APPROVED / ADVANCED
        |--------------------------------------------------------------------------
        */
        $to = collect();
        $cc = collect();

        // Criador (somente quando aprovado)
        if ($this->event === 'approved') {
            $creator = User::find($this->process->created_by);
            if ($creator && $creator->active) {
                $to->push($creator);
            }
        }

        // Próximo setor (quando avançado)
        if ($this->event === 'advanced' && $this->nextStep?->sector_id) {

            $analysts = User::where('sector_id', $this->nextStep->sector_id)
                ->where('active', true)
                ->whereHas(
                    'level',
                    fn($q) => $q->where('name', 'like', '%Analista%')
                )
                ->get();

            $managers = User::where('sector_id', $this->nextStep->sector_id)
                ->where('active', true)
                ->whereHas(
                    'level',
                    fn($q) => $q->where('name', 'like', '%Gestor%')
                )
                ->get();

            $to = $to->merge($analysts);
            $cc = $cc->merge($managers);
        }

        $to = $to->unique('id');
        $cc = $cc->unique('id');

        if ($to->isEmpty()) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | ENVIO (COM MODO TESTE)
        |--------------------------------------------------------------------------
        */

            Mail::to('luiz.cesar@pferd.com')->send(
                new ProcessAdvancedMail(
                    $this->process,
                    $this->nextStep?->name,
                    $this->event
                )
            );
            return;

    }
}
