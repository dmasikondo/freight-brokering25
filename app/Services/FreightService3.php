<?php

namespace App\Services;

use App\Models\Freight;
use App\Models\User;
use App\Enums\FreightStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class FreightService
{
    /**
     * Roles that can create freight at all.
     */
    public const FREIGHT_CREATOR_ROLES = [
        'shipper',
        'admin',
        'superadmin',
        'marketing logistics associate',
        'logistics operations executive',
        'operations logistics associate',
    ];

    /**
     * Roles that can see ALL shippers regardless of territory.
     */
    public const UNRESTRICTED_SHIPPER_ROLES = [
        'admin',
        'superadmin',
        'logistics operations executive',
    ];

    /**
     * Roles that see only their territory's shippers.
     */
    public const TERRITORY_RESTRICTED_ROLES = [
        'marketing logistics associate',
        'operations logistics associate',
    ];

    /**
     * Builds the base query for Freight visibility.
     */
    public function getVisibleFreightsQuery(?User $user, array $filters = []): Builder
    {
        $query = Freight::query()->with(['shipper', 'createdBy']);

        $query->where(function (Builder $mainQuery) use ($user) {
            $mainQuery->where('status', FreightStatus::PUBLISHED);

            if ($user) {
                $mainQuery->orWhere(function (Builder $authQuery) use ($user) {
                    $authQuery->where('creator_id', $user->id)
                              ->orWhere('shipper_id', $user->id);

                    if ($user->hasAnyRole(self::TERRITORY_RESTRICTED_ROLES)) {
                        $this->addTerritoryFreights($authQuery, $user);
                    }

                    if ($user->hasAnyRole(self::UNRESTRICTED_SHIPPER_ROLES)) {
                        $authQuery->orWhere('id', '>', 0);
                    }
                });
            }
        });

        return $this->applyFilters($query, $filters);
    }

    /**
     * Returns a query for shipper search during freight creation,
     * scoped by the acting user's role and territories.
     *
     * - Shipper role: only themselves
     * - Territory-restricted staff: only shippers in their territories
     * - Unrestricted staff (admin, superadmin, logistics ops exec): all shippers
     */
    public function searchShippers(User $actingUser, string $searchTerm): \Illuminate\Database\Eloquent\Collection
    {
        // Shippers can only ever see themselves
        if ($actingUser->hasRole('shipper')) {
            return User::where('id', $actingUser->id)
                ->where(function ($q) use ($searchTerm) {
                    $q->where('contact_person', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('organisation', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('email', 'LIKE', "%{$searchTerm}%");
                })
                ->limit(1)
                ->get();
        }

        $query = User::query()->role('shipper');

        // Territory-restricted staff only see shippers within their territories
        if ($actingUser->hasAnyRole(self::TERRITORY_RESTRICTED_ROLES)) {
            $territoryUserIds = $this->getTerritoryUserIds($actingUser);
            $query->whereIn('id', $territoryUserIds);
        }

        // Unrestricted roles (admin, superadmin, logistics ops exec) fall through
        // with no additional scope — they see all shippers.

        return $query->where(function ($q) use ($searchTerm) {
            $q->where('contact_person', 'LIKE', "%{$searchTerm}%")
              ->orWhere('organisation', 'LIKE', "%{$searchTerm}%")
              ->orWhere('email', 'LIKE', "%{$searchTerm}%");
        })->limit(5)->get();
    }

    /**
     * Whether the given user is allowed to create/post freight at all.
     */
    public function canCreateFreight(User $user): bool
    {
        return $user->hasAnyRole(self::FREIGHT_CREATOR_ROLES);
    }

    /**
     * Whether the acting user should see a read-only view of their
     * own shipper identity (i.e. they are a shipper and cannot search others).
     */
    public function isShipperSelf(User $user): bool
    {
        return $user->hasRole('shipper');
    }

    /**
     * Whether the acting user has unrestricted shipper search access.
     */
    public function hasUnrestrictedShipperSearch(User $user): bool
    {
        return $user->hasAnyRole(self::UNRESTRICTED_SHIPPER_ROLES);
    }

    /**
     * Whether the acting user is territory-restricted for shipper search.
     */
    public function hasTerritoryRestrictedShipperSearch(User $user): bool
    {
        return $user->hasAnyRole(self::TERRITORY_RESTRICTED_ROLES);
    }

    /**
     * Collect all User IDs belonging to the territories assigned to $user.
     */
    public function getTerritoryUserIds(User $user): array
    {
        return $user->territories()
            ->with('users')
            ->get()
            ->flatMap(fn($t) => $t->users->pluck('id'))
            ->unique()
            ->values()
            ->toArray();
    }

    protected function addTerritoryFreights(Builder $query, User $user): void
    {
        $territoryUserIds = $this->getTerritoryUserIds($user);

        $query->orWhere(function (Builder $q) use ($territoryUserIds) {
            $q->whereIn('creator_id', $territoryUserIds)
              ->where('status', '!=', FreightStatus::DRAFT);
        });
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->whereAny(['goods', 'origin_city', 'destination_city', 'category'], 'LIKE', "%{$search}%");
            })
            ->when($filters['category'] ?? null, fn($q, $cat) => $q->where('category', $cat));
    }
}