<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Process extends Model
{
    use HasFactory;

    protected $fillable = [
        'process_type_id',
        'created_by',
        'current_workflow_id',
        'status',
        'cliente_nome',
        'cliente_cnpj',
        'nf_saida',
        'nf_devolucao',
        'recusa_sefaz',
        'movimentacao_mercadoria',
        'observacoes',
    ];

    protected $casts = [
        'movimentacao_mercadoria' => 'boolean',
    ];

    /** 🔗 RELACIONAMENTOS **/
    public function type()
    {
        return $this->belongsTo(ProcessType::class, 'process_type_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function workflow()
    {
        return $this->belongsTo(ProcessWorkflow::class, 'current_workflow_id');
    }

    public function items()
    {
        return $this->hasMany(ProcessItem::class);
    }

    public function documents()
    {
        return $this->hasMany(ProcessDocument::class);
    }

    public function logs()
    {
        return $this->hasMany(ProcessLog::class);
    }
}
