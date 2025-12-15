<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NfeItemIcms extends Model
{
    protected $table = 'nfe_item_icms';

    protected $fillable = [
        'nfe_item_id',
        'orig',
        'cst',
        'mod_bc',
        'v_bc',
        'p_icms',
        'v_icms',
        'tipos'
    ];

    public function item()
    {
        return $this->belongsTo(NfeItem::class, 'nfe_item_id');
    }
}
