<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any users.
     */
    public function viewAny(User $authenticatedUser): bool
    {
        return $authenticatedUser->hasAnyRole(['superadmin', 'admin', 'marketing logistics associate', 'operations logistics associate', 'procurement executive associate']);
    }

    /**
     * Determine whether the user can view the specific user.
     */
    public function view(User $authenticatedUser, User $userToView): bool
    {
        // Superadmins and Admins can view any user
        if ($authenticatedUser->hasAnyRole(['superadmin', 'admin'])) {
            return true;
        }

        // Procurement Executive Associate can view any user
        if ($authenticatedUser->hasAnyRole('procurement executive associate')) {
            return true;
        }

        // Other roles can only view users they created
        if ($authenticatedUser->hasAnyRole(['marketing logistics associate', 'procurement logistics associate'])) {
            // Check if the authenticated user created the user to view
            return $userToView->createdBy->contains($authenticatedUser);
        }

        // Deny all other users
        return false;
    }

    /**
     * Determine whether the user can create new users.
     */
    public function create(User $authenticatedUser): bool
    {
        return $authenticatedUser->hasAnyRole(['superadmin', 'admin', 'marketing logistics associate', 'procurement logistics associate', 'procurement executive associate']);
    }

    /**
     * Determine whether the user can update the specific user.
     */
    public function update(User $authenticatedUser, User $userToUpdate): bool
    {       
        // Superadmins and Admins can update any user
        if ($authenticatedUser->hasAnyRole(['superadmin', 'admin'])) {
            return true;
        }

        // Procurement Executive Associate can update any user
        if ($authenticatedUser->hasAnyRole('procurement executive associate')) {
            return true;
        }

        // Other roles can only update users they created
        if ($authenticatedUser->hasAnyRole(['marketing logistics associate', 'procurement logistics associate'])) {
            // Use a null-safe check to compare the creator's ID with the authenticated user's ID.
            return optional($userToUpdate->createdBy)->id === $authenticatedUser->id;
        }

        // Deny all other users
        return false;
    }
}