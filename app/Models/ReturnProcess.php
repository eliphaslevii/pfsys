<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnProcess extends Model
{
    protected $table = 'processes'; // 👈 conecta à tabela existente
    protected $fillable = [
        'process_type_id',
        'created_by',
        'current_workflow_id',
        'status',
        'cliente_nome',
        'cliente_cnpj',
        'codigoErro',
        'nf_saida',
        'nf_devolucao',
        'recusa_sefaz',
        'movimentacao_mercadoria',
        'observacoes',
    ];

    public function type()
    {
        return $this->belongsTo(ProcessType::class, 'process_type_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

      public function logs()
    {
        return $this->hasMany(ProcessLog::class, 'process_id');
    }
}
