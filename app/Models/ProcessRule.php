<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'process_type_id',
        'rule_name',
        'condition',
        'action',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function processType()
    {
        return $this->belongsTo(ProcessType::class);
    }
}
