<?php

namespace App\Models;

use App\Enums\TenderOfferStatus;
use App\Notifications\TenderOfferNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class TenderOffer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'tenderable_type',
        'tenderable_id',
        'bidder_id',
        'freight_id',
        'amount',
        'proposed_pickup_date',
        'proposed_delivery_date',
        'notes',
        'status',
        'ranked_position',
        'awarded_at',
        'awarded_by',
        'rejection_reason',
    ];

    protected $casts = [
        'amount'                 => 'decimal:2',
        'proposed_pickup_date'   => 'date',
        'proposed_delivery_date' => 'date',
        'awarded_at'             => 'datetime',
        'status'                 => TenderOfferStatus::class,
    ];

    protected static function booted(): void
    {
        static::creating(function (TenderOffer $offer) {
            $offer->uuid = (string) Str::uuid();
        });

        static::saved(function (TenderOffer $offer) {
            $offer->recalculateRankings();
        });

        static::deleted(function (TenderOffer $offer) {
            $offer->recalculateRankings();
        });
    }

    // ---------------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------------

    public function tenderable(): MorphTo
    {
        return $this->morphTo();
    }

    public function bidder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'bidder_id');
    }

    public function awardedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'awarded_by');
    }

    public function freight(): BelongsTo
    {
        return $this->belongsTo(Freight::class);
    }

    // ---------------------------------------------------------------
    // Ranking
    // ---------------------------------------------------------------

    public function recalculateRankings(): void
    {
        $tenderable = $this->tenderable;
        if (!$tenderable) return;

        $rankOrder = $tenderable instanceof \App\Models\Lane ? 'desc' : 'asc';

        $offers = self::where('tenderable_type', $this->tenderable_type)
            ->where('tenderable_id', $this->tenderable_id)
            ->whereIn('status', [
                TenderOfferStatus::PENDING->value,
                TenderOfferStatus::SHORTLISTED->value,
            ])
            ->orderBy('amount', $rankOrder)
            ->get();

        foreach ($offers as $index => $offer) {
            self::withoutEvents(
                fn() =>
                $offer->updateQuietly(['ranked_position' => $index + 1])
            );
        }
    }

    // ---------------------------------------------------------------
    // Notification helpers
    // ---------------------------------------------------------------

    /**
     * Notify staff (admin, superadmin, logistics operations executive)
     * when a bidder acts on an offer.
     *
     * @param string $event  offer_submitted | offer_revised | offer_withdrawn
     */
    public function notifyStaff(string $event): void
    {
        $staffRoles = ['superadmin', 'admin', 'logistics operations executive'];

        User::whereHas('roles', fn($q) => $q->whereIn('name', $staffRoles))
            ->get()
            ->each(
                fn(User $staff) =>
                $staff->notify(new TenderOfferNotification($this, $event))
            );
    }

    /**
     * Notify the bidder when staff acts on their offer.
     *
     * @param string $event  offer_shortlisted | offer_rejected | offer_awarded
     */
    public function notifyBidder(string $event): void
    {
        $this->bidder?->notify(new TenderOfferNotification($this, $event));
    }

    // ---------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            TenderOfferStatus::PENDING->value,
            TenderOfferStatus::SHORTLISTED->value,
        ]);
    }

    public function scopeForTenderable($query, Model $tenderable)
    {
        return $query
            ->where('tenderable_type', get_class($tenderable))
            ->where('tenderable_id', $tenderable->id);
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    public function isAwarded(): bool
    {
        return $this->status === TenderOfferStatus::AWARDED;
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }
}
