<?php

namespace App\Livewire\Users;

use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use App\Models\User;
use App\Policies\UserPolicy;

class UserIndex extends Component
{
    // The public property to hold the search term from the input field.
    // #[Url] makes the search term appear in the URL, which is good for sharing and refreshing.
    #[Url]
    public string $search = '';

    /**
     * This method is re-evaluated whenever a dependency changes,
     * such as the search property.
     * The #[On('updated')] attribute ensures it's recomputed.
     * @return \Illuminate\Support\Collection
     */
    #[Computed]
    #[On('updated')]
    public function users()
    {
        // Get the authorized query builder from the policy.
        // We use the authenticated user to determine what they are allowed to see.
        $authenticatedUser = auth()->user();
        $query = (new UserPolicy())->viewAny($authenticatedUser);

        // Apply search filters if a search term is present.
        if ($this->search) {
            $query->whereAny([
                'contact_person',
                'email',
                'contact_phone',
                'address',
                'city',
                'country',
            ], 'LIKE', '%' . $this->search . '%')
            // Add a search clause for the user who created the record.
            ->orWhereHas('createdBy', function ($query) {
                $query->where('contact_person', 'LIKE', '%' . $this->search . '%');
            })
            // Add a search clause for the user's roles.
            ->orWhereHas('roles', function ($query) {
                $query->where('name', 'LIKE', '%' . $this->search . '%');
            });
        }
        
        // Eager load the relationships to prevent N+1 query problems.
        $query->with('roles', 'createdBy');

        // Execute the query and return the results.
        return $query->get();
    }

    /**
     * Navigates to the user edit page.
     * @param string $slug
     * @return \Illuminate\Http\RedirectResponse
     */
    public function userEdit($slug)
    {
        return redirect()->route('users.edit', ['slug' => $slug]);
    }

    /**
     * Renders the view for the component.
     * @return \Illuminate\Contracts\View\View
     */
    public function render()
    {
        return view('livewire.users.user-index');
    }
}
