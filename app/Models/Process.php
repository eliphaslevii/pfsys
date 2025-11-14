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
        'current_workflow_id',
        'status',
        'cliente_nome',
        'cliente_cnpj',
        'nf_saida',
        'nfo',            // ✅ novo
        'protocolo',      // ✅
        'nf_devolucao',
        'recusa_sefaz',
        'movimentacao_mercadoria',
        'observacoes',
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

    /* 🔹 Workflow atual (etapa ativa) */
    public function currentWorkflow()
    {
        return $this->belongsTo(ProcessWorkflow::class, 'current_workflow_id');
    }

    /* 🔹 Itens vinculados ao processo */
    public function items()
    {
        return $this->hasMany(ProcessItem::class, 'process_id');
    }

    /* 🔹 Execuções (histórico de andamento) */
    public function executions()
    {
        return $this->hasMany(ProcessExecution::class, 'process_id');
    }

    /* 🔹 Logs de ações */
    public function logs()
    {
        return $this->hasMany(ProcessLog::class, 'process_id');
    }

    /* 🔹 Documentos anexados */
    public function documents()
    {
        return $this->hasMany(ProcessDocument::class, 'process_id');
    }

    /* 🔹 Steps (controle de etapas concluídas ou pendentes) */
    public function steps()
    {
        return $this->hasMany(ProcessStep::class, 'process_id');
    }
}
