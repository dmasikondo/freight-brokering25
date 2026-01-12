<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class UserPolicy
{
    public function viewAny(User $user): Builder
    {
        // One line of code handles all the territory logic!
        return User::visibleTo($user);
    }

    public function view(User $auth, User $target): bool
    {
        // 1. Always allow users to view their own profile
        if ($auth->id === $target->id) {
            return true;
        }

        // 2. Superadmins and Admins can view anyone
        if ($auth->hasAnyRole(['superadmin', 'admin'])) {
            return true;
        }

        // 3. Use the Model Scope to check if the target is within the staff's territory
        // This is the most efficient way as it reuses your existing visibility rules.
        return User::visibleTo($auth)
            ->where('id', $target->id)
            ->exists();
    }

    public function suspend(User $auth, User $target): bool
    {
        if ($auth->id === $target->id) return false;
        if ($auth->hasAnyRole(['superadmin', 'admin'])) return true;

        if ($auth->hasRole('logistics operations executive')) {
            if ($target->hasAnyRole(['superadmin', 'admin', 'logistics operations executive'])) {
                return false;
            }
            return User::visibleTo($auth)->where('id', $target->id)->exists();
        }

        return false;
    }

    public function update(User $auth, User $target): bool
    {
        if ($auth->hasAnyRole(['superadmin', 'admin', 'logistics operations executive'])) return true;

        return User::visibleTo($auth)->where('id', $target->id)->exists();
    }
}
