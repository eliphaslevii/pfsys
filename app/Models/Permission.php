<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    /**
     * Os Níveis (Levels) que possuem esta permissão.
     */
    public function levels(): BelongsToMany
    {
        return $this->belongsToMany(Level::class);
    }
}