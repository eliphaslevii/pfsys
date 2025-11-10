<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ProcessLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'process_id',
        'user_id',
        'action',
        'message',
    ];

    public function process()
    {
        return $this->belongsTo(Process::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
