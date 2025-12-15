<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowReason extends Model {
    protected $fillable = ['workflow_template_id','name','description','is_active'];
    public function template(){ return $this->belongsTo(WorkflowTemplate::class,'workflow_template_id'); }
}

