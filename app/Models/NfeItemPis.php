<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NfeItemPis extends Model
{
    protected $table = 'nfe_item_pis';

    protected $fillable = [
        'nfe_item_id',
        'cst',
        'v_bc',
        'p_pis',
        'v_pis',
    ];

    public function item()
    {
        return $this->belongsTo(NfeItem::class, 'nfe_item_id');
    }
}
