<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorksheetHeader;
use App\Models\WorksheetEntry;

class WorksheetHeaderPolicy
{
    /**
     * Who can view any worksheet list (index/archive page).
     * Backend staff roles only.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            'superadmin', 'admin',
            'marketing logistics associate',
            'procurement logistics associate',
            'operations logistics associate',
            'logistics operations executive',
        ]);
    }

    /**
     * Who can view a specific worksheet (show page).
     * Owner, any collaborator shared on the worksheet, or admin.
     */
    public function view(User $user, WorksheetHeader $worksheet): bool
    {
        return (int) $user->id === (int) $worksheet->user_id
            || $worksheet->sharedWith->contains($user->id)
            || $user->hasAnyRole(['superadmin', 'admin']);
    }

    /**
     * Who can create a new worksheet.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            'superadmin', 'admin',
            'marketing logistics associate',
            'procurement logistics associate',
            'operations logistics associate',
            'logistics operations executive',
        ]);
    }

    /**
     * Who can interact with (complete entries on) a worksheet.
     *
     * - Completed worksheets: admins/superadmins only.
     * - Active worksheets: owner or shared collaborator.
     */
    public function update(User $user, WorksheetHeader $worksheet): bool
    {
        if ($worksheet->is_completed && ! $user->hasAnyRole(['superadmin', 'admin'])) {
            return false;
        }

        $isOwner  = (int) $user->id === (int) $worksheet->user_id;
        $isShared = $worksheet->sharedWith->contains($user->id);

        return $isOwner || $isShared;
    }

    /**
     * Who can edit a COMPLETED worksheet entry's data (activity, feedback, etc.).
     *
     * This combines two independent concerns:
     *
     * 1. ACCESS  — does the user have worksheet access at all?  (via `update` above)
     * 2. TIME    — is the entry still within its 8-hour edit window?
     *
     * After the window closes the rules diverge by worksheet type:
     *   - Daily / Weekly / Monthly → absolute lock for EVERYONE (no exceptions).
     *   - Scouting                 → owner and admins retain edit access indefinitely.
     *
     * Usage in Livewire components:
     *   $canEdit = $this->authorize('updateEntry', [$entry->header, $entry]);
     *   // or check without throwing:
     *   $canEdit = auth()->user()->can('updateEntry', [$entry->header, $entry]);
     */
    public function updateEntry(User $user, WorksheetHeader $worksheet, WorksheetEntry $entry): bool
    {
        // Step 1 — basic worksheet access (reuse existing policy method)
        if (! $this->update($user, $worksheet)) {
            return false;
        }

        // Step 2 — time window
        if ($entry->withinEntryEditWindow()) {
            return true;
        }

        // Outside window: daily/weekly/monthly are permanently locked for everyone
        if ($worksheet->worksheet_type->hasWorksheetLevelEditWindow()) {
            return false;
        }

        // Outside window, scouting: only owner or admin
        return (int) $user->id === (int) $worksheet->user_id
            || $user->hasAnyRole(['superadmin', 'admin']);
    }

    /**
     * Who can globally view worksheets from ALL users (admin overview).
     */
    public function viewGlobal(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'admin']);
    }

    /**
     * Who can delete a worksheet entirely.
     * Superadmin only to prevent accidental data loss.
     */
    public function delete(User $user, WorksheetHeader $worksheet): bool
    {
        return $user->hasRole('superadmin');
    }
}