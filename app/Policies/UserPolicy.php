<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use IlluminateSupportCollection;
use Closure;

class UserPolicy
{
    /**
     * Determine whether the user can view any users.
     */
    public function viewAny(User $user): Builder
    {
        if ($user->hasAnyRole(['superadmin', 'admin'])) {
            return User::query();
        }

        $territoryIds = $user->territories()->pluck('territories.id');

        return match (true) {
            $user->hasRole('marketing logistics associate')
            => $this->scopeByClients($user, ['shipper']),

            $user->hasRole('procurement logistics associate')
            => $this->scopeByClients($user, ['carrier']),

            $user->hasRole('operations logistics associate')
            => User::where(function ($q) use ($user, $territoryIds) {
                $q->where($this->getClientConstraint($user, ['shipper', 'carrier']))
                    ->orWhere($this->getAssociateConstraint($territoryIds, ['marketing logistics associate', 'procurement logistics associate']));
            }),

            $user->hasRole('logistics operations executive')
            => User::where(function ($q) use ($user, $territoryIds) {
                $q->where($this->getClientConstraint($user, ['shipper', 'carrier']))
                    ->orWhere($this->getAssociateConstraint($territoryIds, [
                        'marketing logistics associate',
                        'procurement logistics associate',
                        'operations logistics associate'
                    ]));
            }),

            default => User::where('id', $user->id),
        };
    }

    /**
     * Determine whether the user can view the specific user.
     */
    public function view(User $auth, User $target): bool
    {
        if ($auth->hasAnyRole(['superadmin', 'admin'])) {
            return true;
        }

        $territoryIds = $auth->territories()->pluck('territories.id');

        $isMatch = match (true) {
            $auth->hasRole('marketing logistics associate')
            => $this->getClientConstraint($auth, ['shipper']),

            $auth->hasRole('procurement logistics associate')
            => $this->getClientConstraint($auth, ['carrier']),

            $auth->hasRole('operations logistics associate')
            => function ($q) use ($auth, $territoryIds) {
                $q->where($this->getClientConstraint($auth, ['shipper', 'carrier']))
                    ->orWhere($this->getAssociateConstraint($territoryIds, ['marketing logistics associate', 'procurement logistics associate']));
            },

            $auth->hasRole('logistics operations executive')
            => function ($q) use ($auth, $territoryIds) {
                $q->where($this->getClientConstraint($auth, ['shipper', 'carrier']))
                    ->orWhere($this->getAssociateConstraint($territoryIds, [
                        'marketing logistics associate',
                        'procurement logistics associate',
                        'operations logistics associate'
                    ]));
            },

            default => fn($q) => $q->where('id', $auth->id),
        };

        return User::where('id', $target->id)->where($isMatch)->exists();
    }

    // app/Policies/UserPolicy.php

    /**
     * Determine if the user can suspend the target user.
     */
    public function suspend(User $auth, User $target): bool
    {
        // 1. Cannot suspend yourself
        if ($auth->id === $target->id) return false;

        // 2. Role Check: Only Admins, Superadmins, and Executives can suspend
        if (!$auth->hasAnyRole(['superadmin', 'admin', 'logistics operations executive'])) {
            return false;
        }

        // 3. Admin/Superadmin Logic: Can suspend anyone (except themselves, handled above)
        if ($auth->hasAnyRole(['superadmin', 'admin'])) {
            return true;
        }

        // 4. Logistics Operations Executive Logic
        if ($auth->hasRole('logistics operations executive')) {
            // Cannot suspend other high-level staff/admins
            if ($target->hasAnyRole(['superadmin', 'admin', 'logistics operations executive'])) {
                return false;
            }

            // Must be the creator OR in the same territory
            $isCreator = $target->createdBy()->where('user_creations.creator_user_id', $auth->id)->exists();

            $territoryIds = $auth->territories()->pluck('territories.id');
            $inTerritory = $target->territories()->whereIn('territories.id', $territoryIds)->exists();

            if ($isCreator || $inTerritory) {
                return true;
            }

            // If user is Shipper/Carrier, check Geographical Bounds
            if ($target->hasAnyRole(['shipper', 'carrier'])) {
                $bounds = $this->getGeographicalBounds($auth);
                return $target->buslocation()
                    ->where(fn($q) => $q->whereIn('country', $bounds['countries'])->orWhereIn('city', $bounds['cities']))
                    ->exists();
            }
        }

        return false;
    }

    private function scopeByClients(User $user, array $roles): Builder
    {
        return User::where($this->getClientConstraint($user, $roles));
    }

    private function getClientConstraint(User $user, array $roles): Closure
    {
        $bounds = $this->getGeographicalBounds($user);
        return function (Builder $query) use ($user, $roles, $bounds) {
            $query->whereHas('roles', fn($q) => $q->whereIn('name', $roles))
                ->where(function ($q) use ($user, $bounds) {
                    $q->whereHas('createdBy', fn($cb) => $cb->where('user_creations.creator_user_id', $user->id))
                        ->orWhereHas(
                            'buslocation',
                            fn($bl) =>
                            $bl->whereIn('country', $bounds['countries'])
                                ->orWhereIn('city', $bounds['cities'])
                        );
                });
        };
    }

    private function getAssociateConstraint(Collection $territoryIds, array $roles): Closure
    {
        return function (Builder $query) use ($territoryIds, $roles) {
            $query->whereHas('roles', fn($q) => $q->whereIn('name', $roles))
                ->whereHas('territories', fn($t) => $t->whereIn('territories.id', $territoryIds));
        };
    }

    private function getGeographicalBounds(User $user): array
    {
        $territories = $user->territories()->with(['countries', 'zimbabweCities', 'provinces.zimbabweCities'])->get();
        $countries = $territories->flatMap->countries->pluck('name')->unique()->reject(fn($n) => strtolower($n) === 'zimbabwe')->values()->toArray();
        $cities = $territories->flatMap->zimbabweCities->pluck('name')->concat($territories->flatMap->provinces->flatMap->zimbabweCities->pluck('name'))->unique()->values()->toArray();
        return compact('countries', 'cities');
    }

    public function create(User $auth): bool
    {
        return true;
    } // Simplified for brevity

    public function update(User $auth, User $target): bool
    {
        if ($auth->hasAnyRole(['superadmin', 'admin', 'logistics operations executive'])) return true;
        return optional($target->createdBy)->id === $auth->id ||
            $target->territories()->whereIn('territories.id', $auth->territories->pluck('id'))->exists();
    }
}
