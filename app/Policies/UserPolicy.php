<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Antes de tudo: Admin Master pode fazer tudo.
     */
    public function before(User $authUser, $ability)
    {
        if ($authUser->level->name === 'Admin Master') {
            return true; // acessa tudo
        }
    }

    /**
     * Ver usuário
     */
    public function view(User $authUser, User $targetUser): bool
    {
        // Funcionário só pode ver a si mesmo
        if (str_contains($authUser->level->name, 'Funcionário')) {
            return $authUser->id === $targetUser->id;
        }

        // Gerente só pode ver usuários do seu setor
        if (str_contains($authUser->level->name, 'Gerente')) {
            return $authUser->sector_id === $targetUser->sector_id;
        }

        return false;
    }

    /**
     * Criar usuário
     */
    public function create(User $authUser): bool
    {
        // Apenas gerentes podem criar funcionários do próprio setor
        return str_contains($authUser->level->name, 'Gerente');
    }

    /**
     * Atualizar usuário
     */
    public function update(User $authUser, User $targetUser): bool
    {
        // Funcionário não pode atualizar ninguém
        if (str_contains($authUser->level->name, 'Funcionário')) {
            return $authUser->id === $targetUser->id; // só editar a si mesmo
        }

        // Gerente só pode atualizar usuários do seu setor
        if (str_contains($authUser->level->name, 'Gerente')) {
            return $authUser->sector_id === $targetUser->sector_id;
        }

        return false;
    }

    /**
     * Deletar usuário
     */
    public function delete(User $authUser, User $targetUser): bool
    {
        // Apenas Admin Master pode deletar
        return false;
    }
}
