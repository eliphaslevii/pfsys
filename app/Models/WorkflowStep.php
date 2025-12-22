<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowStep extends Model
{
    protected $fillable = [
        'workflow_template_id',
        'name',
        'order',
        'required_level_id',
        'sector_id',
        'next_step_id',
        'next_on_reject_step_id',
        'auto_notify',
        'rules_json'
    ];

    /** 🔹 Setor responsável pela etapa */
    public function sector()
    {
        return $this->belongsTo(Sector::class);
    }

    /** 🔹 Nível mínimo para atuar na etapa */
    public function requiredLevel()
    {
        return $this->belongsTo(Level::class, 'required_level_id');
    }

    /** 🔹 Template do workflow */
    public function template()
    {
        return $this->belongsTo(WorkflowTemplate::class, 'workflow_template_id');
    }

    /** 🔹 Próxima etapa (aprovação) */
    public function nextStep()
    {
        return $this->belongsTo(WorkflowStep::class, 'next_step_id');
    }

    /** 🔹 Próxima etapa (rejeição) */
    public function nextOnReject()
    {
        return $this->belongsTo(WorkflowStep::class, 'next_on_reject_step_id');
    }
}
