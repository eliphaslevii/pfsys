<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Process extends Model
{
    use HasFactory;

    protected $fillable = [
        'process_type_id',
        'created_by',
        'status',

        'cliente_nome',
        'cliente_cnpj',
        'motivo',
        'observacoes',
        'movimentacao_mercadoria',
        'codigo_erro',
        'nf_saida',
        'nf_devolucao',
        'nfo',
        'protocolo',
        'recusa_sefaz',
        'delivery',
        'doc_faturamento',
        'ordem_entrada',
        'migo',

        // CORRETO: existe no banco
        'workflow_template_id',
        'workflow_reason_id',
        'current_workflow_step_id',
    ];

    /* 🔹 Tipo de processo (Ex: Devolução / Recusa) */
    public function type()
    {
        return $this->belongsTo(ProcessType::class, 'process_type_id');
    }

    /* 🔹 Usuário criador do processo */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /* 🔹 Itens vinculados ao processo */
    public function items()
    {
        return $this->hasMany(ProcessItem::class, 'process_id');
    }

    /* 🔹 Execuções */
    public function executions()
    {
        return $this->hasMany(ProcessExecution::class, 'process_id');
    }

    /* 🔹 Logs */
    public function logs()
    {
        return $this->hasMany(ProcessLog::class, 'process_id');
    }

    /* 🔹 Documentos anexados */
    public function documents()
    {
        return $this->hasMany(ProcessDocument::class, 'process_id');
    }

    /* 🔹 Histórico de steps (process_steps) */
    public function steps()
    {
        return $this->hasMany(ProcessStep::class, 'process_id');
    }

    /* 🔹 Step ATUAL baseado em process_steps */
    public function currentStep()
    {
        return $this->hasOne(ProcessStep::class, 'process_id')->where('is_current', true);
    }

    /* 🔹 Template do workflow */
    public function workflowTemplate()
    {
        return $this->belongsTo(WorkflowTemplate::class, 'workflow_template_id');
    }

    /* 🔹 Motivo que escolheu o template */
    public function workflowReason()
    {
        return $this->belongsTo(WorkflowReason::class, 'workflow_reason_id');
    }

    /* 🔹 Etapa atual real (tabela workflow_steps) */
    public function currentWorkflowStep()
    {
        return $this->belongsTo(WorkflowStep::class, 'current_workflow_step_id');
    }
}
