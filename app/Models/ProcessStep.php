<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProcessStep extends Model
{
    use HasFactory;

    protected $table = 'process_steps';

    protected $fillable = [
        'process_id',
        'workflow_step_id',
        'user_id',
        'status',
        'comments',
        'is_current',
        'completed_at',
    ];

    protected $casts = [
        'is_current' => 'boolean',
        'completed_at' => 'datetime',
    ];

    // 🔗 Step pertence ao processo
    public function process()
    {
        return $this->belongsTo(Process::class);
    }

    // 🔗 Step pertence ao workflow_step
    public function workflowStep()
    {
        return $this->belongsTo(WorkflowStep::class);
    }

    // 🔗 Usuário que executou
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 🔥 Escopo do step atual
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }
}
