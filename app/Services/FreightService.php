<?php

namespace App\Services;

use App\Models\Freight;
use App\Models\User;
use App\Enums\FreightStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class FreightService
{
    public const FREIGHT_CREATOR_ROLES = [
        'shipper', 'admin', 'superadmin', 'marketing logistics associate',
        'logistics operations executive', 'operations logistics associate',
    ];

    public const UNRESTRICTED_SHIPPER_ROLES = [
        'admin', 'superadmin', 'logistics operations executive',
    ];

    public const TERRITORY_RESTRICTED_ROLES = [
        'marketing logistics associate', 'operations logistics associate',
    ];

    /**
     * Builds the base query for Freight visibility.
     */
    public function getVisibleFreightsQuery(?User $user, array $filters = []): Builder
    {
        $query = Freight::query()->with(['shipper', 'createdBy']);

        $query->where(function (Builder $mainQuery) use ($user) {
            // Publicly visible: Published freights
            $mainQuery->where('status', FreightStatus::PUBLISHED);

            if ($user) {
                $mainQuery->orWhere(function (Builder $authQuery) use ($user) {
                    // Own freight
                    $authQuery->where('creator_id', $user->id)
                              ->orWhere('shipper_id', $user->id);

                    // Territory-based visibility
                    if ($user->hasAnyRole(self::TERRITORY_RESTRICTED_ROLES)) {
                        $this->addTerritoryFreights($authQuery, $user);
                    }

                    // Admin override
                    if ($user->hasAnyRole(self::UNRESTRICTED_SHIPPER_ROLES)) {
                        $authQuery->orWhere('id', '>', 0);
                    }
                });
            }
        });

        return $this->applyFilters($query, $filters);
    }

    /**
     * Applies filters for search, category, status, and dates.
     */
    protected function applyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->whereAny(['goods', 'origin_city', 'destination_city', 'category'], 'LIKE', "%{$search}%");
                });
            })
            // Staff-only search for specific shippers
            ->when($filters['shipper_search'] ?? null, function ($q, $shipperSearch) {
                $q->whereHas('shipper', function ($sub) use ($shipperSearch) {
                    $sub->whereAny(['organisation', 'contact_person', 'email'], 'LIKE', "%{$shipperSearch}%");
                });
            })
            ->when($filters['category'] ?? null, fn($q, $cat) => $q->where('category', $cat))
            ->when($filters['status'] ?? null, fn($q, $status) => $q->where('status', $status))
            ->when($filters['date_from'] ?? null, fn($q, $date) => $q->whereDate('loading_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn($q, $date) => $q->whereDate('loading_date', '<=', $date));
    }

    protected function addTerritoryFreights(Builder $query, User $user): void
    {
        $territoryUserIds = $this->getTerritoryUserIds($user);

        $query->orWhere(function (Builder $q) use ($territoryUserIds) {
            $q->whereIn('creator_id', $territoryUserIds)
              ->where('status', '!=', FreightStatus::DRAFT);
        });
    }

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

    public function canCreateFreight(User $user): bool
    {
        return $user->hasAnyRole(self::FREIGHT_CREATOR_ROLES);
    }
}