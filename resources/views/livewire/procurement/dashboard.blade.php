<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Lane;
use App\Models\Territory;
use App\Models\ZimbabweCity;
use App\Models\Country;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public array $stats = [];
    public array $recentCarriers = [];
    public array $incompleteRegistrations = [];
    public array $recentLanes = []; 
    public array $assignedTerritories = [];

    public function mount()
    {
        $this->loadDashboard();
    }

    public function loadDashboard()
    {
        /** @var User $user */
        $user = Auth::user();
        
        // 1. Fetch territories with eager loaded relations
        $territoryCollection = $user->territories()
            ->with(['countries', 'zimbabweCities', 'provinces.zimbabweCities'])
            ->get();
            
        $this->assignedTerritories = $territoryCollection->toArray();

        // 2. Extract boundaries defensively for geographical scoping
        $bounds = $this->getGeographicalBounds($territoryCollection);

        // 3. Define the visibility boundary: (Created by me) OR (In my Territories)
        $scopedUserIds = User::query()
            ->where(function (Builder $mainQuery) use ($user, $bounds) {
                $mainQuery->whereHas('createdBy', fn($q) => $q->where('user_creations.creator_user_id', $user->id))
                    ->orWhereHas('buslocation', function ($q) use ($bounds) {
                        $q->where(function ($sub) use ($bounds) {
                            if (!empty($bounds['countries'])) $sub->whereIn('country', $bounds['countries']);
                            if (!empty($bounds['cities'])) {
                                $method = !empty($bounds['countries']) ? 'orWhereIn' : 'whereIn';
                                $sub->$method('city', $bounds['cities']);
                            }
                            if (empty($bounds['countries']) && empty($bounds['cities'])) $sub->whereRaw('1 = 0');
                        });
                    });
            })
            ->pluck('id');

        // 4. Incomplete Definition Logic:
        // A complete carrier has: >=1 buslocation, >=1 fleet, >=2 directors, >=2 traderefs
        $applyIncompleteFilter = function($q) {
            $q->where(function($sub) {
                $sub->whereDoesntHave('buslocation')
                    ->orWhereDoesntHave('fleets')
                    ->orWhereHas('directors', null, '<', 2)
                    ->orWhereHas('traderefs', null, '<', 2);
            });
        };

        $carriersBaseQuery = User::whereHas('roles', fn($q) => $q->where('name', 'carrier'));

        // A. Total number of incompletely registered carriers in user's territory/ownership
        $scopedIncompleteQuery = (clone $carriersBaseQuery)
            ->whereIn('id', $scopedUserIds)
            ->where($applyIncompleteFilter);

        // B. Total number of system's incompletely registered carriers without buslocation
        $systemNoLocationQuery = (clone $carriersBaseQuery)
            ->whereDoesntHave('buslocation');

        // 5. Managed Vehicles (Lanes) scoping
        $lanesQuery = Lane::whereIn('creator_id', $scopedUserIds);

        // 6. Statistics Mapping
        $this->stats = [
            'carriers_count' => (clone $carriersBaseQuery)->whereIn('id', $scopedUserIds)->count(),
            'scoped_incomplete_count' => $scopedIncompleteQuery->count(),
            'system_no_location_count' => $systemNoLocationQuery->count(),
            'vehicles_count' => $lanesQuery->count(),
            'territories_count' => $territoryCollection->count(),
        ];

        // 7. Prioritized Incomplete List (Latest 5)
        // Primary: Scoped Incomplete. Secondary: System No Location.
        $scopedIncompleteList = $scopedIncompleteQuery->latest()->take(5)->get();
        $remainingCount = 5 - $scopedIncompleteList->count();
        
        $systemNoLocationList = collect();
        if ($remainingCount > 0) {
            $systemNoLocationList = $systemNoLocationQuery
                ->whereNotIn('id', $scopedIncompleteList->pluck('id'))
                ->latest()
                ->take($remainingCount)
                ->get();
        }

        $this->incompleteRegistrations = $scopedIncompleteList->concat($systemNoLocationList)
            ->map(function($c) use ($scopedUserIds) {
                $missing = [];
                if ($c->buslocation()->count() < 1) $missing[] = 'buslocation';
                if ($c->fleets()->count() < 1) $missing[] = 'fleets';
                if ($c->directors()->count() < 2) $missing[] = 'directors (min 2)';
                if ($c->traderefs()->count() < 2) $missing[] = 'trade refs (min 2)';

                return [
                    'name' => $c->organisation ?? $c->name ?? 'Unnamed Carrier',
                    'slug' => $c->slug,
                    'email' => $c->email,
                    'missing' => $missing,
                    'in_scope' => $scopedUserIds->contains($c->id),
                    'created_at' => $c->created_at->diffForHumans(),
                ];
            })->toArray();

        // 8. Recent Scoped Carriers
        $this->recentCarriers = (clone $carriersBaseQuery)
            ->whereIn('id', $scopedUserIds)
            ->latest()->take(5)->get()->map(fn($c) => [
                'organisation' => $c->organisation ?? $c->name ?? 'Unnamed',
                'email' => $c->email,
                'slug' => $c->slug,
                'status' => $c->status ?? 'active',
                'created_at' => $c->created_at->toIso8601String(),
            ])->toArray();

        // 9. Recent Vehicles (Lanes)
        $this->recentLanes = $lanesQuery->latest()->take(3)->get()->toArray();
    }

    private function getGeographicalBounds($territories): array
    {
        $collection = collect($territories);
        $countries = $collection->flatMap(fn($t) => $t->countries ?? collect())->pluck('name')->unique()
            ->reject(fn($n) => strtolower($n) === 'zimbabwe')->values()->toArray();
        $cities = $collection->flatMap(fn($t) => ($t->zimbabweCities ?? collect())->concat(collect($t->provinces ?? [])->flatMap(fn($p) => $p->zimbabweCities ?? collect())))
            ->pluck('name')->unique()->values()->toArray();
        return ['countries' => $countries, 'cities' => $cities];
    }

    public function getStatusColor($status): string
    {
        return match($status) {
            'active', 'published' => 'green',
            'pending', 'incomplete' => 'amber',
            'inactive' => 'red',
            default => 'gray'
        };
    }
}; ?>

<div class="min-h-screen bg-gray-50 pb-12 font-sans">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 sticky top-0 z-30 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <x-graphic name="truck" class="w-7 h-7 text-emerald-600" />
                    Procurement Associate Dashboard
                </h1>
                <p class="text-sm text-gray-500 font-medium">Regional carrier onboarding & asset management</p>
            </div>
            <div class="flex gap-3">
                <flux:button href="{{ route('users.create') }}" variant="primary" color="emerald" icon="user-plus" wire:navigate>Register Carrier</flux:button>
                <flux:button href="{{ route('lanes.create') }}" variant="primary" color="sky" icon="plus" wire:navigate>Post Truck</flux:button>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 space-y-8">
        <!-- Main Statistics Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Scoped Carriers Count -->
            <flux:link href="{{ route('users.index', ['role' => 'carrier']) }}" wire:navigate class="block group">
                <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm hover:border-emerald-300 transition-all duration-200">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-emerald-50 rounded-xl text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-colors"><x-graphic name="users" class="w-6 h-6" /></div>
                    </div>
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">My Carriers</p>
                    <p class="text-3xl font-black text-gray-900">{{ $stats['carriers_count'] }}</p>
                </div>
            </flux:link>

            <!-- Scoped Incomplete Card -->
            <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-amber-50 rounded-xl text-amber-600"><x-graphic name="exclamation-circle" class="w-6 h-6" /></div>
                </div>
                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">My Partial Registrations</p>
                <p class="text-3xl font-black text-gray-900">{{ $stats['scoped_incomplete_count'] }}</p>
            </div>

            <!-- System Missing Location Card -->
            <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-orange-50 rounded-xl text-orange-600"><x-graphic name="location-marker" class="w-6 h-6" /></div>
                </div>
                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Unmapped Carriers</p>
                <p class="text-3xl font-black text-gray-900">{{ $stats['system_no_location_count'] }}</p>
            </div>

            <!-- Assigned Regions Card (Requested) -->
            <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm flex flex-col">
                <div class="flex items-center justify-between mb-2">
                    <div class="p-3 bg-purple-50 rounded-xl text-purple-600"><x-graphic name="globe" class="w-6 h-6" /></div>
                </div>
                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Assigned Regions</p>
                <p class="text-3xl font-black text-gray-900">{{ $stats['territories_count'] }}</p>
                <div class="mt-3 space-y-1 max-h-12 overflow-y-auto custom-scrollbar">
                    @forelse($assignedTerritories as $territory)
                        <span class="text-[9px] font-bold text-gray-600 bg-gray-100 px-1.5 py-0.5 rounded mr-1 inline-block">{{ $territory['name'] }}</span>
                    @empty
                        <span class="text-[9px] text-gray-400 font-medium">No jurisdictions assigned</span>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Activity & Priority Lists -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Scoped Recent Carriers -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden flex flex-col">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/30">
                    <h3 class="font-bold text-gray-900 flex items-center gap-2 text-sm uppercase tracking-wider">
                        <x-graphic name="user-group" class="w-5 h-5 text-gray-400" />
                        Regional Carrier Feed
                    </h3>
                    <flux:link href="{{ route('users.index', ['role' => 'carrier']) }}" variant="subtle" size="sm" wire:navigate>View All</flux:link>
                </div>
                <div class="p-6 space-y-3 flex-1">
                    @forelse($recentCarriers as $carrier)
                        <flux:link href="{{ route('users.show', ['user' => $carrier['slug']]) }}" wire:navigate class="block">
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100 hover:bg-white hover:border-emerald-100 transition-all group/item">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center text-emerald-600 group-hover/item:scale-110 transition-transform"><x-graphic name="office-building" class="w-5 h-5" /></div>
                                    <div class="min-w-0">
                                        <h4 class="font-bold text-gray-900 text-sm truncate">{{ $carrier['organisation'] }}</h4>
                                        <p class="text-xs text-gray-500 truncate">{{ $carrier['email'] }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="px-2 py-0.5 bg-{{ $this->getStatusColor($carrier['status']) }}-50 text-{{ $this->getStatusColor($carrier['status']) }}-700 text-[10px] font-bold rounded-full uppercase tracking-widest border border-{{ $this->getStatusColor($carrier['status']) }}-100">{{ $carrier['status'] }}</span>
                                </div>
                            </div>
                        </flux:link>
                    @empty
                        <div class="text-center py-16 text-gray-400 italic font-medium">No regional carriers found.</div>
                    @endforelse
                </div>
            </div>

            <!-- Onboarding Required Card (Incomplete Registrations) -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden flex flex-col">
                <div class="p-6 border-b border-gray-100 bg-gray-50/30 flex justify-between items-center">
                    <h3 class="font-bold text-gray-900 flex items-center gap-2 text-sm uppercase tracking-wider">
                        <x-graphic name="clipboard-check" class="w-5 h-5 text-amber-500" />
                        Onboarding Required
                    </h3>
                </div>
                <div class="p-6 space-y-4 flex-1">
                    @forelse($incompleteRegistrations as $item)
                        <flux:link href="{{ route('users.show', ['user' => $item['slug']]) }}" wire:navigate class="block my-3">
                            <div class="my-3 p-4 border {{ $item['in_scope'] ? 'border-amber-100 bg-amber-50 shadow-sm' : 'border-slate-100 bg-slate-50' }} rounded-xl transition-all hover:shadow-md">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-bold text-gray-900 text-sm truncate">{{ $item['name'] }}</h4>
                                    @if($item['in_scope']) 
                                        <span class="text-[8px] font-black bg-amber-200 text-amber-800 px-1.5 py-0.5 rounded uppercase tracking-tighter">In Region</span> 
                                    @else
                                        <span class="text-[8px] font-black bg-slate-200 text-slate-600 px-1.5 py-0.5 rounded uppercase tracking-tighter">Global</span>
                                    @endif
                                </div>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($item['missing'] as $m)
                                        <span class="text-[9px] font-black uppercase px-2 py-0.5 bg-white border border-gray-200 rounded-full text-gray-600">Missing: {{ $m }}</span>
                                    @endforeach
                                </div>
                                <div class="flex justify-between items-center mt-3">
                                    <p class="text-[10px] text-gray-400 font-medium italic">Applied {{ $item['created_at'] }}</p>
                                    <x-graphic name="chevron-right" class="w-3 h-3 text-gray-300" />
                                </div>
                            </div>
                        </flux:link>
                    @empty
                        <div class="text-center py-16 text-gray-400 italic font-medium">All regional and critical registrations are complete.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Managed Vehicles (Lanes) Summary -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden flex flex-col">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/30">
                <h3 class="font-bold text-gray-900 flex items-center gap-2 text-sm uppercase tracking-wider">
                    <x-graphic name="van" class="w-5 h-5 text-sky-500" />
                    Latest Regional Vehicle Postings
                </h3>
                <flux:link href="{{ route('lanes.index') }}" variant="subtle" size="sm" wire:navigate>View All</flux:link>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                @forelse($recentLanes as $lane)
                    <div class="p-5 bg-gray-50 rounded-xl border border-gray-100 shadow-sm group hover:border-sky-300 transition-colors">
                        <div class="flex justify-between items-start mb-3">
                            <span class="text-[10px] font-black uppercase px-2 py-0.5 bg-sky-100 text-sky-700 rounded-full">{{ $lane['vehicle_type'] ?? 'Truck' }}</span>
                            <span class="text-[10px] text-gray-400 font-bold tracking-tighter">{{ Illuminate\Support\Carbon::parse($lane['created_at'])->format('M d, Y') }}</span>
                        </div>
                        <h4 class="font-bold text-gray-900 text-sm truncate leading-tight">{{ $lane['cityfrom'] }} → {{ $lane['cityto'] }}</h4>
                    </div>
                @empty
                    <div class="col-span-full text-center py-12 text-gray-400 italic font-medium">No vehicle postings in your jurisdiction.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
