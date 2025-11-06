<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Level;
use App\Models\Sector;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'level_id',
        'sector_id',
        'active',

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    /**
     * Define o Setor do usuário.
     */
    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    public function hasPermissionTo(string $permissionName): bool
    {
        // 1. Acesso Rápido por Nível de Autoridade (Super Admin)
        // Usa o operador nullsafe (?)
        if ($this->level?->authority_level >= 90) {
            return true;
        }

        // 2. Checagem Granular via Pivot Table (Se o Level existir)
        if (!$this->level) {
            return false;
        }

        return $this->level->permissions
            ->pluck('name')
            ->contains($permissionName);
    }
}
