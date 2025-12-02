<?php

namespace App\Policies;

use App\Models\User;

class SettingsPolicy
{
    /**
     * Determine if the user can view any settings.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }
}

