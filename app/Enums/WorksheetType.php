<?php

namespace App\Enums;

enum WorksheetType: string
{
    case Scouting = 'scouting';
    case Daily    = 'daily';
    case Weekly   = 'weekly';
    case Monthly  = 'monthly';

    // ── Presentation ─────────────────────────────────────────────

    public function label(): string
    {
        return match ($this) {
            self::Scouting => 'Scouting',
            self::Daily    => 'Daily',
            self::Weekly   => 'Weekly',
            self::Monthly  => 'Monthly',
        };
    }

    /**
     * Short description shown on the type-picker card.
     */
    public function description(): string
    {
        return match ($this) {
            self::Scouting => 'Ad-hoc partner scouting. Multiple active worksheets allowed.',
            self::Daily    => 'One active daily worksheet at a time. Entire worksheet locks after 8 hours.',
            self::Weekly   => 'One active weekly worksheet at a time. Entire worksheet locks after 8 hours.',
            self::Monthly  => 'One active monthly worksheet at a time. Entire worksheet locks after 8 hours.',
        };
    }

    /**
     * Tailwind classes for the read-only badge shown in lists and headers.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Scouting => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::Daily    => 'bg-sky-50 text-sky-700 border-sky-200',
            self::Weekly   => 'bg-violet-50 text-violet-700 border-violet-200',
            self::Monthly  => 'bg-amber-50 text-amber-700 border-amber-200',
        };
    }

    /**
     * Heroicon name used on the type-picker card.
     */
    public function icon(): string
    {
        return match ($this) {
            self::Scouting => 'map',
            self::Daily    => 'sun',
            self::Weekly   => 'calendar-days',
            self::Monthly  => 'calendar',
        };
    }

    // ── Business rules ───────────────────────────────────────────

    /**
     * Scouting allows unlimited concurrent active worksheets per user.
     * Daily / Weekly / Monthly are capped at one active instance per user.
     */
    public function allowsMultipleActive(): bool
    {
        return $this === self::Scouting;
    }

    /**
     * Daily / Weekly / Monthly: 8-hour window measured from worksheet created_at.
     * Absolute lock — nobody (owner, admin, anyone) may edit after it closes.
     *
     * Scouting: window is per-entry, measured from entry completed_at.
     * Owner / admins retain edit access after the per-entry window closes.
     */
    public function hasWorksheetLevelEditWindow(): bool
    {
        return $this !== self::Scouting;
    }

    /**
     * Edit-window duration in hours.
     */
    public function editWindowHours(): int
    {
        return 8;
    }
}