<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnProcess extends Model
{
    protected $fillable = [
        'tipo',
        'nomeCliente',
        'cnpjCliente',
        'motivo',
        'codigoErro',
        'observacao',
        'gestorSolicitante',
        'movimentacaoMercadoria',
    ];

    public function items()
    {
        return $this->hasMany(ReturnProcessItem::class);
    }

    public function steps()
    {
        return $this->hasMany(ReturnProcessStep::class);
    }
}
