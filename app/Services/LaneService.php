<?php

namespace App\Services;

use App\Models\Lane;
use App\Models\User;
use App\Enums\LaneStatus;
use App\Enums\VehiclePositionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class LaneService
{
    /**
     * Builds the base query with visibility constraints and user filters.
     */
    public function getVisibleLanesQuery(?User $user, array $filters = []): Builder
    {
        $query = Lane::query()->with(['carrier', 'createdBy']);

        $query->where(function (Builder $mainQuery) use ($user) {
            // 1. PUBLIC VISIBILITY: Published lanes are visible to everyone
            $mainQuery->where(function ($q) {
                $q->where('status', LaneStatus::PUBLISHED)
                    ->whereIn('vehicle_status', [
                        VehiclePositionStatus::NOT_CONTRACTED,
                        VehiclePositionStatus::INAPPLICABLE
                    ]);
            });

            // 2. PRIVATE VISIBILITY: Extra lanes based on authentication/roles
            if ($user) {
                $mainQuery->orWhere(function (Builder $authQuery) use ($user) {
                    // User sees their own lanes regardless of status
                    $authQuery->where(function ($q) use ($user) {
                        $q->where('creator_id', $user->id)
                            ->orWhere('carrier_id', $user->id);
                    });

                    // Territory-based visibility for specific staff roles
                    if ($user->hasAnyRole(['operations logistics associate', 'procurement executive associate'])) {
                        $this->addTerritoryLanes($authQuery, $user);
                    }

                    // Admin override: See everything
                    if ($user->hasAnyRole(['admin', 'superadmin'])) {
                        $authQuery->orWhere('id', '>', 0);
                    }
                });
            }
        });

        return $this->applyFilters($query, $filters);
    }

    /**
     * Handles territory-based visibility logic.
     */
    protected function addTerritoryLanes(Builder $query, User $user): void
    {
        $territoryUserIds = $user->territories()
            ->with('users')
            ->get()
            ->flatMap(fn($territory) => $territory->users->pluck('id'))
            ->unique()
            ->toArray();

        $query->orWhere(function (Builder $q) use ($territoryUserIds) {
            $q->whereIn('creator_id', $territoryUserIds)
                ->where('status', '!=', LaneStatus::DRAFT);
        });
    }

    /**
     * Applies user-specific filters and search constraints.
     */
    protected function applyFilters(Builder $query, array $filters): Builder
    {
        return $query
            // General Search using whereAny
            ->when($filters['search'] ?? null, function (Builder $q, $search) {
                $q->whereAny([
                    'destination',
                    'location',
                    'cityfrom',
                    'cityto',
                    'countryfrom',
                    'countryto',
                    'capacity'
                ], 'LIKE', "%{$search}%");
            })

            // Carrier & Creator Search (Restricted to Backend Staff)
            ->when($filters['carrier_search'] ?? null, function ($q, $carrierSearch) {
                $staffRoles = ['admin', 'superadmin', 'operations logistics associate', 'procurement executive associate'];
                if (Auth::user()?->hasAnyRole($staffRoles)) {
                    $q->where(function ($sub) use ($carrierSearch) {
                        $sub->whereHas('carrier', function ($query) use ($carrierSearch) {
                            $query->whereAny([
                                'contact_person',
                                'organisation',
                                'identification_number'
                            ], 'LIKE', "%{$carrierSearch}%");
                        })
                            ->orWhereHas('createdBy', function ($query) use ($carrierSearch) {
                                $query->whereAny([
                                    'contact_person',
                                    'email'
                                ], 'LIKE', "%{$carrierSearch}%");
                            });
                    });
                }
            })

            // Standard Enums and Value Filters
            ->when($filters['trailerFilters'] ?? [], fn($q, $trailers) => $q->whereIn('trailer', $trailers))
            ->when($filters['status'] ?? null, fn($q, $status) => $q->where('status', $status))
            ->when($filters['vehicle_status'] ?? null, fn($q, $vStatus) => $q->where('vehicle_status', $vStatus))
            ->when($filters['min_rate'] ?? null, fn($q, $min) => $q->where('rate', '>=', (float)$min))
            ->when($filters['max_rate'] ?? null, fn($q, $max) => $q->where('rate', '<=', (float)$max))
            ->when($filters['rate_type'] ?? null, fn($q, $type) => $q->where('rate_type', $type))
            ->when($filters['available_date'] ?? null, fn($q, $date) => $q->whereDate('availability_date', $date));
    }

    /**
     * Executes the query with sorting and pagination.
     */
    public function getVisibleLanes(?User $user, array $filters = [], int $perPage = 12)
    {
        return $this->getVisibleLanesQuery($user, $filters)
            ->latest()
            ->paginate($perPage);
    }

}
