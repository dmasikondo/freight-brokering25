<?php

namespace App\Policies;

use App\Enums\LaneStatus;
use App\Enums\VehiclePositionStatus;
use App\Models\Lane;
use App\Models\User;
use App\Services\LaneService;
use Illuminate\Auth\Access\Response;

class LanePolicy
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
    public function view(User $user, Lane $lane): bool
    {
        // 1. If the lane is Published, any authenticated user can view it
        if ($lane->status === LaneStatus::PUBLISHED) {
            return true;
        }

        // 2. Otherwise, only those who have "Update" permissions can view it
        // This includes Staff in that territory OR the Carrier who owns the pending lane
        return $this->update($user, $lane);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        $managementRoles = [
            'admin',
            'superadmin',
            'operations logistics associate',
            'procurement executive associate'
        ];

        // Allowed if you are Staff OR a Carrier
        return $user->hasAnyRole($managementRoles) || $user->hasAnyRole('carrier');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Lane $lane): bool
    {
        // Case A: User is a Carrier
        if ($user->hasAnyRole('carrier')) {
            return $lane->creator_id === $user->id
                && $lane->status === LaneStatus::SUBMITTED // "Pending"
                && $lane->vehicle_status === VehiclePositionStatus::INAPPLICABLE;
        }

        // Case B: User is Staff
        return $this->isAuthorizedStaff($user, $lane);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Lane $lane): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Lane $lane): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Lane $lane): bool
    {
        return false;
    }

    public function viewActivityLog(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'superadmin']);
    }

    public function updateStatus(User $user, Lane $lane): bool
    {
        return $this->isAuthorizedStaff($user, $lane);
    }
    protected function isAuthorizedStaff(User $user, Lane $lane): bool
    {
        $managementRoles = [
            'admin',
            'superadmin',
            'operations logistics associate',
            'procurement executive associate'
        ];

        if (!$user->hasAnyRole($managementRoles)) {
            return false;
        }

        return app(LaneService::class)
            ->getVisibleLanesQuery($user)
            ->where('lanes.id', $lane->id)
            ->exists();
    }
}
