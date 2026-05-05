<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use App\Models\Freight;
use App\Enums\FreightStatus;
use App\Services\FreightService;

new class extends Component {
    use WithPagination;

    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $shipper_search = '';

    #[Url]
    public $status = '';

    #[Url]
    public $origin = '';

    #[Url]
    public $destination = '';

    #[Url]
    public $perPage = 12;

    #[Url]
    public $date_from = '';

    #[Url]
    public $date_to = '';

    #[Url]
    public $hazardous = '';

    public function setFilter($property, $value): void
    {
        $this->{$property} = $this->{$property} === $value ? '' : $value;
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'shipper_search', 'status', 'origin', 'destination', 'date_from', 'date_to', 'hazardous']);
        $this->resetPage();
    }

    public function removeFilter($key): void
    {
        $this->reset($key);
        $this->resetPage();
    }

    public function deleteFreight($id): void
    {
        $freight = Freight::findOrFail($id);
        $this->authorize('delete', $freight);
        $freight->delete();
        session()->flash('message', 'Freight record successfully archived.');
    }

    public function highlight($text, $search): string
    {
        if (!$search || empty($text)) return (string) $text;
        return preg_replace(
            '/(' . preg_quote($search, '/') . ')/i',
            '<span class="bg-lime-100 dark:bg-lime-900/50 text-lime-900 dark:text-lime-200 px-0.5 rounded">$1</span>',
            $text
        );
    }

    #[Computed]
    public function freights()
    {
        $user = auth()->user();
        $query = Freight::query()->with(['goods', 'creator', 'shipper']);

        // --- VISIBILITY RULES ---
        if (!$user) {
            // Guests: published only
            $query->where('status', FreightStatus::PUBLISHED);
        } elseif ($user->hasAnyRole(['admin', 'superadmin', 'logistics operations executive'])) {
            // Unrestricted: see all, optionally filter by status tab
            if ($this->status) {
                $query->where('status', $this->status);
            }
        } elseif ($user->hasRole('carrier')) {
            // Carriers: published + their own
            $query->where(function ($q) use ($user) {
                $q->where('status', FreightStatus::PUBLISHED)
                  ->orWhere('creator_id', $user->id)
                  ->orWhere('shipper_id', $user->id);
            });
            if ($this->status) {
                $query->where('status', $this->status);
            }
        } elseif ($user->hasAnyRole(['marketing logistics associate', 'operations logistics associate'])) {
            // Territory-restricted staff
            $service = app(FreightService::class);
            $territoryUserIds = $service->getTerritoryUserIds($user);

            $query->where(function ($q) use ($user, $territoryUserIds) {
                $q->where('status', FreightStatus::PUBLISHED)
                  ->orWhere('creator_id', $user->id)
                  ->orWhere(function ($sub) use ($territoryUserIds) {
                      $sub->whereIn('shipper_id', $territoryUserIds)
                          ->where('status', '!=', FreightStatus::DRAFT);
                  });
            });
            if ($this->status) {
                $query->where('status', $this->status);
            }
        } else {
            // Any other authenticated user: published only
            $query->where('status', FreightStatus::PUBLISHED);
        }

        // --- SEARCH & FILTERS ---
        $query
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->whereHas('goods', fn($g) => $g->where('name', 'like', "%{$this->search}%"))
                        ->orWhere('name', 'like', "%{$this->search}%")
                        ->orWhere('description', 'like', "%{$this->search}%");
                });
            })
            ->when($this->shipper_search, function ($q) {
                $q->whereHas('shipper', function ($sub) {
                    $sub->where('contact_person', 'like', "%{$this->shipper_search}%")
                        ->orWhere('organisation', 'like', "%{$this->shipper_search}%")
                        ->orWhere('email', 'like', "%{$this->shipper_search}%");
                });
            })
            ->when($this->origin, fn($q) => $q->where(
                fn($sub) => $sub->where('cityfrom', 'like', "%{$this->origin}%")
                                ->orWhere('countryfrom', 'like', "%{$this->origin}%")
            ))
            ->when($this->destination, fn($q) => $q->where(
                fn($sub) => $sub->where('cityto', 'like', "%{$this->destination}%")
                                ->orWhere('countryto', 'like', "%{$this->destination}%")
            ))
            ->when($this->date_from, fn($q) => $q->whereDate('datefrom', '>=', $this->date_from))
            ->when($this->date_to, fn($q) => $q->whereDate('dateto', '<=', $this->date_to))
            ->when($this->hazardous !== '', fn($q) => $q->where('is_hazardous', (bool) $this->hazardous));

        return $query->latest('updated_at')->paginate($this->perPage);
    }

    #[Computed]
    public function stats()
    {
        $user = auth()->user();
        if (!$user) return null;

        $base = Freight::query();

        if ($user->hasAnyRole(['admin', 'superadmin', 'logistics operations executive'])) {
            // all freight
        } elseif ($user->hasRole('carrier')) {
            $base->where(fn($q) => $q->where('creator_id', $user->id)->orWhere('shipper_id', $user->id));
        } elseif ($user->hasAnyRole(['marketing logistics associate', 'operations logistics associate'])) {
            $territoryUserIds = app(FreightService::class)->getTerritoryUserIds($user);
            $base->where(fn($q) => $q->where('creator_id', $user->id)->orWhereIn('shipper_id', $territoryUserIds));
        } else {
            $base->where('status', FreightStatus::PUBLISHED);
        }

        return [
            'total' => (clone $base)->count(),
            'by_status' => (clone $base)
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray(),
        ];
    }

    #[Computed]
    public function recentActivity()
    {
        if (auth()->guest() || !auth()->user()->can('viewAny', Freight::class)) {
            return collect();
        }
        return Freight::query()
            ->with(['creator', 'goods'])
            ->latest('updated_at')
            ->take(6)
            ->get();
    }
}; ?>

<div class="space-y-6">

    {{-- HEADER --}}
    <header class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">Freight Control Centre</flux:heading>
            <flux:subheading italic>Command center for logistics, cargo tracking, and bid management.</flux:subheading>
        </div>
        @can('create', App\Models\Freight::class)
            <flux:button href="{{ route('freights.create') }}" variant="primary" color="lime" icon="plus">
                Create Shipment
            </flux:button>
        @endcan
    </header>

    {{-- STATS / DASHBOARD --}}
    @auth
        @if ($this->stats)
            <div class="bg-white dark:bg-slate-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-zinc-900 dark:text-white">Overview</h2>
                        <p class="text-sm text-zinc-500">Total Visible Freight: <strong>{{ $this->stats['total'] }}</strong></p>
                    </div>
                </div>

                <div class="space-y-3">
                    <p class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Filter by Status</p>
                    <div class="flex flex-wrap gap-2">
                        <button wire:click="setFilter('status', '')"
                            class="flex items-center gap-2 px-3 py-2 rounded-xl border transition-all text-sm font-bold
                            {{ $status === '' ? 'bg-zinc-900 text-white border-zinc-900 ring-2 ring-zinc-900/20' : 'bg-white hover:bg-zinc-50 border-zinc-200 text-zinc-600' }}">
                            All
                            <span class="px-2 py-0.5 text-xs rounded-lg {{ $status === '' ? 'bg-white/20' : 'bg-zinc-100 text-zinc-600' }}">
                                {{ $this->stats['total'] }}
                            </span>
                        </button>
                        @foreach (FreightStatus::cases() as $case)
                            @php $count = $this->stats['by_status'][$case->value] ?? 0; @endphp
                            <button wire:click="setFilter('status', '{{ $case->value }}')"
                                class="flex items-center gap-2 px-3 py-2 rounded-xl border transition-all text-sm font-bold
                                {{ $status === $case->value ? 'bg-zinc-900 text-white border-zinc-900 ring-2 ring-zinc-900/20' : 'bg-white hover:bg-zinc-50 border-zinc-200 text-zinc-600' }}">
                                <div class="w-2 h-2 rounded-full bg-{{ $case->color() }}-500"></div>
                                {{ $case->label() }}
                                <span class="px-2 py-0.5 text-xs rounded-lg {{ $status === $case->value ? 'bg-white/20' : 'bg-zinc-100 text-zinc-600' }}">
                                    {{ $count }}
                                </span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @else
        {{-- Guest aggregate stats --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-6 bg-zinc-900 rounded-2xl text-white flex items-center justify-between shadow-lg">
                <div>
                    <p class="text-zinc-400 text-xs font-bold uppercase tracking-widest mb-1">Live Shipments</p>
                    <h3 class="text-4xl font-black">{{ $this->freights->total() }}</h3>
                </div>
                <div class="p-3 bg-zinc-800 rounded-xl">
                    <flux:icon name="truck" class="size-8 text-lime-500" />
                </div>
            </div>
            <div class="p-6 bg-white border border-zinc-200 rounded-2xl flex items-center justify-between shadow-sm">
                <div>
                    <p class="text-zinc-500 text-xs font-bold uppercase tracking-widest mb-1">Routes Available</p>
                    <h3 class="text-4xl font-black text-zinc-900">{{ $this->freights->total() }}</h3>
                </div>
                <div class="p-3 bg-zinc-50 rounded-xl text-zinc-400">
                    <flux:icon name="map-pin" class="size-8" />
                </div>
            </div>
        </div>
    @endauth

    {{-- SEARCH & FILTER BAR --}}
    <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl shadow-sm border border-zinc-200 dark:border-zinc-800 space-y-4">

        {{-- Row 1: Primary Search --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
            <div class="lg:col-span-4">
                <flux:input icon="magnifying-glass"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Goods, cargo description..." />
            </div>
            <div class="lg:col-span-3">
                <flux:input icon="map-pin"
                    wire:model.live.debounce.300ms="origin"
                    placeholder="Origin city or country..." />
            </div>
            <div class="lg:col-span-3">
                <flux:input icon="flag"
                    wire:model.live.debounce.300ms="destination"
                    placeholder="Destination city or country..." />
            </div>
            <div class="lg:col-span-2 flex gap-2 items-center lg:justify-end">
                <flux:select wire:model.live="perPage" class="w-full">
                    <option value="12">12 / page</option>
                    <option value="24">24 / page</option>
                    <option value="48">48 / page</option>
                </flux:select>
            </div>
        </div>

        {{-- Row 2: Advanced Filters --}}
        <div class="flex flex-wrap items-center gap-3 pt-4 border-t border-zinc-100 dark:border-zinc-800">

            {{-- Shipper Search (Staff only) --}}
            @auth
                @if(auth()->user()->hasAnyRole(['admin', 'superadmin', 'logistics operations executive', 'marketing logistics associate', 'operations logistics associate']))
                    <flux:input icon="user-group"
                        wire:model.live.debounce.300ms="shipper_search"
                        placeholder="Search shipper..."
                        class="w-48" />
                @endif
            @endauth

            {{-- Date Range --}}
            <div class="flex items-center gap-1 bg-zinc-50 dark:bg-white/5 p-1 rounded-xl border border-zinc-200 dark:border-zinc-700">
                <flux:input type="date" wire:model.live="date_from"
                    class="w-[140px] border-none shadow-none !bg-transparent focus:ring-0" />
                <span class="text-zinc-300 text-xs">—</span>
                <flux:input type="date" wire:model.live="date_to"
                    class="w-[140px] border-none shadow-none !bg-transparent focus:ring-0" />
            </div>

            {{-- Hazardous Filter --}}
            <flux:select wire:model.live="hazardous" class="w-[160px]">
                <option value="">All Cargo</option>
                <option value="1">Hazardous Only</option>
                <option value="0">Non-Hazardous</option>
            </flux:select>

            {{-- Reset --}}
            @if($search || $shipper_search || $origin || $destination || $date_from || $date_to || $hazardous !== '' || $status)
                <flux:button variant="ghost" color="red" icon="x-mark" wire:click="clearFilters" class="ml-auto">
                    Reset Filters
                </flux:button>
            @endif
        </div>
    </div>

    {{-- ACTIVE FILTER CHIPS --}}
    @if($search || $shipper_search || $origin || $destination || $date_from || $date_to || $status)
        <div class="flex flex-wrap gap-2">
            @if($search)
                <x-filter-chip label="Search: {{ $search }}" wire:click="removeFilter('search')" />
            @endif
            @if($shipper_search)
                <x-filter-chip label="Shipper: {{ $shipper_search }}" wire:click="removeFilter('shipper_search')" />
            @endif
            @if($origin)
                <x-filter-chip label="From: {{ $origin }}" wire:click="removeFilter('origin')" />
            @endif
            @if($destination)
                <x-filter-chip label="To: {{ $destination }}" wire:click="removeFilter('destination')" />
            @endif
            @if($date_from)
                <x-filter-chip label="From date: {{ $date_from }}" wire:click="removeFilter('date_from')" />
            @endif
            @if($date_to)
                <x-filter-chip label="To date: {{ $date_to }}" wire:click="removeFilter('date_to')" />
            @endif
            @if($status)
                @php $statusEnum = FreightStatus::tryFrom($status); @endphp
                <x-filter-chip label="Status: {{ $statusEnum?->label() ?? $status }}" wire:click="removeFilter('status')" />
            @endif
        </div>
    @endif

    {{-- RESULTS GRID --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse ($this->freights as $freight)
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-zinc-200 dark:border-zinc-800 overflow-hidden hover:border-lime-500 transition-all duration-300 group shadow-sm hover:shadow-xl flex flex-col">

                <div class="p-6 flex-1 flex flex-col">

                    {{-- Header: Status + Actions --}}
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex flex-col gap-1">
                            <flux:badge :color="$freight->status->color()" size="sm" variant="pill">
                                {{ $freight->status->label() }}
                            </flux:badge>
                            <span class="text-[10px] text-gray-400 font-medium ml-1">
                                {{ $freight->created_at->diffForHumans() }}
                            </span>
                        </div>

                        @auth
                            @can('update', $freight)
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                    <flux:menu>
                                        <flux:menu.item href="{{ route('freights.edit', $freight) }}" icon="pencil-square">
                                            Edit
                                        </flux:menu.item>
                                        <flux:menu.item
                                            wire:click="deleteFreight('{{ $freight->id }}')"
                                            wire:confirm="Archive this shipment?"
                                            icon="trash"
                                            variant="danger">
                                            Archive
                                        </flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            @endcan
                        @endauth
                    </div>

                    {{-- Cargo Name & Description --}}
                    <div class="mb-4">
                        <h3 class="font-bold text-lg dark:text-white leading-tight">
                            {!! $this->highlight($freight->name ?? 'Cargo Shipment', $this->search) !!}
                        </h3>
                        @if($freight->is_hazardous)
                            <flux:badge size="sm" color="red" variant="ghost" class="mt-1">
                                ⚠ Hazardous
                            </flux:badge>
                        @endif
                        @if ($freight->description)
                            <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 italic mt-1">
                                "{{ $freight->description }}"
                            </p>
                        @endif
                    </div>

                    {{-- Route --}}
                    <div class="bg-gray-50 dark:bg-slate-800/40 rounded-xl p-4 mb-4 space-y-3">
                        <div class="flex gap-3">
                            <flux:icon name="map-pin" variant="mini" class="text-lime-600 shrink-0 mt-0.5" />
                            <div class="text-xs">
                                <span class="font-bold block dark:text-zinc-200">
                                    {!! $this->highlight($freight->cityfrom, $this->origin) !!},
                                    {!! $this->highlight($freight->countryfrom, $this->origin) !!}
                                </span>
                                <span class="text-gray-500">{{ $freight->pickup_address }}</span>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <flux:icon name="flag" variant="mini" class="text-red-500 shrink-0 mt-0.5" />
                            <div class="text-xs">
                                <span class="font-bold block dark:text-zinc-200">
                                    {!! $this->highlight($freight->cityto, $this->destination) !!},
                                    {!! $this->highlight($freight->countryto, $this->destination) !!}
                                </span>
                                <span class="text-gray-500">{{ $freight->delivery_address }}</span>
                            </div>
                        </div>
                        @if($freight->distance)
                            <div class="flex items-center gap-1 text-[10px] text-zinc-400">
                                <flux:icon name="arrows-right-left" variant="mini" class="size-3" />
                                {{ number_format((float) ($freight->distance ?? 0)) }} km
                            </div>
                        @endif
                    </div>

                    {{-- Key Details Grid --}}
                    <div class="grid grid-cols-2 gap-3 text-xs mb-4">
                        <div>
                            <span class="text-gray-400 block uppercase text-[10px] font-bold">Timeline</span>
                            <span class="dark:text-white font-medium">
                                {{ $freight->datefrom?->format('d M') }} — {{ $freight->dateto?->format('d M, Y') }}
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-400 block uppercase text-[10px] font-bold">Trailer Pref.</span>
                            <div class="flex items-center gap-1 dark:text-white font-medium">
                                <flux:icon name="truck" variant="mini" class="w-3 h-3 opacity-50" />
                                {{ $freight->vehicle_type?->label() ?? 'Any' }}
                            </div>
                        </div>
                        <div>
                            <span class="text-gray-400 block uppercase text-[10px] font-bold">
                                {{ $freight->payment_option?->label() ?? 'Budget' }}
                            </span>
                            <span class="font-bold text-zinc-800 dark:text-white">
                                US${{ number_format((float) ($freight->carriage_rate ?? 0), 2) }}
                                <span class="font-normal text-zinc-400 text-[10px]">
                                    {{ $freight->payment_option?->value === 'rate_of_carriage' ? '/km' : 'total' }}
                                </span>
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-400 block uppercase text-[10px] font-bold">Capacity</span>
                            <span class="text-lime-600 font-bold">
                               {{ number_format((float) ($freight->weight ?? 0)) }}
                                {{ strtoupper($freight->capacity_unit?->value ?? '') }}
                            </span>
                        </div>
                    </div>

                    {{-- Shipper (staff only) --}}
                    @auth
                        @if(auth()->user()->hasAnyRole(['admin', 'superadmin', 'logistics operations executive', 'marketing logistics associate', 'operations logistics associate']))
                            <div class="flex items-center gap-2 pt-3 border-t border-zinc-100 dark:border-zinc-800">
                                <flux:icon name="user" variant="mini" class="size-3 text-zinc-400" />
                                <span class="text-[10px] text-zinc-500">
                                    Shipper:
                                    <span class="font-bold text-zinc-700 dark:text-zinc-300">
                                        {!! $this->highlight(
                                            $freight->shipper?->organisation ?? $freight->shipper?->contact_person ?? 'Unknown',
                                            $this->shipper_search
                                        ) !!}
                                    </span>
                                </span>
                            </div>
                        @endif
                    @endauth

                    {{-- Goods Tags --}}
                    <div class="flex flex-wrap gap-1 mt-auto pt-3">
                        @foreach ($freight->goods as $good)
                            <flux:badge size="sm" variant="ghost" class="text-[10px]">
                                {{ $good->name }}
                            </flux:badge>
                        @endforeach
                    </div>
                </div>

                {{-- Card Footer --}}
                <div class="px-6 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-t border-zinc-100 dark:border-zinc-800 flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <div class="size-2 rounded-full {{ $freight->status === FreightStatus::PUBLISHED ? 'bg-lime-500 animate-pulse' : 'bg-zinc-300' }}"></div>
                        <span class="text-xs font-bold text-zinc-500 dark:text-zinc-400">
                            {{ $freight->creator?->contact_person ?? 'System' }}
                        </span>
                    </div>
                    <flux:button
                        href="{{ route('freights.show', $freight->uuid) }}"
                        variant="filled"
                        color="lime"
                        size="sm"
                        icon-trailing="chevron-right"
                        wire:navigate>
                        View Details
                    </flux:button>
                </div>
            </div>
        @empty
            <div class="col-span-full py-20 text-center bg-zinc-50 dark:bg-zinc-900/50 rounded-3xl border-2 border-dashed border-zinc-200 dark:border-zinc-800">
                <flux:icon name="truck" class="w-12 h-12 text-zinc-300 mx-auto mb-4" />
                <p class="text-zinc-500 font-bold">No freight listings match your criteria.</p>
                <p class="text-xs text-zinc-400 mt-1">Try adjusting your filters or search terms.</p>
                <flux:button variant="ghost" class="mt-4" wire:click="clearFilters">Clear All Filters</flux:button>
            </div>
        @endforelse
    </div>

    {{-- PAGINATION --}}
    <div class="mt-8">
        {{ $this->freights->links() }}
    </div>

    {{-- RECENT ACTIVITY SIDEBAR (staff only) --}}
    @can('viewAny', App\Models\Freight::class)
        @if($this->recentActivity->count())
            <div class="bg-zinc-50 dark:bg-slate-900/50 rounded-2xl p-6 border border-zinc-200 dark:border-zinc-800 mt-4">
                <div class="flex items-center gap-2 mb-6">
                    <flux:icon name="clock" class="w-5 h-5 text-zinc-400" />
                    <flux:heading size="lg">Recent Activity</flux:heading>
                </div>
                <div class="space-y-6 relative">
                    <div class="absolute left-3 top-2 bottom-2 w-0.5 bg-zinc-200 dark:bg-slate-700"></div>
                    @foreach ($this->recentActivity as $activity)
                        <div class="relative pl-10">
                            <div class="absolute left-0 top-1 w-6 h-6 rounded-full bg-white dark:bg-slate-800 border-2 border-{{ $activity->status->color() }}-500 z-10 flex items-center justify-center">
                                <div class="w-1.5 h-1.5 rounded-full bg-{{ $activity->status->color() }}-500"></div>
                            </div>
                            <div class="text-sm">
                                <div class="flex justify-between items-start">
                                    <span class="font-bold text-zinc-900 dark:text-white">{{ $activity->name }}</span>
                                    <span class="text-[10px] text-zinc-400 uppercase tracking-tighter">
                                        {{ $activity->updated_at->diffForHumans() }}
                                    </span>
                                </div>
                                <p class="text-xs text-zinc-500 mt-1">
                                    Modified by <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $activity->creator?->contact_person ?? 'System' }}</span>
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endcan
</div>