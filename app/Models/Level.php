<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Level extends Model // CORREÇÃO: Deve ser "Level", não "Sector"
{
    use HasFactory;

    protected $fillable = ['sector_id', 'name', 'authority_level'];

    /**
     * O Setor ao qual este Nível pertence.
     */
    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    /**
     * Usuários associados a este Nível.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Permissões associadas a este Nível via tabela pivot.
     */
    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            'level_permission',
            'level_id',
            'permission_id'
        );
    }
}
