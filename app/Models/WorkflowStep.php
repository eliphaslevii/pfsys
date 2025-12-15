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

    /** 🔹 Setor responsável */
    public function sector()
    {
        return $this->belongsTo(Sector::class);
    }

    /** 🔹 Nível obrigatório */
    public function requiredLevel()
    {
        return $this->belongsTo(Level::class, 'required_level_id');
    }

    /** 🔹 Workflow Template */
    public function template()
    {
        return $this->belongsTo(WorkflowTemplate::class, 'workflow_template_id');
    }

    /** 🔹 Próximo passo (caso aprovado) */
    public function nextStep()
    {
        return $this->belongsTo(WorkflowStep::class, 'next_step_id');
    }

    /** 🔹 Próximo passo caso rejeitado */
    public function nextOnReject()
    {
        return $this->belongsTo(WorkflowStep::class, 'next_on_reject_step_id');
    }
}
