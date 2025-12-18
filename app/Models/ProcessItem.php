<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessItem extends Model
{
    use HasFactory;

    /**
     * Nome da tabela
     */
    protected $table = 'process_items';

    /**
     * Campos que podem ser preenchidos em massa
     */
    protected $fillable = [
        'process_id',
        'artigo',
        'descricao',
        'ncm',
        'quantidade',
        'preco_unitario',
    ];

    /**
     * Casts automáticos
     */
    protected $casts = [
        'quantidade'     => 'decimal:2',
        'preco_unitario' => 'decimal:2',
    ];

    /**
     * 🔹 Processo ao qual o item pertence
     */
    public function process()
    {
        return $this->belongsTo(Process::class, 'process_id');
    }
}
