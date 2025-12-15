<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Level;

class ProcessWorkflow extends Model
{
    use HasFactory;

    protected $fillable = [
        'process_type_id',
        'step_name',
        'required_level_id',
        'next_step',
        'motivo',
        'auto_notify',
    ];

    protected $casts = [
        'auto_notify' => 'boolean',
    ];

    public function processType()
    {
        return $this->belongsTo(ProcessType::class);
    }

    public function requiredLevel()
    {
        return $this->belongsTo(Level::class, 'required_level_id');
    }

    public function notifications()
    {
        return $this->hasMany(ProcessNotification::class, 'workflow_id');
    }
    public function template()
    {
        return $this->belongsTo(WorkflowTemplate::class, 'workflow_template_id');
    }
}
