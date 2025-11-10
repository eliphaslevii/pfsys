<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProcessStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'process_id',
        'workflow_id',
        'user_id',
        'status',
        'action',
        'comments',
        'is_current',
        'completed_at',
    ];

    protected $casts = [
        'is_current' => 'boolean',
        'completed_at' => 'datetime',
    ];

    // 🔗 Relações
    public function process()
    {
        return $this->belongsTo(Process::class);
    }

    public function workflow()
    {
        return $this->belongsTo(ProcessWorkflow::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
