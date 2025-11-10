<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    public function workflows()
    {
        return $this->hasMany(ProcessWorkflow::class);
    }

    public function processes()
    {
        return $this->hasMany(Process::class);
    }

    public function notifications()
    {
        return $this->hasMany(ProcessNotification::class);
    }
}
