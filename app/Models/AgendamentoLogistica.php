<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgendamentoLogistica extends Model
{
    protected $table = 'agendamentos';

    protected $fillable = [
        'transportadora_nome',
        'transportadora_cnpj',
        'data_agendada',
        'data_confirmada',
        'data_coleta',
        'status',
        'observacoes'
    ];

    public function nfes()
    {
        return $this->belongsToMany(
            Nfe::class,
            'agendamento_nfe',     // tabela pivot
            'agendamento_id',      // FK deste model na pivot
            'nfe_id'               // FK do outro model na pivot
        )
        ->withPivot(['bipado', 'bipado_em'])
        ->withTimestamps();
    }

    public function scopePendentes($q)
    {
        return $q->where('status', 'pendente');
    }
}
