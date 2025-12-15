<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NfeTracking extends Model
{
    protected $table = 'nfe_tracking';

    protected $fillable = [
        'nfe_id',
        'transportadora',
        'status',
        'mensagem',
        'data_evento',
    ];

    protected $casts = [
        'data_evento' => 'datetime',
    ];
}
