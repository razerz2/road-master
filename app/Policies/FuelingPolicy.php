<?php

namespace App\Policies;

use App\Models\Fueling;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FuelingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->role === 'admin') {
            return true;
        }
        return $user->hasPermission('fuelings', 'view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Fueling $fueling): bool
    {
        if ($user->role === 'admin') {
            return true;
        }
        if ($user->role === 'condutor') {
            return $fueling->user_id === $user->id;
        }
        return $user->hasPermission('fuelings', 'view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->role === 'admin') {
            return true;
        }
        return $user->hasPermission('fuelings', 'create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Fueling $fueling): bool
    {
        if ($user->role === 'admin') {
            return true;
        }
        if ($user->role === 'condutor') {
            return $fueling->user_id === $user->id && $user->hasPermission('fuelings', 'edit');
        }
        return $user->hasPermission('fuelings', 'edit');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Fueling $fueling): bool
    {
        if ($user->role === 'admin') {
            return true;
        }
        if ($user->role === 'condutor') {
            return $fueling->user_id === $user->id && $user->hasPermission('fuelings', 'delete');
        }
        return $user->hasPermission('fuelings', 'delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Fueling $fueling): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Fueling $fueling): bool
    {
        return false;
    }
}
