<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sector extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'is_active', 'parent_id'];

    /**
     * Setores subordinados (Hierarquia).
     */
    public function children(): HasMany
    {
        return $this->hasMany(Sector::class, 'parent_id');
    }

    /**
     * Setor superior (Setor Pai).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Sector::class, 'parent_id');
    }

    /**
     * Níveis (Levels) definidos neste setor.
     */
    public function levels(): HasMany
    {
        return $this->hasMany(Level::class);
    }

    /**
     * Usuários que pertencem a este setor.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}