<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VehicleMandatoryEvent;
use Illuminate\Auth\Access\Response;

class MandatoryEventPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->role === 'admin') {
            return true;
        }
        return $user->hasPermission('mandatory_events', 'view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, VehicleMandatoryEvent $vehicleMandatoryEvent): bool
    {
        if ($user->role === 'admin') {
            return true;
        }
        return $user->hasPermission('mandatory_events', 'view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->role === 'admin') {
            return true;
        }
        return $user->hasPermission('mandatory_events', 'create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, VehicleMandatoryEvent $vehicleMandatoryEvent): bool
    {
        if ($user->role === 'admin') {
            return true;
        }
        return $user->hasPermission('mandatory_events', 'edit');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, VehicleMandatoryEvent $vehicleMandatoryEvent): bool
    {
        if ($user->role === 'admin') {
            return true;
        }
        return $user->hasPermission('mandatory_events', 'delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, VehicleMandatoryEvent $vehicleMandatoryEvent): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, VehicleMandatoryEvent $vehicleMandatoryEvent): bool
    {
        return false;
    }
}
