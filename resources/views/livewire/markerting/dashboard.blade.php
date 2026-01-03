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
    public array $recentShippers = [];
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

        // 2. Extract boundaries defensively
        $bounds = $this->getGeographicalBounds($territoryCollection);

        // 3. Define visibility boundary: (Created by me) OR (In my Territories)
        // Grouping the OR condition inside a where() closure is critical for correct logic
        $scopedUserIds = User::query()
            ->where(function (Builder $mainQuery) use ($user, $bounds) {
                // Condition A: Ownership (Created by current user)
                $mainQuery->whereHas('createdBy', function ($q) use ($user) {
                    $q->where('user_creations.creator_user_id', $user->id);
                });

                // Condition B: Geography (Business Location matches territories)
                $mainQuery->orWhereHas('buslocation', function ($q) use ($bounds) {
                    $q->where(function ($sub) use ($bounds) {
                        $hasCountry = !empty($bounds['countries']);
                        $hasCity = !empty($bounds['cities']);

                        if ($hasCountry) {
                            $sub->whereIn('country', $bounds['countries']);
                        }

                        if ($hasCity) {
                            $method = $hasCountry ? 'orWhereIn' : 'whereIn';
                            $sub->$method('city', $bounds['cities']);
                        }

                        // If no bounds exist, we force a mismatch to ensure only ownership works
                        if (!$hasCountry && !$hasCity) {
                            $sub->whereRaw('1 = 0');
                        }
                    });
                });
            })
            ->pluck('id');

        // 4. Shippers Stats (Scoped by IDs AND Role)
        $shippersQuery = User::whereIn('id', $scopedUserIds)
            ->whereHas('roles', fn($q) => $q->where('name', 'shipper'));

        // 5. Lanes (Shipments) Stats (Scoped by creator ownership of the lane)
        $lanesQuery = Lane::whereIn('creator_id', $scopedUserIds);

        $this->stats = [
            'shippers_count' => $shippersQuery->count(),
            'shippers_this_month' => (clone $shippersQuery)->whereMonth('created_at', now()->month)->count(),
            'lanes_count' => $lanesQuery->count(),
            'lanes_active' => (clone $lanesQuery)->where('status', 'published')->count(),
            'territories_count' => $territoryCollection->count(),
        ];

        // 6. Recent Shippers
        $this->recentShippers = $shippersQuery->latest()->take(3)->get()->map(fn($s) => [
            'organisation' => $s->organisation ?? $s->contact_person ?? 'Unnamed Entity',
            'email' => $s->email,
            'slug' => $s->slug,
            'status' => $s->status ?? 'active',
            'created_at' => $s->created_at->toIso8601String(),
        ])->toArray();

        // 7. Recent Lanes (Shipments)
        $this->recentLanes = $lanesQuery->latest()->take(3)->get()->toArray();
    }

    private function getGeographicalBounds($territories): array
    {
        $collection = collect($territories);

        // Extract non-Zimbabwe countries
        $countries = $collection->flatMap(function ($t) {
            return $t->countries ?? collect();
        })->pluck('name')
          ->unique()
          ->reject(fn($name) => strtolower($name) === 'zimbabwe')
          ->values()
          ->toArray();

        // Extract cities from direct territory link AND province-based links
        $cities = $collection->flatMap(function ($t) {
            $direct = $t->zimbabweCities ?? collect();
            $provinces = $t->provinces ?? collect();
            $fromProvinces = $provinces->flatMap(fn($p) => $p->zimbabweCities ?? collect());
            
            return $direct->concat($fromProvinces);
        })->pluck('name')
          ->unique()
          ->values()
          ->toArray();

        return [
            'countries' => $countries,
            'cities' => $cities,
        ];
    }

    public function getStatusColor($status): string
    {
        return match($status) {
            'active', 'published' => 'green',
            'pending' => 'amber',
            'inactive' => 'red',
            default => 'gray'
        };
    }
}; ?>


<div class="min-h-screen bg-gray-50 pb-12">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <x-graphic name="chart-bar" class="w-7 h-7 text-indigo-600" />
                    Marketing Associate Dashboard
                </h1>
                <p class="text-sm text-gray-500">Managing regional shippers and logistics pipelines</p>
            </div>
            <div class="flex items-center gap-3">
                <flux:button href="{{ route('users.create') }}" variant="primary" color="emerald" icon="user-plus" wire:navigate>
                    Register Shipper
                </flux:button>
                <flux:button href="{{ route('lanes.create') }}" variant="primary" color="sky" icon="plus" wire:navigate>
                    New Shipment
                </flux:button>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 space-y-8">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- My Shippers -->
            <flux:link href="{{ route('users.index') }}" wire:navigate class="block">
                <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm hover:border-indigo-300 transition-all group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-indigo-50 rounded-xl text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                            <x-graphic name="users" class="w-6 h-6" />
                        </div>
                        <span class="text-xs font-bold text-green-600 bg-green-50 px-2 py-1 rounded-full">+{{ $stats['shippers_this_month'] }} new</span>
                    </div>
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">My Shippers</p>
                    <p class="text-3xl font-black text-gray-900">{{ $stats['shippers_count'] }}</p>
                </div>
            </flux:link>

            <!-- My Shipments (Lanes) -->
            <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-emerald-50 rounded-xl text-emerald-600">
                        <x-graphic name="van" class="w-6 h-6" />
                    </div>
                    <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full">{{ $stats['lanes_active'] }} Active</span>
                </div>
                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">My Shipments</p>
                <p class="text-3xl font-black text-gray-900">{{ $stats['lanes_count'] }}</p>
            </div>

            <!-- Pending Feedback (Mock) -->
            <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-amber-50 rounded-xl text-amber-600">
                        <x-graphic name="annotation" class="w-6 h-6" />
                    </div>
                </div>
                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Avg. Rating</p>
                <div class="flex items-baseline gap-2">
                    <p class="text-3xl font-black text-gray-900">4.2</p>
                    <span class="text-sm text-gray-400">/ 5.0</span>
                </div>
            </div>

            <!-- Territories -->
            <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm overflow-hidden flex flex-col">
                <div class="flex items-center justify-between mb-2">
                    <div class="p-3 bg-purple-50 rounded-xl text-purple-600">
                        <x-graphic name="location-marker" class="w-6 h-6" />
                    </div>
                </div>
                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Assigned Areas</p>
                <p class="text-3xl font-black text-gray-900">{{ $stats['territories_count'] }}</p>
                <div class="mt-3 space-y-1 max-h-16 overflow-y-auto custom-scrollbar">
                    @foreach($assignedTerritories as $territory)
                        <div class="flex items-center gap-1.5">
                            <span class="w-1 h-1 bg-purple-400 rounded-full"></span>
                            <span class="text-[10px] font-bold text-gray-500 truncate">{{ $territory['name'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <flux:link href="{{ route('users.create') }}" wire:navigate class="p-4 bg-white border border-gray-200 rounded-xl flex items-center gap-4 hover:shadow-md transition-shadow group">
                <div class="p-3 bg-blue-50 rounded-lg group-hover:bg-blue-100 transition-colors">
                    <x-graphic name="user-add" class="w-6 h-6 text-blue-600" />
                </div>
                <div>
                    <p class="font-bold text-gray-900 text-sm">Register Shipper</p>
                    <p class="text-xs text-gray-500">Add new client</p>
                </div>
            </flux:link>

            <flux:link href="{{ route('lanes.create') }}" wire:navigate class="p-4 bg-white border border-gray-200 rounded-xl flex items-center gap-4 hover:shadow-md transition-shadow group">
                <div class="p-3 bg-green-50 rounded-lg group-hover:bg-green-100 transition-colors">
                    <x-graphic name="plus-circle" class="w-6 h-6 text-green-600" />
                </div>
                <div>
                    <p class="font-bold text-gray-900 text-sm">Create Shipment</p>
                    <p class="text-xs text-gray-500">New logistics order</p>
                </div>
            </flux:link>

            <div class="p-4 bg-white border border-gray-200 rounded-xl flex items-center gap-4 hover:shadow-md transition-shadow group cursor-pointer">
                <div class="p-3 bg-purple-50 rounded-lg group-hover:bg-purple-100 transition-colors">
                    <x-graphic name="chart-bar" class="w-6 h-6 text-purple-600" />
                </div>
                <div>
                    <p class="font-bold text-gray-900 text-sm">Payment Tracking</p>
                    <p class="text-xs text-gray-500">Monitor collections</p>
                </div>
            </div>

            <div class="p-4 bg-white border border-gray-200 rounded-xl flex items-center gap-4 hover:shadow-md transition-shadow group cursor-pointer">
                <div class="p-3 bg-amber-50 rounded-lg group-hover:bg-amber-100 transition-colors">
                    <x-graphic name="document-download" class="w-6 h-6 text-amber-600" />
                </div>
                <div>
                    <p class="font-bold text-gray-900 text-sm">Download Assets</p>
                    <p class="text-xs text-gray-500">Forms & Templates</p>
                </div>
            </div>
        </div>

        <!-- Lists Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Shippers -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden flex flex-col">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-bold text-gray-900 flex items-center gap-2">
                        <x-graphic name="user-group" class="w-5 h-5 text-gray-400" />
                        My Recent Shippers
                    </h3>
                    <flux:link href="{{ route('users.index', ['search' => 'shippers']) }}" variant="subtle" size="sm" wire:navigate>View All</flux:link>
                </div>
                <div class="p-6 space-y-4 flex-1">
                    @forelse($recentShippers as $shipper)
                    <flux:link href="{{ route('users.show', ['user' => $shipper['slug']]) }}" wire:navigate class="block">
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100 hover:bg-white transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600">
                                    <x-graphic name="office-building" class="w-5 h-5" />
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 text-sm">{{ $shipper['organisation'] ?? 'Unknown Org' }}</h4>
                                    <p class="text-xs text-gray-500">{{ $shipper['email'] }}</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="px-2 py-0.5 bg-{{ $this->getStatusColor($shipper['status'] ?? 'active') }}-50 text-{{ $this->getStatusColor($shipper['status'] ?? 'active') }}-700 text-[10px] font-bold rounded-full uppercase tracking-tighter">
                                    {{ $shipper['status'] ?? 'active' }}
                                </span>
                                <p class="text-[10px] text-gray-400">{{ Illuminate\Support\Carbon::parse($shipper['created_at'])->diffForHumans() }}</p>
                            </div>
                        </div>
                    </flux:link>
                    @empty
                        <div class="text-center py-12">
                            <x-graphic name="users" class="w-12 h-12 text-gray-200 mx-auto mb-2" />
                            <p class="text-sm text-gray-400">No shippers found in your scope.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Shipments (Lanes) -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden flex flex-col">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-bold text-gray-900 flex items-center gap-2">
                        <x-graphic name="cube" class="w-5 h-5 text-gray-400" />
                        My Recent Shipments
                    </h3>
                    <flux:link href="{{ route('lanes.index') }}" variant="subtle" size="sm" wire:navigate>View All</flux:link>
                </div>
                <div class="p-6 space-y-4 flex-1">
                    @forelse($recentLanes as $lane)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100 hover:bg-white transition-colors">
                            <div class="flex items-center gap-4 text-sm">
                                <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center text-emerald-600">
                                    <x-graphic name="location-marker" class="w-5 h-5" />
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900">{{ $lane['cityfrom'] }} → {{ $lane['cityto'] }}</h4>
                                    <p class="text-xs text-gray-500">{{ $lane['vehicle_type'] ?? 'General Cargo' }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="px-2 py-0.5 bg-{{ $this->getStatusColor($lane['status'] ?? 'active') }}-50 text-{{ $this->getStatusColor($lane['status'] ?? 'active') }}-700 text-[10px] font-bold rounded-full uppercase tracking-tighter">
                                    {{ $lane['status'] ?? 'published' }}
                                </span>
                                <p class="text-[10px] text-gray-400 mt-1">{{ Illuminate\Support\Carbon::parse($lane['created_at'])->format('M d, Y') }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <x-graphic name="van" class="w-12 h-12 text-gray-200 mx-auto mb-2" />
                            <p class="text-sm text-gray-400">No recent shipments recorded.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Documents & Tools -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-8">
            <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                <x-graphic name="clipboard-list" class="w-5 h-5 text-indigo-500" />
                Asset Management
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="border-2 border-dashed border-gray-200 rounded-2xl p-8 text-center group hover:border-indigo-400 transition-colors">
                    <x-graphic name="document-download" class="w-12 h-12 text-gray-300 mx-auto mb-4 group-hover:text-indigo-400 transition-colors" />
                    <h4 class="font-bold text-gray-900 mb-2">Internal Guidelines</h4>
                    <p class="text-sm text-gray-500 mb-6">Access policy documents and shipping rate calculators.</p>
                    <div class="flex flex-wrap justify-center gap-2">
                        <flux:button size="sm" variant="subtle">Contracts</flux:button>
                        <flux:button size="sm" variant="subtle">Agreements</flux:button>
                        <flux:button size="sm" variant="subtle">SOPs</flux:button>
                    </div>
                </div>
                <div class="border-2 border-dashed border-gray-200 rounded-2xl p-8 text-center group hover:border-blue-400 transition-colors">
                    <x-graphic name="phone" class="w-12 h-12 text-gray-300 mx-auto mb-4 group-hover:text-blue-400 transition-colors" />
                    <h4 class="font-bold text-gray-900 mb-2">Regional Support</h4>
                    <p class="text-sm text-gray-500 mb-6">Need assistance with a regional shipper or compliance issue?</p>
                    <flux:button size="sm" icon="chat-bubble-left-right">Open Support Desk</flux:button>
                </div>
            </div>
        </div>
    </div>
</div>