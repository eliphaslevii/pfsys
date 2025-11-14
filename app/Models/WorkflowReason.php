<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowReason extends Model
{
    use HasFactory;

    protected $table = 'workflow_reasons';

    protected $fillable = [
        'workflow_template_id',
        'name',
    ];

    /**
     * 🔗 Relacionamento: motivo pertence a um template
     */
    public function template()
    {
        return $this->belongsTo(WorkflowTemplate::class, 'workflow_template_id');
    }
}
