<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessExecution extends Model
{
    use HasFactory;

    protected $fillable = [
        'process_id',
        'current_workflow_id',
        'assigned_to',
        'approved_by',
        'approved_at',
        'status',
        'observations',
    ];

    /**
     * 🔗 Relacionamento com o processo principal
     */
    public function process()
    {
        return $this->belongsTo(Process::class);
    }

    /**
     * 🔗 Relacionamento com o workflow (etapa atual)
     */
    public function workflow()
    {
        return $this->belongsTo(ProcessWorkflow::class, 'current_workflow_id');
    }

    /**
     * 👤 Usuário responsável pela execução atual
     */
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * ✅ Usuário que aprovou esta etapa
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
