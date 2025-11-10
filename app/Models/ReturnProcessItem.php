<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnProcessItem extends Model
{
    protected $fillable = [
        'return_process_id',
        'artigo',
        'descricao',
        'ncm',
        'nf_saida',
        'nf_devolucao',
        'quantidade',
        'preco_unitario',
    ];

    public function process()
    {
        return $this->belongsTo(ReturnProcess::class);
    }
}
