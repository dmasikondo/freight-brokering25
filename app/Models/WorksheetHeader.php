<?php

namespace App\Models;

use App\Enums\WorksheetType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorksheetHeader extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'worksheet_type',
        'is_completed',
    ];

    protected $casts = [
        'worksheet_type' => WorksheetType::class,
        'is_completed'   => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────

    public function entries(): HasMany
    {
        return $this->hasMany(WorksheetEntry::class, 'header_id')
            ->orderBy('sort_order', 'asc');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sharedWith()
    {
        return $this->belongsToMany(User::class, 'worksheet_header_user')
            ->withTimestamps();
    }

    // ── Sequence lock (adding new partners) ───────────────────────

    /**
     * Can new partners still be added to this worksheet's sequence?
     *
     * For all types: sequence is locked 8 hours after created_at.
     * This is separate from entry editing — interaction always continues.
     */
    public const SEQUENCE_LOCK_HOURS = 8;

    public function sequenceLocked(): bool
    {
        return $this->created_at->diffInHours(now()) >= self::SEQUENCE_LOCK_HOURS;
    }

    public function sequenceLockReason(): ?string
    {
        if (! $this->sequenceLocked()) {
            return null;
        }

        $lockedAt = $this->created_at->addHours(self::SEQUENCE_LOCK_HOURS);

        return 'Partner sequence locked ' . $lockedAt->diffForHumans()
            . '. No new partners can be added, but interactions can continue.';
    }

    public function sequenceMinutesRemaining(): int
    {
        $closeAt = $this->created_at->addHours(self::SEQUENCE_LOCK_HOURS);
        return max(0, (int) now()->diffInMinutes($closeAt, false));
    }

    // ── Progress helpers ──────────────────────────────────────────

    public function completedCount(): int
    {
        return $this->entries()->whereNotNull('completed_at')->count();
    }

    public function totalCount(): int
    {
        return $this->entries()->count();
    }

    public function progressPercent(): float
    {
        $total = $this->totalCount();
        return $total > 0 ? round(($this->completedCount() / $total) * 100) : 0;
    }

    // ── Edit window (kept for backward compat, delegates to entry logic) ──

    /**
     * Whether the worksheet type uses a worksheet-level edit window.
     * Now only used to determine lock messaging; actual locking is per-entry.
     */
    public function withinEditWindow(): bool
    {
        return true; // entry-level logic handles all locking now
    }

    public function canBeEditedBy(User $user): bool
    {
        return true; // delegates to WorksheetEntry::canBeEditedBy()
    }

    public function editLockReason(): ?string
    {
        return null;
    }

    public function editWindowMinutesRemaining(): int
    {
        return $this->sequenceMinutesRemaining();
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeActiveOfType($query, WorksheetType $type, int $userId): void
    {
        $query->where('user_id', $userId)
              ->where('worksheet_type', $type->value)
              ->where('is_completed', false);
    }
}