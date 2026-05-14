<?php

namespace App\Policies;

use App\Models\TenderOffer;
use App\Models\User;
use App\Models\Freight;
use App\Models\Lane;
use App\Enums\TenderOfferStatus;

class TenderOfferPolicy
{
    // ---------------------------------------------------------------
    // Role helpers — single source of truth
    // ---------------------------------------------------------------

    /** Full platform staff — see everything, do everything */
    private function isStaff(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'admin', 'logistics operations executive']);
    }

    /** Admins only — destructive actions like revoke */
    private function isAdmin(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'admin']);
    }

    // ---------------------------------------------------------------
    // Tenderable owner helper — polymorphic-aware
    // Freight owner: shipper_id / creator_id
    // Lane owner:    carrier_id / creator_id
    // ---------------------------------------------------------------

    private function isTenderableOwner(User $user, Freight|Lane $tenderable): bool
    {
        if ($tenderable instanceof Freight) {
            if (isset($tenderable->creator_id) && $tenderable->creator_id === $user->id) return true;
            if (isset($tenderable->shipper_id) && $tenderable->shipper_id === $user->id) return true;
        }

        if ($tenderable instanceof Lane) {
            if (isset($tenderable->creator_id) && $tenderable->creator_id === $user->id) return true;
            if (isset($tenderable->carrier_id) && $tenderable->carrier_id === $user->id) return true;
        }

        return false;
    }

    // ---------------------------------------------------------------
    // Policies
    // ---------------------------------------------------------------

    /**
     * Can the user submit a new offer on this tenderable?
     * Called as: $this->authorize('create', [TenderOffer::class, $tenderable])
     *
     * - Freight: bidder must be a carrier
     * - Lane:    bidder must be a shipper
     */
    public function create(User $user, Freight|Lane $tenderable): bool
    {
        $requiredRole = $tenderable instanceof Freight ? 'carrier' : 'shipper';

        if (!$user->hasAnyRole([$requiredRole])) return false;

        if ($tenderable->status->value !== 'published') return false;

        if ($this->isTenderableOwner($user, $tenderable)) return false;

        // For lane bids, shipper must have at least one published freight
        if ($tenderable instanceof Lane) {
            if (!Freight::where(function ($q) use ($user) {
                $q->where('shipper_id', $user->id)
                    ->orWhere('creator_id', $user->id);
            })
                ->where('status', 'published')
                ->exists()) {
                return false;
            }
        }

        return !TenderOffer::forTenderable($tenderable)
            ->where('bidder_id', $user->id)
            ->active()
            ->exists();
    }

    /**
     * Can the user revise their own offer?
     * Called as: $this->authorize('update', $offer)
     */
    public function update(User $user, TenderOffer $offer): bool
    {
        if ($offer->bidder_id !== $user->id) return false;
        if (!$offer->isActive()) return false;

        return $offer->tenderable?->status->value === 'published';
    }

    /**
     * Can the user withdraw their own offer?
     * Called as: $this->authorize('withdraw', $offer)
     */
    public function withdraw(User $user, TenderOffer $offer): bool
    {
        return $offer->bidder_id === $user->id && $offer->isActive();
    }

    /**
     * Can the user shortlist or reject offers?
     * Called as: $this->authorize('manage', $offer)
     */
    public function manage(User $user, TenderOffer $offer): bool
    {
        if ($this->isStaff($user)) return true;

        $tenderable = $offer->tenderable;

        return $tenderable && $this->isTenderableOwner($user, $tenderable);
    }

    /**
     * Can the user award an offer?
     * Called as: $this->authorize('award', $offer)
     */
    public function award(User $user, TenderOffer $offer): bool
    {
        if ($this->isStaff($user)) return true;

        $tenderable = $offer->tenderable;

        return $tenderable && $this->isTenderableOwner($user, $tenderable);
    }

    /**
     * Can the user revoke an award? Admin / superadmin only.
     * Called as: $this->authorize('revoke', $offer)
     */
    public function revoke(User $user, TenderOffer $offer): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * Can the user see the full leaderboard (all bidders + amounts)?
     * Called as: $this->authorize('viewLeaderboard', [TenderOffer::class, $tenderable])
     *
     * Staff always can. The tenderable owner can (they need to award).
     * Plain bidders only ever see their own offer — handled in the component.
     */
    public function viewLeaderboard(User $user, Freight|Lane $tenderable): bool
    {
        if ($this->isStaff($user)) return true;

        return $this->isTenderableOwner($user, $tenderable);
    }
}
