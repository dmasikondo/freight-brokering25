<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Builder;

class UserPolicy
{
    /**
     * Determine whether the user can view any users.
     */
    public function viewAny(User $authenticatedUser): Builder
    {
        //return $authenticatedUser->hasAnyRole(['superadmin', 'admin', 'marketing logistics associate', 'operations logistics associate', 'procurement executive associate']);
        // Superadmins can view all users.
        if ($authenticatedUser->hasAnyRole(['superadmin','admin'])) {
            return User::query();
        }

        // Procurement executive associates can view all shippers and carriers they created.
        if ($authenticatedUser->hasRole('procurement executive associate')) {
            return User::whereHas('createdBy', fn($query) => 
                $query->where('user_creations.creator_user_id', $authenticatedUser->id))
                 ->whereHas('roles', fn($query) => 
                    $query->where('roles.name', 'shipper')
                           ->orWhere('roles.name','carrier'));          
        }
        
        // Marketing logistics associates can view all shippers they created.
        elseif ($authenticatedUser->hasRole('marketing logistics associate')) {
            return User::whereHas('createdBy', fn($query) => 
                $query->where('user_creations.creator_user_id', $authenticatedUser->id))
                 ->whereHas('roles', fn($query) => 
                    $query->where('roles.name', 'shipper'));
                           
        }

        // Operations logistic associates can view all shippers and carriers within territory they created.
        elseif ($authenticatedUser->hasRole('operations logistics associate')) {
            return User::whereHas('createdBy', fn($query) => 
                $query->where('user_creations.creator_user_id', $authenticatedUser->id))
                 ->whereHas('roles', fn($query) => 
                    $query->where('roles.name', 'shipper')
                    ->orWhere('roles.name','carrier'));
                           
        }        

        // Procurement logistics associates can view all carriers they created.
        elseif ($authenticatedUser->hasRole('procurement logistics associate')) {
            return User::whereHas('createdBy', fn($query) => 
                $query->where('user_creations.creator_user_id', $authenticatedUser->id))
                 ->whereHas('roles', fn($query) => 
                    $query->where('roles.name', 'carrier'));
        } 
        else{
            return User::where('id',$authenticatedUser->id);
        }       
    }

    /**
     * Determine whether the user can view the specific user.
     */
    public function view(User $authenticatedUser, User $userToView): bool
    {
        // Superadmins and Admins can view any user-
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
            if ($userToView->createdBy && $userToView->createdBy->creator_user_id === $authenticatedUser->id) {
                // The authenticated user is the creator of $userToView
                return true; // or whatever action you want to take
            }
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