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

        // 4. Carriers Stats (Scoped by IDs AND Role)
        $carriersQuery = User::whereIn('id', $scopedUserIds)
            ->whereHas('roles', fn($q) => $q->where('name', 'carrier'));

        // 5. Lanes (Shipments) Stats (Scoped by creator ownership of the lane)
        $lanesQuery = Lane::whereIn('creator_id', $scopedUserIds);

        $this->stats = [
            'carriers_count' => $carriersQuery->count(),
            'carriers_this_month' => (clone $carriersQuery)->whereMonth('created_at', now()->month)->count(),
            'lanes_count' => $lanesQuery->count(),
            'lanes_active' => (clone $lanesQuery)->where('status', 'published')->count(),
            'territories_count' => $territoryCollection->count(),
        ];

        // 6. Recent Carriers
        $this->recentCarriers = $carriersQuery->latest()->take(5)->get()->map(fn($s) => [
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

<div class="min-h-screen bg-gray-50 pb-12 font-sans">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 sticky top-0 z-30 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <x-graphic name="chart-bar" class="w-7 h-7 text-indigo-600" />
                    Procurement Associate Dashboard
                </h1>
                <p class="text-sm text-gray-500 font-medium tracking-tight">Managing regionally scoped carriers, vehicle availability & logistics operations</p>
            </div>
            <div class="flex items-center gap-3">
                <flux:button href="{{ route('users.create') }}" variant="primary" color="emerald" icon="user-plus" wire:navigate>
                    Register Carrier
                </flux:button>
                <flux:button href="{{ route('lanes.create') }}" variant="primary" color="sky" icon="plus" wire:navigate>
                    Post Truck
                </flux:button>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 space-y-8">
        <!-- Main Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Scoped Carriers -->
            <flux:link href="{{ route('users.index')}}" wire:navigate class="block group">
                <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm hover:border-indigo-300 transition-all duration-200">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-indigo-50 rounded-xl text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                            <x-graphic name="users" class="w-6 h-6" />
                        </div>
                        <div class="text-right">
                            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest">Growth</span>
                            <span class="text-xs font-bold text-green-600 bg-green-50 px-2 py-0.5 rounded-full">+{{ $stats['carriers_this_month'] }}</span>
                        </div>
                    </div>
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Visible Carriers</p>
                    <p class="text-3xl font-black text-gray-900">{{ $stats['carriers_count'] }}</p>
                </div>
            </flux:link>

            <!-- Scoped Lanes -->
            <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-emerald-50 rounded-xl text-emerald-600">
                        <x-graphic name="van" class="w-6 h-6" />
                    </div>
                    <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full">{{ $stats['lanes_active'] }} Published</span>
                </div>
                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">My Managed Shipments</p>
                <p class="text-3xl font-black text-gray-900">{{ $stats['lanes_count'] }}</p>
            </div>

            <!-- Performance Card -->
            <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-amber-50 rounded-xl text-amber-600">
                        <x-graphic name="star" class="w-6 h-6" />
                    </div>
                </div>
                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Region Avg Rating</p>
                <div class="flex items-baseline gap-2">
                    <p class="text-3xl font-black text-gray-900">4.2</p>
                    <span class="text-sm text-gray-400 font-medium">/ 5.0</span>
                </div>
            </div>

            <!-- Territory Summary -->
            <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm flex flex-col">
                <div class="flex items-center justify-between mb-2">
                    <div class="p-3 bg-purple-50 rounded-xl text-purple-600">
                        <x-graphic name="location-marker" class="w-6 h-6" />
                    </div>
                </div>
                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Active Jurisdictions</p>
                <p class="text-3xl font-black text-gray-900">{{ $stats['territories_count'] }}</p>
                <div class="mt-3 space-y-1.5 max-h-16 overflow-y-auto custom-scrollbar">
                    @forelse($assignedTerritories as $territory)
                        <div class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 bg-purple-400 rounded-full flex-shrink-0"></span>
                            <span class="text-[10px] font-bold text-gray-600 truncate">{{ $territory['name'] }}</span>
                        </div>
                    @empty
                        <span class="text-[10px] text-gray-400 italic font-medium">No jurisdictions assigned</span>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Dashboard Actions -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <flux:link href="{{ route('users.create') }}" wire:navigate class="p-4 bg-white border border-gray-200 rounded-xl flex items-center gap-4 hover:shadow-md transition-all group">
                <div class="p-3 bg-blue-50 rounded-lg group-hover:bg-blue-100 transition-colors">
                    <x-graphic name="user-add" class="w-6 h-6 text-blue-600" />
                </div>
                <div>
                    <p class="font-bold text-gray-900 text-sm">Register Carrier</p>
                    <p class="text-xs text-gray-500 font-medium">Expand client network</p>
                </div>
            </flux:link>

            <flux:link href="{{ route('lanes.create') }}" wire:navigate class="p-4 bg-white border border-gray-200 rounded-xl flex items-center gap-4 hover:shadow-md transition-all group">
                <div class="p-3 bg-green-50 rounded-lg group-hover:bg-green-100 transition-colors">
                    <x-graphic name="plus-circle" class="w-6 h-6 text-green-600" />
                </div>
                <div>
                    <p class="font-bold text-gray-900 text-sm">New Lane (vehicle)</p>
                    <p class="text-xs text-gray-500 font-medium">Post to regional board</p>
                </div>
            </flux:link>

            <div class="p-4 bg-white border border-gray-200 rounded-xl flex items-center gap-4 hover:shadow-md transition-all group cursor-pointer">
                <div class="p-3 bg-purple-50 rounded-lg group-hover:bg-purple-100 transition-colors">
                    <x-graphic name="currency-dollar" class="w-6 h-6 text-purple-600" />
                </div>
                <div>
                    <p class="font-bold text-gray-900 text-sm">Accounts</p>
                    <p class="text-xs text-gray-500 font-medium">Pending billing</p>
                </div>
            </div>

            <div class="p-4 bg-white border border-gray-200 rounded-xl flex items-center gap-4 hover:shadow-md transition-all group cursor-pointer">
                <div class="p-3 bg-amber-50 rounded-lg group-hover:bg-amber-100 transition-colors">
                    <x-graphic name="document-text" class="w-6 h-6 text-amber-600" />
                </div>
                <div>
                    <p class="font-bold text-gray-900 text-sm">Audit Trail</p>
                    <p class="text-xs text-gray-500 font-medium">Recent compliance checks</p>
                </div>
            </div>
        </div>

        <!-- Detailed Lists -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Carriers -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden flex flex-col">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/30">
                    <h3 class="font-bold text-gray-900 flex items-center gap-2 text-sm uppercase tracking-wider">
                        <x-graphic name="user-group" class="w-5 h-5 text-gray-400" />
                        My Regional Carriers
                    </h3>
                    <flux:link href="{{ route('users.index') }}" variant="subtle" size="sm" wire:navigate>View All</flux:link>
                </div>
                <div class="p-6 space-y-3 flex-1">
                    @forelse($recentCarriers as $carrier)
                        <flux:link href="{{ route('users.show', ['user' => $carrier['slug']]) }}" wire:navigate class="block">
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100 hover:bg-white hover:border-indigo-100 transition-all duration-200 group/item">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 group-hover/item:scale-110 transition-transform">
                                        <x-graphic name="office-building" class="w-5 h-5" />
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="font-bold text-gray-900 text-sm truncate">{{ $carrier['organisation'] }}</h4>
                                        <p class="text-xs text-gray-500 truncate">{{ $carrier['email'] }}</p>
                                    </div>
                                </div>
                                <div class="text-right flex flex-col items-end gap-1 flex-shrink-0">
                                    <span class="px-2 py-0.5 bg-{{ $this->getStatusColor($carrier['status']) }}-50 text-{{ $this->getStatusColor($carrier['status']) }}-700 text-[10px] font-bold rounded-full uppercase tracking-widest border border-{{ $this->getStatusColor($carrier['status']) }}-100">
                                        {{ $carrier['status'] }}
                                    </span>
                                    <p class="text-[10px] text-gray-400 font-medium">{{ Illuminate\Support\Carbon::parse($carrier['created_at'])->diffForHumans() }}</p>
                                </div>
                            </div>
                        </flux:link>
                    @empty
                        <div class="text-center py-16">
                            <x-graphic name="users" class="w-16 h-16 text-gray-100 mx-auto mb-4" />
                            <p class="text-sm font-semibold text-gray-400 italic">No carriers currently in your scope.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Shipments -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden flex flex-col">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/30">
                    <h3 class="font-bold text-gray-900 flex items-center gap-2 text-sm uppercase tracking-wider">
                        <x-graphic name="cube" class="w-5 h-5 text-gray-400" />
                        My Latest Vehicles
                    </h3>
                    <flux:link href="{{ route('lanes.index') }}" variant="subtle" size="sm" wire:navigate>View All</flux:link>
                </div>
                <div class="p-6 space-y-3 flex-1">
                    @forelse($recentLanes as $lane)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100 hover:bg-white transition-all shadow-sm">
                            <div class="flex items-center gap-4 min-w-0">
                                <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center text-emerald-600">
                                    <x-graphic name="van" class="w-5 h-5" />
                                </div>
                                <div class="min-w-0">
                                    <h4 class="font-bold text-gray-900 text-sm truncate">{{ $lane['cityfrom'] }} → {{ $lane['cityto'] }}</h4>
                                    <p class="text-xs text-gray-500 font-medium truncate">{{ $lane['vehicle_type'] ?? 'Standard Truck' }}</p>
                                </div>
                            </div>
                            <div class="text-right flex flex-col items-end gap-1 flex-shrink-0">
                                <span class="px-2 py-0.5 bg-{{ $this->getStatusColor($lane['status'] ?? 'published') }}-50 text-{{ $this->getStatusColor($lane['status'] ?? 'published') }}-700 text-[10px] font-bold rounded-full uppercase tracking-widest">
                                    {{ $lane['status'] ?? 'published' }}
                                </span>
                                <p class="text-[10px] text-gray-400 font-medium">{{ Illuminate\Support\Carbon::parse($lane['created_at'])->format('M d, Y') }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-16">
                            <x-graphic name="van" class="w-16 h-16 text-gray-100 mx-auto mb-4" />
                            <p class="text-sm font-semibold text-gray-400 italic">No shipments posted in your territory.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Support & Compliance -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-8">
            <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                <x-graphic name="shield-check" class="w-5 h-5 text-indigo-500" />
                Compliance & Resources
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="border-2 border-dashed border-gray-100 rounded-2xl p-8 text-center group hover:border-indigo-300 hover:bg-indigo-50/10 transition-all duration-300">
                    <x-graphic name="document-download" class="w-12 h-12 text-gray-300 mx-auto mb-4 group-hover:text-indigo-400 transition-colors" />
                    <h4 class="font-bold text-gray-900 mb-2">Regional Legal Assets</h4>
                    <p class="text-sm text-gray-500 mb-6 font-medium">Download standard contracts tailored for your assigned areas.</p>
                    <div class="flex flex-wrap justify-center gap-2">
                        <flux:button size="sm" variant="subtle" color="indigo">Carrier Contract Form</flux:button>
                        <flux:button size="sm" variant="subtle" color="indigo">Load Confirmation Form</flux:button>
                    </div>
                </div>
                <div class="border-2 border-dashed border-gray-100 rounded-2xl p-8 text-center group hover:border-emerald-300 hover:bg-emerald-50/10 transition-all duration-300">
                    <x-graphic name="phone" class="w-12 h-12 text-gray-300 mx-auto mb-4 group-hover:text-emerald-400 transition-colors" />
                    <h4 class="font-bold text-gray-900 mb-2">Ops Direct Support</h4>
                    <p class="text-sm text-gray-500 mb-6 font-medium">Verify business locations or territory overlaps with Logistics HQ.</p>
                    <flux:button size="sm" icon="chat-bubble-left-right" color="emerald">Contact Logistics Lead</flux:button>
                </div>
            </div>
        </div>
    </div>
</div>
