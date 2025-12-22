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
    public function canApproveProcess(): bool
    {
        return $this->hasPermission('process.approve');
    }
    public function hasPermission(string $permission): bool
    {
        // Super Admin / Admin geral
        if ($this->level && preg_match('/super|admin/i', $this->level->name)) {
            return true;
        }

        // Permissões herdadas do nível
        return $this->level
            ?->permissions
            ?->contains('name', $permission) ?? false;
    }
    /**
     * Compatibilidade com o Blade (@can) e padrões do Spatie.
     */
    public function hasPermissionTo(string $permission): bool
    {
        return $this->hasPermission($permission);
    }

    public function workflowNotificationSteps()
    {
        return $this->belongsToMany(WorkflowStep::class, 'workflow_step_user');
    }
}
