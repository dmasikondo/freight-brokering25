<?php

namespace App\Policies;

use App\Models\Freight;
use App\Models\User;
use App\Services\FreightService;
use App\Enums\FreightStatus;
use Illuminate\Auth\Access\Response;

class FreightPolicy
{

    /**
     * Determine if a user can access the full management list (Control Centre).
     */
    public function viewAny(User $user): bool
    {
        // If they pass the staff visibility check or own the freight, they can view the index
        return $this->isStaffOrOwner($user);
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
        return $user->hasAnyRole(['superadmin', 'admin', 'marketing logistics associate', 'shipper', 'logistics operations executive', 'operations logistics associate']);
    }

    public function update(User $user, Freight $freight): bool
    {
        // Case A: User is a Carrier
        if ($user->hasAnyRole('shipper')) {
            return $freight->creator_id === $user->id
                && $freight->status === FreightStatus::SUBMITTED; // "Pending"
        }

        // Case B: User is Staff
        return $this->isAuthorizedStaff($user, $freight);
    }

    public function delete(User $user, Freight $freight): bool
    {
        return $this->isStaffOrOwner($user, $freight);
    }
    public function updateStatus(User $user, Freight $freight): bool
    {
        return $this->isAuthorizedStaff($user, $freight);
    }
    public function viewAllShipperDetails(User $user, Freight $freight): bool
    {
        return $this->isStaffOrOwner($user, $freight);
    }
    public function viewSomeShipperDetails(User $user): bool
    {
        $staffRoles = [
            'admin',
            'superadmin',
            'logistics operations executive',
            'operations logistics associate',
            'marketing logistics associate',
            'procurement logistics associate'

        ];        
        return $user->hasAnyRole($staffRoles);
    }

        public function viewFreightActivityLog(User $user): bool
    {
        $staffRoles = [
            'admin',
            'superadmin',
        ];        
        return $user->hasAnyRole($staffRoles);
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
     * Logic for backend staff or record ownership.
     */
    private function isStaffOrOwner(User $user, ?Freight $freight = null): bool
    {
        // 1. Super Users / Admins
        if ($user->hasAnyRole(['admin', 'superadmin', 'operations logistics executive'])) {
            return true;
        }

        // 2. Staff members within scope (Marketing/Operations etc.)
        // Uses your existing 'visibleTo' scope logic
        if (User::query()->visibleTo($user)->where('id', $user->id)->exists()) {
            if ($user->hasAnyRole(['marketing logistics associate', 'logistics operations associate'])) {
                return true;
            } else {
                return false;
            }
        }

        // 3. Ownership check (if a specific freight is provided)
        if ($freight && $user->hasRole('shipper')) {
            return  $user->id == $freight->shipper_id;
        }
        // 4. user is the creator of freight
        if ($freight && $user) {
            return $user->id == $freight->creator_id;
        }
        return false;
    }

    protected function isAuthorizedStaff(User $user, Freight $freight): bool
    {
        $managementRoles = [
            'admin',
            'superadmin',
            'logistics operations executive',
            'operations logistics associate',
            'marketing logistics associate',

        ];

        if (!$user->hasAnyRole($managementRoles)) {
            return false;
        }

        return app(FreightService::class)
            ->getVisibleFreightsQuery($user)
            ->where('freights.id', $freight->id)
            ->exists();
    }
}
