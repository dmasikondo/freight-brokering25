<?php

namespace App\Livewire\Users;

use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Str;
use Livewire\WithPagination;

class UserIndex extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $sortBy = 'latest'; // latest, name_asc, name_desc, org_asc, org_desc

    #[Url(history: true)]
    public string $filterRole = '';

    #[Url(history: true)]
    public string $statusFilter = 'all'; 

    public int $perPage = 25;
    public int $limit = 25;

    public function updatedSearch() { $this->resetPage(); $this->limit = $this->perPage; }
    public function updatedSortBy() { $this->resetPage(); }
    public function updatedFilterRole() { $this->resetPage(); }
    public function updatedStatusFilter() { $this->resetPage(); }

    public function loadMore()
    {
        $this->limit += $this->perPage;
    }

    #[Computed]
    public function users()
    {
        $authenticatedUser = auth()->user();
        $query = (new UserPolicy())->viewAny($authenticatedUser);

        // 1. Search Logic (Search by Name, Org, Email, ID, Role)
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('contact_person', 'LIKE', '%' . $this->search . '%')
                  ->orWhere('organisation', 'LIKE', '%' . $this->search . '%')
                  ->orWhere('email', 'LIKE', '%' . $this->search . '%')
                  ->orWhere('id', 'LIKE', '%' . $this->search . '%') 
                  ->orWhereHas('roles', fn($sub) => $sub->where('name', 'LIKE', '%' . $this->search . '%'));                  
            });
        }

        // 2. Scoped Filter
        if ($this->filterRole) {
            $query->whereHas('roles', fn($q) => $q->where('name', $this->filterRole));
        }

        // 3. Status/Compliance Logic (Applied ONLY to Carriers)
        if ($this->statusFilter !== 'all') {
            $query->whereHas('roles', fn($q) => $q->where('name', 'carrier'));
            
            match ($this->statusFilter) {
                'fully_registered' => $query->whereHas('buslocation')->whereHas('fleets')->whereHas('directors', null, '>=', 2)->whereHas('traderefs', null, '>=', 2),
                'partial_scoped' => $query->where(fn($sub) => $sub->whereDoesntHave('buslocation')->orWhereDoesntHave('fleets')),
                'unmapped_global' => $query->whereDoesntHave('buslocation'),
                default => null
            };
        }

        // 4. Robust Sorting (Corrected table name to role_user)
        match ($this->sortBy) {
            'name_asc' => $query->orderBy('contact_person', 'asc'),
            'name_desc' => $query->orderBy('contact_person', 'desc'),
            'org_asc' => $query->orderBy('organisation', 'asc'),
            'org_desc' => $query->orderBy('organisation', 'desc'),
            default => $query->latest()
        };

        return $query->with(['roles', 'createdBy', 'buslocation'])->paginate($this->limit);
    }

    #[Computed]
    public function viewableRoles()
    {
        $user = auth()->user();
        return match (true) {
            $user->hasAnyRole(['superadmin', 'admin', 'logistics operations executive']) => ['shipper', 'carrier', 'marketing logistics associate', 'procurement logistics associate', 'operations logistics associate'],
            $user->hasRole('operations logistics associate') => ['shipper', 'carrier', 'marketing logistics associate', 'procurement logistics associate'],
            $user->hasRole('marketing logistics associate') => ['shipper'],
            $user->hasRole('procurement logistics associate') => ['carrier'],
            default => []
        };
    }

    public function highlight(string $text, string $search)
    {
        if (empty($search)) return $text;
        $highlighted = preg_replace("/($search)/i", '<span class="bg-yellow-100 font-black text-slate-900 px-0.5 rounded shadow-sm">$1</span>', $text);
        return Str::of($highlighted);
    }
}
