<?php

namespace App\Policies;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TripPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->role === 'admin') {
            return true;
        }
        return $user->hasPermission('trips', 'view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Trip $trip): bool
    {
        if ($user->role === 'admin') {
            return true;
        }
        if ($user->role === 'condutor') {
            // Condutor só pode ver percursos onde é o condutor E o veículo está relacionado a ele
            $hasVehicleRelation = $user->vehicles()->where('vehicles.id', $trip->vehicle_id)->exists();
            return $trip->driver_id === $user->id && $hasVehicleRelation;
        }
        
        return $user->hasPermission('trips', 'view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->role === 'admin') {
            return true;
        }
        return $user->hasPermission('trips', 'create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Trip $trip): bool
    {
        if ($user->role === 'admin') {
            return true;
        }
        if ($user->role === 'condutor') {
            return $trip->driver_id === $user->id && $user->hasPermission('trips', 'edit');
        }
        return $user->hasPermission('trips', 'edit');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Trip $trip): bool
    {
        if ($user->role === 'admin') {
            return true;
        }
        if ($user->role === 'condutor') {
            return $trip->driver_id === $user->id && $user->hasPermission('trips', 'delete');
        }
        return $user->hasPermission('trips', 'delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Trip $trip): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Trip $trip): bool
    {
        return false;
    }
}
