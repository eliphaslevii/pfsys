<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NfeTrackingState extends Model
{
    protected $table = 'nfe_tracking_state';

    protected $fillable = [
        'nfe_id',
        'next_check_at',
        'last_status',
        'last_message',
        'stop_tracking',
    ];

    public function nfe()
    {
        return $this->belongsTo(Nfe::class);
    }
}
