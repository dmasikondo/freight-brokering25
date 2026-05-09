<?php

namespace App\Models;

use App\Enums\PlannedAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorksheetEntry extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'header_id',
        'partner_name',
        'contact_details',
        'partner_type',
        'planned_action',
        'planned_action_custom',
        'activity',
        'feedback',
        'way_forward',
        'started_at',
        'completed_at',
        'sort_order',
        'reminder_at',
        'private_notes',
        'last_edited_by_id',
        'notified_as_reminder',
    ];

    protected $casts = [
        'partner_type'    => \App\Enums\PartnerType::class,
        'planned_action'  => PlannedAction::class,
        'started_at'      => 'datetime',
        'completed_at'    => 'datetime',
        'reminder_at'     => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────

    public function header(): BelongsTo
    {
        return $this->belongsTo(WorksheetHeader::class, 'header_id');
    }

    public function lastEditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_edited_by_id');
    }

    // ── Planned action display ────────────────────────────────────

    /**
     * Human-readable planned action label, including custom text if set.
     */
    public function plannedActionLabel(): string
    {
        if (! $this->planned_action) {
            return '—';
        }

        return $this->planned_action->displayLabel($this->planned_action_custom);
    }

    // ── Entry-level edit window ───────────────────────────────────

    /**
     * How long (in hours) a completed entry stays editable after completion.
     * Applies uniformly to ALL worksheet types.
     * Scouting: owner/admin retain access after the window.
     * Daily/Weekly/Monthly: absolute lock for everyone after the window.
     */
    public const ENTRY_EDIT_WINDOW_HOURS = 8;

    /**
     * Whether this entry is still inside its individual edit window.
     * Measured from completed_at. Incomplete entries always return true.
     */
    public function withinEntryEditWindow(): bool
    {
        if (! $this->completed_at) {
            return true;
        }

        return $this->completed_at->diffInHours(now()) < self::ENTRY_EDIT_WINDOW_HOURS;
    }

    /**
     * Can the given user edit this completed entry's data?
     *
     * Rule (all worksheet types):
     *   Within 8h of entry completion  → anyone with worksheet access
     *
     * After 8h:
     *   Scouting                       → only worksheet owner or admin/superadmin
     *   Daily / Weekly / Monthly       → nobody (absolute lock)
     */
    public function canBeEditedBy(User $user): bool
    {
        if ($this->withinEntryEditWindow()) {
            return true;
        }

        $header = $this->header;

        // After window: daily/weekly/monthly are permanently locked
        if ($header->worksheet_type->hasWorksheetLevelEditWindow()) {
            return false;
        }

        // After window: scouting allows owner / admin
        return $header->user_id === $user->id
            || $user->hasAnyRole(['admin', 'superadmin']);
    }

    /**
     * Human-readable reason why editing is blocked, or null if allowed.
     */
    public function editLockReason(): ?string
    {
        if ($this->withinEntryEditWindow()) {
            return null;
        }

        $lockedAt = $this->completed_at->addHours(self::ENTRY_EDIT_WINDOW_HOURS);
        $header   = $this->header;

        if ($header->worksheet_type->hasWorksheetLevelEditWindow()) {
            return 'Entry locked ' . $lockedAt->diffForHumans() . '. No further edits are permitted.';
        }

        return 'Edit window closed ' . $lockedAt->diffForHumans()
            . '. Only the worksheet owner or an admin may edit.';
    }

    /**
     * Minutes remaining in this entry's edit window (0 if already closed).
     */
    public function entryEditWindowMinutesRemaining(): int
    {
        if (! $this->completed_at) {
            return self::ENTRY_EDIT_WINDOW_HOURS * 60;
        }

        $closeAt = $this->completed_at->addHours(self::ENTRY_EDIT_WINDOW_HOURS);

        return max(0, (int) now()->diffInMinutes($closeAt, false));
    }
}