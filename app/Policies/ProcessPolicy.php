<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Process;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProcessPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        // Admin Master pode tudo
        if ($user->level?->name === 'Admin Master') {
            return true;
        }
    }

    public function view(User $user, Process $process)
    {
        return $user->hasPermission('process.view');
    }

    public function create(User $user)
    {
        return $user->hasPermission('process.create');
    }

    public function approve(User $user, Process $process)
    {
        return $user->hasPermission('process.approve') &&
               $user->level_id === $process->workflow?->required_level_id;
    }

    public function reject(User $user, Process $process)
    {
        return $user->hasPermission('process.reject') &&
               $user->level_id === $process->workflow?->required_level_id;
    }

    public function delete(User $user, Process $process)
    {
        return $user->hasPermission('process.delete');
    }
}
