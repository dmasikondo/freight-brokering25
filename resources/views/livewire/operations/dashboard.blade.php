<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Freight;
use App\Models\Lane;
use App\Models\Territory;
use App\Models\ZimbabweCity;
use App\Models\Country;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

new class extends Component {
    public array $stats = [];
    public array $recentShippers = [];
    public array $recentCarriers = [];
    public array $marketingAssociates = [];
    public array $procurementAssociates = [];
    public array $activeShipments = [];
    public array $assignedTerritories = [];

    public function mount()
    {
        $this->loadDashboard();
    }

    public function loadDashboard()
    {
        /** @var User $user */
        $user = Auth::user();
        
        // 1. Fetch user territories for relationship matching
        $territoryCollection = $user->territories()
            ->with(['countries', 'zimbabweCities', 'provinces.zimbabweCities'])
            ->get();
            
        $userTerritoryIds = $territoryCollection->pluck('id');
        $this->assignedTerritories = $territoryCollection->toArray();

        // 2. Extract geographical boundaries for Location-based scoping (for Shippers/Carriers)
        $bounds = $this->getGeographicalBounds($territoryCollection);

        // 3. Define Visibility Scopes
        
        // A. Geographical/Ownership Scope (For Shippers & Carriers)
        $scopedClientIds = User::query()
            ->where(function (Builder $mainQuery) use ($user, $bounds) {
                // Ownership (Created by me)
                $mainQuery->whereHas('createdBy', function ($q) use ($user) {
                    $q->where('user_creations.creator_user_id', $user->id);
                });

                // Geography (Location matches my territory)
                $mainQuery->orWhereHas('buslocation', function ($q) use ($bounds) {
                    $q->where(function ($sub) use ($bounds) {
                        $hasCountry = !empty($bounds['countries']);
                        $hasCity = !empty($bounds['cities']);
                        if ($hasCountry) $sub->whereIn('country', $bounds['countries']);
                        if ($hasCity) {
                            $method = $hasCountry ? 'orWhereIn' : 'whereIn';
                            $sub->$method('city', $bounds['cities']);
                        }
                        if (!$hasCountry && !$hasCity) $sub->whereRaw('1 = 0');
                    });
                });
            })
            ->pluck('id');

        // B. Associate Scope (Direct Territory Linkage)
        $scopedAssociateIds = User::query()
            ->whereHas('territories', fn($q) => $q->whereIn('territories.id', $userTerritoryIds))
            ->pluck('id');

        // 4. Incomplete Definition Logic (>=1 buslocation, >=1 fleet, >=2 directors, >=2 traderefs)
        $getIncompleteCount = function($u) {
            $missingCount = 0;
            if ($u->buslocation()->count() < 1) $missingCount++;
            if ($u->fleets()->count() < 1) $missingCount++;
            if ($u->directors()->count() < 2) $missingCount++;
            if ($u->traderefs()->count() < 2) $missingCount++;
            return $missingCount;
        };

        $applyIncompleteFilter = function($q) {
            $q->where(function($sub) {
                $sub->whereDoesntHave('buslocation')
                    ->orWhereDoesntHave('fleets')
                    ->orWhereHas('directors', null, '<', 2)
                    ->orWhereHas('traderefs', null, '<', 2);
            });
        };

        // 5. Build Scoped Queries
        $scopedCarriers = User::whereHas('roles', fn($q) => $q->where('name', 'carrier'))->whereIn('id', $scopedClientIds);
        $scopedShippers = User::whereHas('roles', fn($q) => $q->where('name', 'shipper'))->whereIn('id', $scopedClientIds);
        
        $marketingInScope = User::whereHas('roles', fn($q) => $q->where('name', 'marketing logistics associate'))->whereIn('id', $scopedAssociateIds);
        $procurementInScope = User::whereHas('roles', fn($q) => $q->where('name', 'procurement logistics associate'))->whereIn('id', $scopedAssociateIds);

        // 6. Statistics Aggregation
        $this->stats = [
            'carriers_count' => $scopedCarriers->count(),
            'shippers_count' => $scopedShippers->count(),
            'marketing_count' => $marketingInScope->count(),
            'procurement_count' => $procurementInScope->count(),
            'scoped_incomplete_carriers' => (clone $scopedCarriers)->tap($applyIncompleteFilter)->count(),
            'global_incomplete_carriers' => User::whereHas('roles', fn($q) => $q->where('name', 'carrier'))->tap($applyIncompleteFilter)->count(),
            'active_shipments' => Freight::whereIn('creator_id', $scopedClientIds)
                ->where('status', 'published')
                ->whereIn('shipment_status', ['loading', 'in transit'])
                ->count(),
            'territories_count' => $territoryCollection->count(),
        ];

        // 7. Data Feed Mapping
        $this->recentShippers = $scopedShippers->latest()->take(5)->get()->map(fn($u) => [
            'name' => $u->organisation ?? $u->name,
            'slug' => $u->slug,
            'email' => $u->email,
            'created_at' => $u->created_at->diffForHumans(),
        ])->toArray();

        $this->recentCarriers = $scopedCarriers->latest()->take(5)->get()->map(function($u) use ($getIncompleteCount) {
            $missing = $getIncompleteCount($u);
            return [
                'name' => $u->organisation ?? $u->name,
                'slug' => $u->slug,
                'email' => $u->email,
                'created_at' => $u->created_at->diffForHumans(),
                'is_complete' => $missing === 0,
                'missing_components' => $missing,
            ];
        })->toArray();

        $this->marketingAssociates = $marketingInScope->get()->map(fn($u) => [
            'name' => $u->contact_person ?? $u->name,
            'email' => $u->email,
            'slug' => $u->slug,
        ])->toArray();

        $this->procurementAssociates = $procurementInScope->get()->map(fn($u) => [
            'name' => $u->contact_person ?? $u->name,
            'email' => $u->email,
            'slug' => $u->slug,
        ])->toArray();
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
}; ?>

<div class="min-h-screen bg-slate-50 pb-12 font-sans">
    <!-- Main Header -->
    <div class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h1 class="text-2xl font-black text-slate-900 flex items-center gap-3">
                    <x-graphic name="presentation-chart-line" class="w-8 h-8 text-indigo-600" />
                    Operations Associate Dashboard
                </h1>
                <div class="flex flex-wrap items-center gap-2 mt-1">
                    <x-graphic name="map" class="w-4 h-4 text-emerald-500" />
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">
                        Assigned Territories: 
                        @forelse($assignedTerritories as $t)
                            <span class="text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100 mr-1">{{ $t['name'] }}</span>
                        @empty
                            <span class="text-slate-400">None</span>
                        @endforelse
                    </span>
                </div>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                <flux:button href="{{ route('users.create') }}" variant="primary" color="indigo" icon="user-plus" wire:navigate>Register Shipper</flux:button>
                <flux:button href="{{ route('users.create') }}" variant="primary" color="emerald" icon="truck" wire:navigate>Register Carrier</flux:button>
                <flux:button href="{{ route('freights.create') }}" variant="primary" color="sky" icon="document-plus" wire:navigate>Create Shipment</flux:button>
                <flux:button href="{{ route('lanes.create') }}" variant="primary" color="purple" icon="clipboard-document-list" wire:navigate>Post Truck</flux:button>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-10 space-y-8">
        <!-- Dashboard Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
                <div class="p-3 w-fit bg-indigo-50 rounded-2xl text-indigo-600 mb-4"><x-graphic name="office-building" class="w-6 h-6" /></div>
                <p class="text-sm font-bold text-slate-500 uppercase tracking-wider">Scoped Shippers</p>
                <p class="text-4xl font-black text-slate-900">{{ $stats['shippers_count'] }}</p>
            </div>

            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
                <div class="p-3 w-fit bg-emerald-50 rounded-2xl text-emerald-600 mb-4"><x-graphic name="van" class="w-6 h-6" /></div>
                <p class="text-sm font-bold text-slate-500 uppercase tracking-wider">Scoped Carriers</p>
                <p class="text-4xl font-black text-slate-900">{{ $stats['carriers_count'] }}</p>
            </div>

            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
                <div class="p-3 w-fit bg-amber-50 rounded-2xl text-amber-600 mb-4"><x-graphic name="exclamation" class="w-6 h-6" /></div>
                <p class="text-sm font-bold text-slate-500 uppercase tracking-wider">My Partial Carrier Registrations</p>
                <p class="text-4xl font-black text-slate-900">{{ $stats['scoped_incomplete_carriers'] }}</p>
            </div>

            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
                <div class="p-3 w-fit bg-red-50 rounded-2xl text-red-600 mb-4"><x-graphic name="location-marker" class="w-6 h-6" /></div>
                <p class="text-sm font-bold text-slate-500 uppercase tracking-wider">Unmapped Carriers (Global)</p>
                <p class="text-4xl font-black text-slate-900">{{ $stats['global_incomplete_carriers'] }}</p>
            </div>
        </div>

        <!-- Team & Activity Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Team Associates Scoped by Shared Territory -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Marketing Logistics Associates -->
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-4 flex items-center justify-between">
                        <span>Marketing Logistics Associates</span>
                        <span class="px-2 py-0.5 bg-purple-100 text-purple-700 rounded-full text-[10px]">{{ $stats['marketing_count'] }}</span>
                    </h3>
                    <div class="space-y-3">
                        @forelse($marketingAssociates as $ma)
                            <flux:link href="{{ route('users.show', ['user' => $ma['slug']]) }}" wire:navigate class="block p-3  transition-all group">
                                <p class="text-sm font-bold text-slate-700 group-hover:text-purple-700">{{ $ma['name'] }}</p>
                                <p class="text-[10px] text-slate-400 font-medium truncate">{{ $ma['email'] }}</p>
                            </flux:link>
                        @empty
                            <p class="text-xs text-slate-400 italic">No region-matched marketing associates.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Procurement Logistics Associates -->
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-4 flex items-center justify-between">
                        <span>Procurement Logistics Associates</span>
                        <span class="px-2 py-0.5 bg-orange-100 text-orange-700 rounded-full text-[10px]">{{ $stats['procurement_count'] }}</span>
                    </h3>
                    <div class="space-y-3">
                        @forelse($procurementAssociates as $pa)
                            <flux:link href="{{ route('users.show', ['user' => $pa['slug']]) }}" wire:navigate class="block p-3  transition-all group">
                                <p class="text-sm font-bold text-slate-700 group-hover:text-orange-700">{{ $pa['name'] }}</p>
                                <p class="text-[10px] text-slate-400 font-medium truncate">{{ $pa['email'] }}</p>
                            </flux:link>
                        @empty
                            <p class="text-xs text-slate-400 italic">No region-matched procurement logistics associates.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Recent Regional Intake -->
            <div class="lg:col-span-3 space-y-8">
                <!-- Recent Shippers -->
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden flex flex-col p-6">
                    <div class="p-6 bg-slate-50/50 border-b border-slate-100 flex justify-between items-center">
                        <h4 class="text-sm font-black text-slate-900 uppercase tracking-widest">Regional Shipper Intake (Recently Scoped)</h4>
                        <flux:link href="{{ route('users.index', ['role' => 'shipper']) }}" size="sm" variant="subtle" wire:navigate>Directory</flux:link>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse($recentShippers as $shipper)
                        <div class="hover:bg-slate-50 transition-all group">
                            <flux:link href="{{ route('users.show', ['user' => $shipper['slug']]) }}" wire:navigate class="flex items-center justify-between w-full px-8 py-5 ">
                                <div class="flex items-center gap-5">
                                    <div class="w-12 h-12 bg-indigo-100 rounded-2xl flex items-center justify-center text-indigo-600 group-hover:scale-110 transition-transform"><x-graphic name="building-office-2" class="w-6 h-6" /></div>
                                    <div class="min-w-0">
                                        <p class="text-base font-bold text-slate-900 truncate">{{ $shipper['name'] }}</p>
                                        <p class="text-xs text-slate-400 font-medium">{{ $shipper['email'] }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] font-black uppercase text-indigo-500 bg-indigo-50 px-2 py-1 rounded border border-indigo-100">{{ $shipper['created_at'] }}</span>
                                </div>
                            </flux:link>
                        </div>

                        @empty
                            <p class="p-16 text-center text-sm text-slate-400 italic font-medium">No regional shippers found in scope.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Recent Carriers -->
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden flex flex-col p-6">
                    <div class="p-6 bg-slate-50/50 border-b border-slate-100 flex justify-between items-center">
                        <h4 class="text-sm font-black text-slate-900 uppercase tracking-widest">Regional Carrier Intake (Recently Scoped)</h4>
                        <flux:link href="{{ route('users.index', ['role' => 'carrier']) }}" size="sm" variant="subtle" wire:navigate>Directory</flux:link>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse($recentCarriers as $carrier)
                            <flux:link href="{{ route('users.show', ['user' => $carrier['slug']]) }}" wire:navigate class="flex items-center justify-between w-full px-8 py-5 hover:bg-slate-50 transition-all group">
                                <div class="flex items-center gap-5">
                                    <div class="w-12 h-12 bg-emerald-100 rounded-2xl flex items-center justify-center text-emerald-600 group-hover:scale-110 transition-transform"><x-graphic name="office-building" class="w-6 h-6" /></div>
                                    <div class="min-w-0">
                                        <p class="text-base font-bold text-slate-900 truncate">{{ $carrier['name'] }}</p>
                                        <p class="text-xs text-slate-400 font-medium">{{ $carrier['email'] }}</p>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-1.5">
                                    @if($carrier['is_complete'])
                                        <span class="text-[9px] font-black uppercase text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200 flex items-center gap-1">
                                            <x-graphic name="check-circle" class="w-3 h-3" /> Fully Registered
                                        </span>
                                    @else
                                        <span class="text-[9px] font-black uppercase text-amber-600 bg-amber-50 px-2.5 py-1 rounded-full border border-amber-200 flex items-center gap-1">
                                            <x-graphic name="exclamation-circle" class="w-3 h-3" /> Missing {{ $carrier['missing_components'] }} components
                                        </span>
                                    @endif
                                    <span class="text-[10px] font-bold text-slate-400 italic">Joined {{ $carrier['created_at'] }}</span>
                                </div>
                            </flux:link>
                        @empty
                            <p class="p-16 text-center text-sm text-slate-400 italic font-medium">No regional carriers found in scope.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Compliance & Ops Manual -->
<div class="bg-amber-50 border border-amber-200 rounded-xl p-6 dark:bg-amber-900/20 dark:border-amber-700/30">
        <div class="flex items-center gap-3">
           <flux:icon.exclamation-triangle class="text-amber-500 dark:text-amber-300"/>

                    <div>
                <h4 class="font-semibold text-amber-800 dark:text-amber-200">Contracting Notice</h4>
                <p class="text-amber-700 dark:text-amber-300 mt-1">
                    All contracting activities must be processed through the Logistics Operations Executive. 
                    Please coordinate with the executive team for any contractual agreements.
                </p>
            </div>
        </div>
    </div>
    </div>
</div>