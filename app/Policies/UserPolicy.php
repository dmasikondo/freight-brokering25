<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class UserPolicy
{
    /**
     * Determine whether the user can view any users.
     */
    public function viewAny(User $user): Builder
    {
        // 1. Administrative roles have unrestricted access
        if ($user->hasAnyRole(['superadmin', 'admin'])) {
            return User::query();
        }

        // 2. Define scoped access for operational roles
        return match (true) {
            $user->hasRole('marketing logistics associate') 
                => $this->applyScopedQuery($user, ['shipper']),

            $user->hasRole('procurement logistics associate') 
                => $this->applyScopedQuery($user, ['carrier']),

            $user->hasAnyRole(['operations logistics associate', 'procurement executive associate']) 
                => $this->applyScopedQuery($user, ['shipper', 'carrier']),

            default => User::where('id', $user->id),
        };
    }

    /**
     * Core logic to scope users based on roles, creators, and territories.
     */
    private function applyScopedQuery(User $user, array $roles): Builder
    {
        $bounds = $this->getGeographicalBounds($user);

        return User::whereHas('roles', fn($q) => $q->whereIn('name', $roles))
            ->where(function (Builder $query) use ($user, $bounds) {
                $query->whereHas('createdBy', fn($q) => 
                    $q->where('user_creations.creator_user_id', $user->id)
                )
                ->orWhereHas('buslocation', fn($q) => 
                    $q->whereIn('country', $bounds['countries'])
                      ->orWhereIn('city', $bounds['cities'])
                );
            });
    }

    /**
     * Centralized logic to extract territory-based allowed locations.
     */
    private function getGeographicalBounds(User $user): array
    {
        $territories = $user->territories()
            ->with(['countries', 'zimbabweCities', 'provinces.zimbabweCities'])
            ->get();

        $countries = $territories->flatMap->countries
            ->pluck('name')
            ->unique()
            ->reject(fn($name) => strtolower($name) === 'zimbabwe')
            ->values()
            ->toArray();

        $cities = $territories->flatMap->zimbabweCities->pluck('name')
            ->concat($territories->flatMap->provinces->flatMap->zimbabweCities->pluck('name'))
            ->unique()
            ->values()
            ->toArray();

        return compact('countries', 'cities');
    }

    public function view(User $auth, User $target): bool
    {
        // 1. Admins have unrestricted viewing access
        if ($auth->hasAnyRole(['superadmin', 'admin'])) {
            return true;
        }

        /**
         * 2. Scoped Access check for Operational Roles.
         * We leverage applyScopedQuery to ensure that the "Created By" or 
         * "Territory" logic is consistent between list and view views.
         */
        $targetRoles = match (true) {
            $auth->hasRole('marketing logistics associate') => ['shipper'],
            $auth->hasRole('procurement logistics associate') => ['carrier'],
            $auth->hasAnyRole([
                'operations logistics associate', 
                'logistics operations executive', 
                'procurement executive associate'
            ]) => ['shipper', 'carrier'],
            default => null
        };

        if ($targetRoles) {
            return $this->applyScopedQuery($auth, $targetRoles)
                ->where('users.id', $target->id)
                ->exists();
        }

        // 3. Fallback: Users can view their own profile
        return $auth->id === $target->id;
    }

    public function create(User $auth): bool
    {
        return $auth->hasAnyRole([
            'superadmin', 'admin', 'marketing logistics associate', 
            'procurement logistics associate', 'procurement executive associate',
            'operations logistics associate'
        ]);
    }

    public function update(User $auth, User $target): bool
    {
        if ($auth->hasAnyRole(['superadmin', 'admin', 'procurement executive associate'])) {
            return true;
        }

        // Logic split: Check creation ownership OR territory overlap
        $isCreator = optional($target->createdBy)->id === $auth->id;
        
        $hasTerritoryOverlap = $target->territories()
            ->whereIn('territories.id', $auth->territories->pluck('id'))
            ->exists();

        return $isCreator || $hasTerritoryOverlap;
    }
}