<?php

namespace App\Models;

use App\Enums\TenderOfferStatus;
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
        'carrier_id',
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

        // Recalculate rankings after any offer is saved
        static::saved(function (TenderOffer $offer) {
            $offer->recalculateRankings();
        });

        static::deleted(function (TenderOffer $offer) {
            $offer->recalculateRankings();
        });
    }

    // -----------------------------------------------
    // Relationships
    // -----------------------------------------------

    public function tenderable(): MorphTo
    {
        return $this->morphTo();
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'carrier_id');
    }

    public function awardedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'awarded_by');
    }

    // -----------------------------------------------
    // Ranking
    // -----------------------------------------------

    /**
     * Recalculate ranked_position for all active offers on this tender.
     * Freight (full_budget / rate_of_carriage) → lowest amount wins (asc).
     * Lane → highest amount wins (desc).
     */
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
            self::withoutEvents(fn() =>
                $offer->updateQuietly(['ranked_position' => $index + 1])
            );
        }
    }

    // -----------------------------------------------
    // Scopes
    // -----------------------------------------------

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

    // -----------------------------------------------
    // Helpers
    // -----------------------------------------------

    public function isAwarded(): bool
    {
        return $this->status === TenderOfferStatus::AWARDED;
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }
}