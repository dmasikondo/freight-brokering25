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
    // #[Url] makes the search term appear in the URL.
    #[Url]
    public string $search = '';

    /**
     * This method is re-evaluated whenever a dependency changes,
     * such as the search property.
     * @return \Illuminate\Support\Collection
     */
    #[Computed]
    #[On('updated')]
    public function users()
    {
        // Get the authorized query builder from the policy.
        $authenticatedUser = auth()->user();
        $query = (new UserPolicy())->viewAny($authenticatedUser);

        // Apply search filters if a search term is present.
        if ($this->search) {
            $query->whereAny([
                'contact_person',
                'email',
                'contact_phone'
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
     * A helper method to highlight the search term in a given string.
     * @param string $text The text to be highlighted.
     * @param string $search The search term.
     * @return \Illuminate\Support\Stringable
     */
    private function highlight(string $text, string $search)
    {
        // If the search term is empty, return the original text.
        if (empty($search)) {
            return $text;
        }

        // Use preg_replace for a case-insensitive search and replace.
        // The '$1' in the replacement string refers to the captured search term.
        $highlighted = preg_replace("/($search)/i", '<span class="bg-yellow-200 font-bold text-black">$1</span>', $text);
        
        // The result is an HTML string, so return it as a Stringable to be rendered.
        return \Illuminate\Support\Str::of($highlighted);
    }
}
