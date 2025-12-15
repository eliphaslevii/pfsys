<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NfeItemIbscbs extends Model
{
    protected $table = 'nfe_item_ibscbs';

    protected $fillable = [
        'nfe_item_id',
        'cst',
        'class_tributaria',
        'vbc',
        'p_ibsu_f',
        'v_ibsu_f',
        'p_ibsm_u',
        'v_ibsm_u',
        'v_ibs',
        'p_cbs',
        'v_cbs',
    ];

    public function item()
    {
        return $this->belongsTo(NfeItem::class, 'nfe_item_id');
    }
}
