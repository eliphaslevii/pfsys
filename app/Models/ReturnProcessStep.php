<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnProcessStep extends Model
{
    protected $fillable = [
        'return_process_id',
        'step_name',
        'responsavel',
        'status',
        'comentario',
    ];

    public function process()
    {
        return $this->belongsTo(ReturnProcess::class);
    }
}
