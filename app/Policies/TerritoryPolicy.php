<?php

namespace App\Policies;

use App\Models\Territory;
use App\Models\User;

class TerritoryPolicy
{
    /**
     * Determine if the user can view the list of territories.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'admin']);
    }

    /**
     * Determine if the user can view a specific territory.
     */
    public function view(User $user, Territory $territory): bool
    {
        return $user->hasAnyRole(['superadmin', 'admin']);
    }

    /**
     * Determine if the user can create territories.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'admin']);
    }

    // You can also add update and delete methods here following the same logic
}