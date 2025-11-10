<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'process_type_id',
        'workflow_id',
        'step_name',
        'to',
        'cc',
        'bcc',
        'subject',
        'template_view',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function processType()
    {
        return $this->belongsTo(ProcessType::class);
    }

    public function workflow()
    {
        return $this->belongsTo(ProcessWorkflow::class, 'workflow_id');
    }
}
