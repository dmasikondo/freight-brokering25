<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Freight;
use App\Models\Lane;
use App\Models\Territory;
use App\Models\ZimbabweCity;
use App\Models\Country;
use App\Enum\FreightStatus;
use App\Enum\ShipmentStatus;
use App\Enum\LaneStatus;
use App\Enum\VehiclePositionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

new class extends Component {
    public array $stats = [];
    public array $recentShippers = [];
    public array $recentCarriers = [];
    public array $marketingAssociates = [];
    public array $procurementAssociates = [];
    public array $operationsAssociates = [];
    public array $assignedTerritories = [];
    public array $shipmentBreakdown = [];
    public array $vehicleBreakdown = [];

    public function mount()
    {
        $this->loadDashboard();
    }

    public function loadDashboard()
    {
        /** @var User $user */
        $user = Auth::user();

        // 1. Geography & Territory Scoping
        $territoryCollection = $user
            ->territories()
            ->with(['countries', 'zimbabweCities', 'provinces.zimbabweCities'])
            ->get();

        $userTerritoryIds = $territoryCollection->pluck('id');
        $this->assignedTerritories = $territoryCollection->toArray();
        $bounds = $this->getGeographicalBounds($territoryCollection);

        // 2. Define Scope IDs
        // Clients: Owned OR in Territory
        $scopedClientIds = User::query()
            ->where(function (Builder $mainQuery) use ($user, $bounds) {
                $mainQuery->whereHas('createdBy', fn($q) => $q->where('user_creations.creator_user_id', $user->id))->orWhereHas('buslocation', function ($q) use ($bounds) {
                    $q->where(function ($sub) use ($bounds) {
                        if (!empty($bounds['countries'])) {
                            $sub->whereIn('country', $bounds['countries']);
                        }
                        if (!empty($bounds['cities'])) {
                            $sub->orWhereIn('city', $bounds['cities']);
                        }
                        if (empty($bounds['countries']) && empty($bounds['cities'])) {
                            $sub->whereRaw('1 = 0');
                        }
                    });
                });
            })
            ->pluck('id');

        // Associates: Shared Territory
        $scopedAssociateIds = User::query()->whereHas('territories', fn($q) => $q->whereIn('territories.id', $userTerritoryIds))->pluck('id');

        // 3. Compliance Logic
        $getIncompleteCount = function ($u) {
            $missing = 0;
            if ($u->buslocation()->count() < 1) {
                $missing++;
            }
            if ($u->fleets()->count() < 1) {
                $missing++;
            }
            if ($u->directors()->count() < 2) {
                $missing++;
            }
            if ($u->traderefs()->count() < 2) {
                $missing++;
            }
            return $missing;
        };

        // 4. Queries
        $carriers = User::whereHas('roles', fn($q) => $q->where('name', 'carrier'))->whereIn('id', $scopedClientIds);
        $shippers = User::whereHas('roles', fn($q) => $q->where('name', 'shipper'))->whereIn('id', $scopedClientIds);

        $marketing = User::whereHas('roles', fn($q) => $q->where('name', 'marketing logistics associate'))->whereIn('id', $scopedAssociateIds);
        $procurement = User::whereHas('roles', fn($q) => $q->where('name', 'procurement logistics associate'))->whereIn('id', $scopedAssociateIds);
        $ops = User::whereHas('roles', fn($q) => $q->where('name', 'operations logistics associate'))->whereIn('id', $scopedAssociateIds);

        $freights = Freight::whereIn('creator_id', $scopedClientIds);
        $lanes = Lane::whereIn('creator_id', $scopedClientIds);

        // 5. Analytics & Stats
        $this->stats = [
            'shippers_count' => $shippers->count(),
            'carriers_count' => $carriers->count(),
            'ops_associates_count' => $ops->count(),
            'active_shipments' => (clone $freights)->whereIn('shipment_status', ['loading', 'in transit'])->count(),
            'available_vehicles' => (clone $lanes)->where('vehicle_status', 'ready')->count(),
            'scoped_incomplete_carriers' => (clone $carriers)
                ->where(function ($q) {
                    $q->whereDoesntHave('buslocation')->orWhereDoesntHave('fleets')->orWhereHas('directors', null, '<', 2)->orWhereHas('traderefs', null, '<', 2);
                })
                ->count(),
        ];

        // 6. Detailed Breakdown for Executives
        $this->shipmentBreakdown = [
            'published' => (clone $freights)->where('status', 'published')->count(),
            'pending' => (clone $freights)->where('status', 'submitted')->count(),
            'in_transit' => (clone $freights)->where('shipment_status', 'in transit')->count(),
        ];

        $this->vehicleBreakdown = [
            'published' => (clone $lanes)->where('status', 'published')->count(),
            'ready' => (clone $lanes)->where('vehicle_status', 'ready')->count(),
            'loading' => (clone $lanes)->where('vehicle_status', 'loading')->count(),
        ];

        // 7. Associate Mapping
        $mapAssociate = fn($u) => ['name' => $u->contact_person ?? $u->name, 'email' => $u->email, 'slug' => $u->slug];
        $this->marketingAssociates = $marketing->get()->map($mapAssociate)->toArray();
        $this->procurementAssociates = $procurement->get()->map($mapAssociate)->toArray();
        $this->operationsAssociates = $ops->get()->map($mapAssociate)->toArray();

        // 8. Recent Scoped Intake
        $this->recentShippers = $shippers
            ->latest()
            ->take(5)
            ->get()
            ->map(
                fn($u) => [
                    'name' => $u->organisation ?? $u->name,
                    'email' => $u->email,
                    'slug' => $u->slug,
                    'created_at' => $u->created_at->diffForHumans(),
                ],
            )
            ->toArray();

        $this->recentCarriers = $carriers
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($u) use ($getIncompleteCount) {
                $m = $getIncompleteCount($u);
                return [
                    'name' => $u->organisation ?? $u->name,
                    'email' => $u->email,
                    'slug' => $u->slug,
                    'created_at' => $u->created_at->diffForHumans(),
                    'is_complete' => $m === 0,
                    'missing' => $m,
                ];
            })
            ->toArray();
    }

    private function getGeographicalBounds($territories): array
    {
        $c = collect($territories);
        return [
            'countries' => $c->flatMap(fn($t) => $t->countries ?? [])->pluck('name')->unique()->reject(fn($n) => strtolower($n) === 'zimbabwe')->values()->toArray(),
            'cities' => $c->flatMap(fn($t) => ($t->zimbabweCities ?? collect())->concat(collect($t->provinces ?? [])->flatMap(fn($p) => $p->zimbabweCities ?? [])))->pluck('name')->unique()->values()->toArray(),
        ];
    }
}; ?>

<div class="min-h-screen bg-slate-50 pb-16 font-sans antialiased text-slate-900">
    <!-- Executive Top Nav -->
    <div class="bg-white border-b border-slate-200 sticky top-0 z-50 shadow-sm backdrop-blur-md bg-white/90">
        <div
            class="max-w-screen-2xl mx-auto px-6 lg:px-12 py-5 flex flex-col lg:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-indigo-600 rounded-2xl shadow-lg shadow-indigo-200 text-white">
                    <x-graphic name="presentation-chart-bar" class="w-8 h-8" />
                </div>
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-slate-900">Operations Executive Hub</h1>
                    <div class="flex items-center gap-2 mt-0.5">
                        <x-graphic name="globe-alt" class="w-4 h-4 text-emerald-500" />
                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">
                            Territories: @forelse($assignedTerritories as $t)
                                <span
                                    class="text-emerald-600 font-bold underline decoration-emerald-200 underline-offset-4">{{ $t['name'] }}</span>
                            @empty Global
                            @endforelse
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <flux:button href="{{ route('users.create') }}" variant="primary" color="indigo" icon="user-plus"
                    wire:navigate>Register Shipper</flux:button>
                <flux:button href="{{ route('users.create') }}" variant="primary" color="emerald" icon="truck"
                    wire:navigate>Register Carrier</flux:button>
                <flux:button href="{{ route('freights.create') }}" variant="primary" color="sky" icon="document-plus"
                    wire:navigate>Create Shipment</flux:button>
                <flux:button href="{{ route('lanes.create') }}" variant="primary" color="purple"
                    icon="clipboard-document-list" wire:navigate>Post Truck</flux:button>
            </div>
        </div>
    </div>

    <div class="max-w-screen-2xl mx-auto px-6 lg:px-12 mt-10 space-y-10">
        <!-- High-Level KPIs -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6">
            <div class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Shippers</p>
                <p class="text-4xl font-black text-slate-900">{{ $stats['shippers_count'] }}</p>
            </div>
            <div class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Carriers</p>
                <p class="text-4xl font-black text-slate-900">{{ $stats['carriers_count'] }}</p>
            </div>
            <div class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Ops Associates</p>
                <p class="text-4xl font-black text-indigo-600">{{ $stats['ops_associates_count'] }}</p>
            </div>
            <div class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Live Shipments</p>
                <p class="text-4xl font-black text-sky-600">{{ $stats['active_shipments'] }}</p>
            </div>
            <div class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Ready Vehicles</p>
                <p class="text-4xl font-black text-emerald-600">{{ $stats['available_vehicles'] }}</p>
            </div>
            <div class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Partial Regs</p>
                <p class="text-4xl font-black text-amber-600">{{ $stats['scoped_incomplete_carriers'] }}</p>
            </div>
        </div>

        <!-- Logistics Analytics Summaries -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Shipment Summary -->
            <div class="bg-slate-900 rounded-[2.5rem] p-8 text-white shadow-xl">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-lg font-black uppercase tracking-tighter flex items-center gap-3">
                        <x-graphic name="cube" class="w-6 h-6 text-sky-400" />
                        Shipment Lifecycle (Freight)
                    </h3>
                    <flux:link href="{{ route('freights.index') }}" variant="subtle" class="!text-sky-300 font-bold"
                        wire:navigate>Explore Details</flux:link>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-white/5 p-5 rounded-2xl border border-white/10">
                        <p class="text-3xl font-black mb-1">{{ $shipmentBreakdown['published'] }}</p>
                        <p class="text-[9px] font-black text-slate-400 uppercase">Published</p>
                    </div>
                    <div class="bg-white/5 p-5 rounded-2xl border border-white/10">
                        <p class="text-3xl font-black mb-1">{{ $shipmentBreakdown['pending'] }}</p>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">Pending Approval</p>
                    </div>
                    <div class="bg-white/5 p-5 rounded-2xl border border-white/10">
                        <p class="text-3xl font-black mb-1">{{ $shipmentBreakdown['in_transit'] }}</p>
                        <p class="text-[9px] font-black text-slate-400 uppercase">On Road</p>
                    </div>
                </div>
            </div>

            <!-- Vehicle Summary -->
            <div class="bg-white rounded-[2.5rem] p-8 border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-lg font-black uppercase tracking-tighter flex items-center gap-3">
                        <x-graphic name="truck" class="w-6 h-6 text-emerald-500" />
                        Asset Availability (Lanes)
                    </h3>
                    <flux:link href="{{ route('lanes.index') }}" variant="subtle" class="!text-emerald-600 font-bold"
                        wire:navigate>Vehicle Map</flux:link>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100">
                        <p class="text-3xl font-black mb-1">{{ $vehicleBreakdown['published'] }}</p>
                        <p class="text-[9px] font-black text-slate-400 uppercase">Postings</p>
                    </div>
                    <div class="bg-emerald-50 p-5 rounded-2xl border border-emerald-100">
                        <p class="text-3xl font-black text-emerald-700 mb-1">{{ $vehicleBreakdown['ready'] }}</p>
                        <p class="text-[9px] font-black text-emerald-600 uppercase">Ready Loads</p>
                    </div>
                    <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100">
                        <p class="text-3xl font-black mb-1">{{ $vehicleBreakdown['loading'] }}</p>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">Currently Loading</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Regional Associate Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Marketing Associates -->
            <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm">
                <h3
                    class="text-sm font-black text-slate-900 uppercase tracking-widest mb-6 flex items-center justify-between">
                    <span>Marketing Team</span>
                    <span
                        class="bg-purple-100 text-purple-700 px-2 py-1 rounded-full text-[10px]">{{ count($marketingAssociates) }}</span>
                </h3>
                <div class="space-y-4">
                    @forelse($marketingAssociates as $u)
                        <flux:link href="{{ route('users.show', ['user' => $u['slug']]) }}" wire:navigate
                            class="block group">
                            <p class="text-sm font-bold text-slate-800 group-hover:text-purple-600 transition-colors">
                                {{ $u['name'] }}</p>
                            <p class="text-[10px] text-slate-400 font-medium">{{ $u['email'] }}</p>
                        </flux:link>
                    @empty
                        <p class="text-xs text-slate-400 italic">No marketing associates found.</p>
                    @endforelse
                </div>
            </div>

            <!-- Procurement Associates -->
            <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm">
                <h3
                    class="text-sm font-black text-slate-900 uppercase tracking-widest mb-6 flex items-center justify-between">
                    <span>Procurement Team</span>
                    <span
                        class="bg-orange-100 text-orange-700 px-2 py-1 rounded-full text-[10px]">{{ count($procurementAssociates) }}</span>
                </h3>
                <div class="space-y-4">
                    @forelse($procurementAssociates as $u)
                        <flux:link href="{{ route('users.show', ['user' => $u['slug']]) }}" wire:navigate
                            class="block group">
                            <p class="text-sm font-bold text-slate-800 group-hover:text-orange-600 transition-colors">
                                {{ $u['name'] }}</p>
                            <p class="text-[10px] text-slate-400 font-medium">{{ $u['email'] }}</p>
                        </flux:link>
                    @empty
                        <p class="text-xs text-slate-400 italic">No procurement associates found.</p>
                    @endforelse
                </div>
            </div>

            <!-- Operations Associates (New) -->
            <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm">
                <h3
                    class="text-sm font-black text-slate-900 uppercase tracking-widest mb-6 flex items-center justify-between">
                    <span>Operations Associates</span>
                    <span
                        class="bg-indigo-100 text-indigo-700 px-2 py-1 rounded-full text-[10px]">{{ count($operationsAssociates) }}</span>
                </h3>
                <div class="space-y-4">
                    @forelse($operationsAssociates as $u)
                        <flux:link href="{{ route('users.show', ['user' => $u['slug']]) }}" wire:navigate
                            class="block group">
                            <p class="text-sm font-bold text-slate-800 group-hover:text-indigo-600 transition-colors">
                                {{ $u['name'] }}</p>
                            <p class="text-[10px] text-slate-400 font-medium">{{ $u['email'] }}</p>
                        </flux:link>
                    @empty
                        <p class="text-xs text-slate-400 italic">No operations associates found.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Scoped Intake: Recent Regional Activity -->
        <div class="grid grid-cols-1 gap-12">
            <!-- Full Width Shippers -->
            <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-10 py-6 bg-slate-50/50 border-b border-slate-100 flex justify-between items-center">
                    <h4 class="text-sm font-black text-slate-900 uppercase tracking-widest">Recent Regional Shipper
                        Intake</h4>
                    <flux:link href="{{ route('users.index', ['role' => 'shipper']) }}" size="sm"
                        variant="subtle" wire:navigate>View Full Directory</flux:link>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($recentShippers as $s)
                        <div
                            class="px-12 py-6 hover:bg-slate-50 transition-all group">
                            <flux:link href="{{ route('users.show', ['user' => $s['slug']]) }}" wire:navigate class="flex items-center justify-between ">
                                <div class="flex items-center gap-6">
                                    <div
                                        class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 group-hover:scale-105 transition-transform">
                                        <x-graphic name="building-office-2" class="w-7 h-7" /></div>
                                    <div>
                                        <p class="text-lg font-black text-slate-900">{{ $s['name'] }}</p>
                                        <p class="text-xs text-slate-400 font-bold uppercase tracking-tight">
                                            {{ $s['email'] }}</p>
                                    </div>
                                </div>
                                <span
                                    class="text-[10px] font-black uppercase text-indigo-400 bg-indigo-50/50 px-3 py-1 rounded border border-indigo-100">Detected
                                    {{ $s['created_at'] }}</span>
                            </flux:link>
                        </div>

                    @empty
                        <p class="p-20 text-center text-slate-400 font-medium italic">No regional shippers found in the
                            current jurisdiction.</p>
                    @endforelse
                </div>
            </div>

            <!-- Full Width Carriers -->
            <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-10 py-6 bg-slate-50/50 border-b border-slate-100 flex justify-between items-center">
                    <h4 class="text-sm font-black text-slate-900 uppercase tracking-widest">Recent Regional Carrier
                        Intake</h4>
                    <flux:link href="{{ route('users.index', ['role' => 'carrier']) }}" size="sm"
                        variant="subtle" wire:navigate>View Full Directory</flux:link>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($recentCarriers as $c)
                        <div
                            class=" px-12 py-6 hover:bg-slate-50 transition-all group">
                            <flux:link href="{{ route('users.show', ['user' => $c['slug']]) }}" wire:navigate class="flex items-center justify-between">
                                <div class="flex items-center gap-6">
                                    <div
                                        class="w-14 h-14 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 group-hover:scale-105 transition-transform">
                                        <x-graphic name="truck" class="w-7 h-7" /></div>
                                    <div>
                                        <p class="text-lg font-black text-slate-900">{{ $c['name'] }}</p>
                                        <p class="text-xs text-slate-400 font-bold uppercase tracking-tight">
                                            {{ $c['email'] }}</p>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    @if ($c['is_complete'])
                                        <span
                                            class="text-[9px] font-black uppercase bg-emerald-50 text-emerald-700 px-3 py-1 rounded-full border border-emerald-200">Compliance
                                            Validated</span>
                                    @else
                                        <span
                                            class="text-[9px] font-black uppercase bg-amber-50 text-amber-700 px-3 py-1 rounded-full border border-amber-200">Pending
                                            {{ $c['missing'] }} Components</span>
                                    @endif
                                    <p class="text-[10px] text-slate-400 font-bold italic">Member since
                                        {{ $c['created_at'] }}</p>
                                </div>
                            </flux:link>
                        </div>

                    @empty
                        <p class="p-20 text-center text-slate-400 font-medium italic">No regional carriers found in the
                            current jurisdiction.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Executive Contracting Notice -->
        <div class="bg-amber-50 border border-amber-200 rounded-[2.5rem] p-10 flex items-center gap-8 shadow-sm">
            <div class="w-16 h-16 bg-amber-100 rounded-3xl flex items-center justify-center text-amber-600 shrink-0">
                <flux:icon.exclamation-triangle class="w-8 h-8" />
            </div>
            <div class="flex-1">
                <h4 class="text-xl font-black text-amber-900 mb-1">Executive Contracting Authority</h4>
                <p class="text-amber-800 text-sm font-medium leading-relaxed max-w-4xl">
                    Final ratification of all regional logistics contracts is restricted to the Executive Hub. Please
                    ensure that all associate-gathered data (specifically Buslocations and Trailer specs) has been
                    audited for completeness before attempting to bind the company to any third-party carrier
                    agreements.
                </p>
            </div>
            <flux:button variant="subtle" color="amber" class="font-black px-6 py-2 rounded-xl">Ops Manual
            </flux:button>
        </div>
    </div>
</div>
