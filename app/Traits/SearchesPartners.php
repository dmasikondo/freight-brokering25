<?php

namespace App\Traits;

use App\Models\User;
use App\Policies\UserPolicy;

/**
 * Policy-scoped partner search for Livewire worksheet components.
 *
 * Mirrors the search logic in user.index but stripped to just the
 * multi-column name/org/email/id search — no role or compliance filters.
 *
 * UserPolicy::viewAny() returns an Eloquent Builder already scoped to
 * users visible to the authenticated user. We layer whereAny on top.
 */
trait SearchesPartners
{
    /**
     * Return policy-scoped users matching $term across four columns.
     * Returns an empty Eloquent Collection when $term is blank.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\User>
     */
    protected function searchPartners(string $term, int $limit = 6): \Illuminate\Database\Eloquent\Collection
    {
        if (blank($term)) {
            return User::whereNull('id')->get(); // typed empty Eloquent Collection
        }

        $query = (new UserPolicy())->viewAny(auth()->user());

        $query->where(function ($q) use ($term) {
            $q->whereAny([
                'contact_person',
                'organisation',
                'email',
                'identification_number',
            ], 'LIKE', '%' . trim($term) . '%');
        });

        return $query->limit($limit)->get();
    }
}