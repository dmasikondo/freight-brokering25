<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorksheetHeader;
use Illuminate\Auth\Access\Response;

class WorksheetHeaderPolicy
{
    /**
     * Define the backend staff roles allowed to access worksheets.
     */
    protected array $backendStaff = [
        'superadmin',
        'admin',
        'marketing logistics associate',
        'procurement logistics associate',
        'operations logistics associate',
        'logistics operations executive'
    ];

    /**
     * Only backend staff can view the list/index.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole($this->backendStaff);
    }

    /**
     * Users can only view a specific worksheet if they are backend staff 
     * AND (they own it OR they are an admin/superadmin).
     */
    public function view(User $user, WorksheetHeader $worksheet): bool
    {
        if (! $user->hasAnyRole($this->backendStaff)) return false;

        if ($user->hasAnyRole(['superadmin', 'admin'])) return true;

        return $user->id === $worksheet->user_id || $worksheet->sharedWith->contains($user->id);
    }

    /**
     * Only backend staff can create new worksheets.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole($this->backendStaff);
    }

    public function update(User $user, WorksheetHeader $worksheet): bool
    {
        // If it's already completed, nobody (except maybe superadmin) should edit it
        if ($worksheet->is_completed && !$user->hasAnyRole(['superadmin', 'admin'])) {
            return false;
        }

        // Allow edit if owner OR if shared with this user
        $isOwner = $user->id === $worksheet->user_id;
        $isShared = $worksheet->sharedWith->contains($user->id);

        return $isOwner || $isShared;
    }

    /**
     * Determine if the user can see worksheets from all authors.
     */
    public function viewGlobal(User $user): bool
    {
        // This is the logic that specifically authorizes the "Global View" toggle
        return $user->hasAnyRole(['superadmin', 'admin']);
    }
}
