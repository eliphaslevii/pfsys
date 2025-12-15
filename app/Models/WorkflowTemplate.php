<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowTemplate extends Model {
    protected $fillable = ['process_type_id','name','description','is_active'];
    public function reasons() { return $this->hasMany(WorkflowReason::class,'workflow_template_id'); }
    public function steps() { return $this->hasMany(WorkflowStep::class,'workflow_template_id')->orderBy('order'); }
    public function processType() { return $this->belongsTo(ProcessType::class,'process_type_id'); }
}
