<?php

namespace App\Policies;

use App\Models\TenderOffer;
use App\Models\User;
use App\Models\Freight;
use App\Models\Lane;
use App\Enums\TenderOfferStatus;
use App\Enums\FreightStatus;

class TenderOfferPolicy
{
    /**
     * Can the user submit a new offer on this tenderable?
     */
    public function create(User $user, Freight|Lane $tenderable): bool
    {
        // Determine required bidder role
        $requiredRole = $tenderable instanceof Freight ? 'carrier' : 'shipper';

        if (!$user->hasRole($requiredRole)) return false;

        // Tenderable must be published
        if ($tenderable->status->value !== 'published') return false;

        // User must not already have an active offer
        return !TenderOffer::forTenderable($tenderable)
            ->where('bidder_id', $user->id)
            ->active()
            ->exists();
    }

    /**
     * Can the user revise their own offer?
     */
    public function update(User $user, TenderOffer $offer): bool
    {
        if ($offer->bidder_id !== $user->id) return false;
        if (!$offer->isActive()) return false;

        // Tenderable must still be published
        return $offer->tenderable?->status->value === 'published';
    }

    /**
     * can the back end staff manage offer issues
     */
    public function manage(User $user)
    {
        return $user->hasAnyRole(['superadmin','admin','logistics operations executive']);
    }
    
    /**
     * Can the user withdraw their own offer?
     */
    public function withdraw(User $user, TenderOffer $offer): bool
    {
        return $offer->bidder_id === $user->id && $offer->isActive();
    }

    /**
     * Can the user award an offer?
     * Staff + shipper (own freight) + carrier (own lane)
     */
    public function award(User $user, TenderOffer $offer): bool
    {
        $tenderable = $offer->tenderable;

        if ($user->hasAnyRole(['admin', 'superadmin', 'logistics operations executive'])) {
            return true;
        }

        if ($tenderable instanceof Freight) {
            return $tenderable->bidder_id === $user->id
                || $tenderable->creator_id === $user->id
                || $user->hasAnyRole(['marketing logistics associate', 'operations logistics associate']);
        }

        if ($tenderable instanceof Lane) {
            return $tenderable->bidder_id === $user->id
                || $tenderable->creator_id === $user->id;
        }

        return false;
    }

    /**
     * Can the user shortlist/reject offers?
     */
    public function moderate(User $user, TenderOffer $offer): bool
    {
        return $this->award($user, $offer);
    }

    /**
     * Can the user view the full leaderboard?
     */
    public function viewLeaderboard(User $user, Freight|Lane $tenderable): bool
    {
        if ($user->hasAnyRole(['admin', 'superadmin', 'logistics operations executive'])) {
            return true;
        }

        if ($tenderable instanceof Freight) {
            return $tenderable->shipper_id === $user->id
                || $tenderable->creator_id === $user->id
                || $user->hasAnyRole(['marketing logistics associate', 'operations logistics associate']);
        }

        if ($tenderable instanceof Lane) {
            return $tenderable->carrier_id === $user->id
                || $tenderable->creator_id === $user->id;
        }

        return false;
    }
}