<?php

namespace App\Services;

use App\Models\Lane;
use App\Models\User;
use App\Enums\LaneStatus;
use App\Enums\VehiclePositionStatus;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;

class LaneService
{
    public function getVisibleLanesQuery(User $user, array $filters = []): Builder
    {
        $query = Lane::query()->with('createdBy');

        // Handle different user roles
        if ($user->hasRole('superadmin')) {
            // Superadmin sees all lanes
            return $this->applyFilters($query, $filters);
        }

        if ($user->hasRole('admin')) {
            // Admin sees all lanes except DRAFT
            $query->where('status', '!=', LaneStatus::DRAFT);
            return $this->applyFilters($query, $filters);
        }

        if ($user->hasAnyRole([
            'carrier',
            'procurement logistics associate',
            'operations logistics associate',
            'procurement executive associate'
        ])) {
            // These roles see their own lanes + additional rules
            $query->where(function (Builder $q) use ($user) {
                // User's own lanes
                $q->where('creator_id', $user->id);

                // Additional rules for specific roles
                if ($user->hasAnyRole([
                    'operations logistics associate',
                    'procurement executive associate'
                ])) {
                    // Add lanes created by users in their territory except DRAFT
                    $this->addTerritoryLanes($q, $user);
                }
            });
        } else {
            // Default user: only published/expired with specific vehicle status
            $query->whereIn('vehicle_status', [
                VehiclePositionStatus::NOT_CONTRACTED,
                VehiclePositionStatus::INAPPLICABLE
            ])
            ->whereIn('status', [
                LaneStatus::PUBLISHED,
                LaneStatus::EXPIRED
            ]);
        }

        return $this->applyFilters($query, $filters);
    }

    protected function addTerritoryLanes(Builder $query, User $user): void
    {
        // Get users in the same territory
        $territoryUserIds = $user->territories()
            ->with('users')
            ->get()
            ->flatMap(fn($territory) => $territory->users->pluck('id'))
            ->unique()
            ->toArray();

        // Add OR condition for territory users' lanes except DRAFT
        $query->orWhere(function (Builder $q) use ($territoryUserIds) {
            $q->whereIn('creator_id', $territoryUserIds)
              ->where('status', '!=', LaneStatus::DRAFT);
        });
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        return $query->when($filters['search'] ?? null, function (Builder $q, $search) {
            $q->whereAny([
                'destination',
                'location', 
                'cityfrom',
                'cityto',
                'countryfrom',
                'countryto'
            ], 'LIKE', "%{$search}%");
        })
        ->when($filters['trailerFilters'] ?? [], function (Builder $q, $trailerFilters) {
            $q->whereIn('trailer', $trailerFilters);
        })
        ->when($filters['statusFilters'] ?? [], function (Builder $q, $statusFilters) {
            // Custom status filter logic if needed
        })
        ->when($filters['routeFilters'] ?? [], function (Builder $q, $routeFilters) {
            // Custom route filter logic if needed
        });
    }

    public function getVisibleLanes(User $user, array $filters = [], int $perPage = 9)
    {
        return $this->getVisibleLanesQuery($user, $filters)
            ->latest()
            ->paginate($perPage);
    }
}