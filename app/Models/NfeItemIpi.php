<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NfeItemIpi extends Model
{
    protected $table = 'nfe_item_ipi';

    protected $fillable = [
        'nfe_item_id',
        'cst',
        'c_enq',
        'v_bc',
        'p_ipi',
        'v_ipi',
    ];

    public function item()
    {
        return $this->belongsTo(NfeItem::class, 'nfe_item_id');
    }
}
