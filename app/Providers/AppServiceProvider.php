<?php

namespace App\Providers;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Illuminate\Support\ServiceProvider;
use App\Policies\UserPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    protected $policies = [
        User::class => UserPolicy::class,
    ];

    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function (User $user, string $ability) {
            // 1. Super Admin (nível >= 90) tem permissão total
            if ($user->level?->authority_level >= 90) {
                return true;
            }

            // 2. Permissões granulares via método hasPermissionTo
            if ($user->level && method_exists($user, 'hasPermissionTo')) {
                return $user->hasPermissionTo($ability) ?: null;
            }

            return null;
        });

        // Gate adicional: acesso a relatórios de gestão
        Gate::define('access-management-reports', function (User $user) {
            return $user->level?->authority_level >= 70;
        });

        // ✅ Novo Gate: criação de usuários (user.create)
        Gate::define('user.create', function (User $user) {
            // Permite somente a partir de nível 90 (Super Admin)
            return $user->level?->authority_level >= 90;
        });
    }

}
