<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NfeIbscbsTot extends Model
{
    protected $table = 'nfe_ibscbs_tot';

    protected $fillable = [
        'nfe_id',
        'vbc_ibscbs',

        'ibs_uf_vdif',
        'ibs_uf_dev',
        'ibs_uf_total',

        'ibs_mun_vdif',
        'ibs_mun_dev',
        'ibs_mun_total',

        'v_ibs',
        'v_cbs'
    ];

    public function nfe()
    {
        return $this->belongsTo(Nfe::class, 'nfe_id');
    }
}
