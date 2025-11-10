<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ProcessDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'process_id',
        'file_name',
        'file_path',
        'file_type',
        'uploaded_by',
    ];

    public function process()
    {
        return $this->belongsTo(Process::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
