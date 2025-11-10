<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'process_id',
        'artigo',
        'descricao',
        'ncm',
        'quantidade',
        'preco_unitario',
    ];

    public function process()
    {
        return $this->belongsTo(Process::class);
    }
}
