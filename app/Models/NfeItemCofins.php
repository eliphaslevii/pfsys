<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NfeItemCofins extends Model
{
    protected $table = 'nfe_item_cofins';

    protected $fillable = [
        'nfe_item_id',
        'cst',
        'v_bc',
        'p_cofins',
        'v_cofins',
    ];

    public function item()
    {
        return $this->belongsTo(NfeItem::class, 'nfe_item_id');
    }
}
