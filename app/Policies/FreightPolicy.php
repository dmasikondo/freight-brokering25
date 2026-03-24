<?php

namespace App\Policies;

use App\Models\Freight;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FreightPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Freight $freight): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'admin', 'marketing logistics associate', 'shipper']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Freight $freight): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Freight $freight): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Freight $freight): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Freight $freight): bool
    {
        return false;
    }

    /**
     * Determine if the user can view sensitive shipper contact details.
     */
public function viewShipperDetails(User $user, Freight $freight): bool
    {
        // 1. Super Users always have access
        if ($user->hasAnyRole(['admin', 'superadmin'])) {
            return true;
        }

        // 2. If the user is a shipper, they only see details of their own freight
        if ($user->hasRole('shipper')) {
            return (int) $user->id === (int) $freight->shipper_id;
        }        

        // 3. Check if the user is part of the "Visible To" staff scope
        // This validates the roles: marketing logistics associate, operations logistics associate, etc.
        $isVisibleStaff = User::query()->visibleTo($user)->where('id', $user->id)->exists();

        if ($isVisibleStaff) {
            return true;
        }



        return false;
    }
}
