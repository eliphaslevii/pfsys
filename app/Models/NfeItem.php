<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NfeItem extends Model
{
    protected $table = 'nfe_itens';

    protected $fillable = [
        'nfe_id',
        'n_item',
        'codigo_produto',
        'descricao',
        'ncm',
        'cfop',
        'quantidade',
        'unidade',
        'valor_unitario',
        'valor_total',
        'fci',
        'pedido_cliente',
        'valor_tributos_totais',
    ];

    public function nfe()
    {
        return $this->belongsTo(Nfe::class, 'nfe_id');
    }

    public function icms()
    {
        return $this->hasOne(NfeItemIcms::class, 'nfe_item_id');
    }

    public function ipi()
    {
        return $this->hasOne(NfeItemIpi::class, 'nfe_item_id');
    }

    public function pis()
    {
        return $this->hasOne(NfeItemPis::class, 'nfe_item_id');
    }

    public function cofins()
    {
        return $this->hasOne(NfeItemCofins::class, 'nfe_item_id');
    }

    public function ibscbs()
    {
        return $this->hasOne(NfeItemIbscbs::class, 'nfe_item_id');
    }
}
