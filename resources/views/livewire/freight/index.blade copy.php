<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use App\Models\Freight;
use App\Enums\FreightStatus;

new class extends Component {
    /**
     * URL-bound states for filtering and search
     */
    #[Url]
    public $status = 'published';

    #[Url]
    public $search = '';

    #[Url]
    public $origin = '';

    #[Url]
    public $destination = '';

    /**
     * Search Criteria: Merges status filtering with text-based search
     */
    #[Computed]
    public function getFreights()
    {
        $query = Freight::query();

        // 1. Apply Authorization Logic
        if (auth()->guest() || !auth()->user()->can('viewAny', Freight::class)) {
            // Public/Restricted View: Force 'published' status only
            $query->where('status', \App\Enums\FreightStatus::PUBLISHED);
        } else {
            // Staff/Admin View: Filter by the tab selected in the Control Centre
            $query->where('status', $this->status);
        }

        // 2. Apply the rest of the search criteria
        return $query
            ->when($this->search, function ($q) {
                $q->where(fn($sub) => $sub->whereHas('goods', fn($g) => $g->where('name', 'like', "%{$this->search}%"))->orWhere('name', 'like', "%{$this->search}%"));
            })
            ->when($this->origin, fn($q) => $q->where('cityfrom', 'like', "%{$this->origin}%"))
            ->when($this->destination, fn($q) => $q->where('cityto', 'like', "%{$this->destination}%"))
            ->with(['goods', 'creator'])
            ->latest('updated_at')
            ->get();
    }

    /**
     * Freight Activity: Provides a timeline of recent changes
     */
    #[Computed]
    public function getActivity()
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

    public function clearFilters()
    {
        $this->reset(['search', 'origin', 'destination']);
    }

    public function deleteFreight($id)
    {
        $freight = Freight::findOrFail($id);
        $freight->delete();
        session()->flash('message', 'Freight record successfully archived.');
    }
}; ?>

<div class="space-y-8">
    <header class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">Freight Control Centre</flux:heading>
            <flux:subheading italic>Command center for logistics, cargo tracking, and bid management.</flux:subheading>
        </div>

        <div class="flex gap-2">
            <flux:button href="{{ route('freights.create') }}" variant="primary" color="lime" icon="plus">
                Create Shipment
            </flux:button>
        </div>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        <main class="lg:col-span-8 space-y-6">

            <section
                class="bg-white dark:bg-slate-800 p-5 rounded-xl border border-gray-200 dark:border-slate-700 shadow-sm">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <flux:input wire:model.live.debounce.400ms="search" label="Search Cargo"
                        placeholder="e.g. Chrome Ore..." icon="magnifying-glass" />
                    <flux:input wire:model.live.debounce.400ms="origin" label="Origin" placeholder="City or Country"
                        icon="map-pin" />
                    <flux:input wire:model.live.debounce.400ms="destination" label="Destination"
                        placeholder="City or Country" icon="flag" />
                </div>

                @if ($search || $origin || $destination)
                    <div class="mt-4 flex justify-end">
                        <flux:button wire:click="clearFilters" variant="ghost" size="xs" icon="x-mark">Reset All
                            Filters</flux:button>
                    </div>
                @endif
            </section>

            @can('viewAny', App\Models\Freight::class)
                <nav class="flex items-center gap-2 overflow-x-auto pb-2 no-scrollbar">
                    @foreach (FreightStatus::cases() as $case)
                        @php $isActive = $status === $case->value; @endphp
                        <button wire:click="$set('status', '{{ $case->value }}')"
                            class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all whitespace-nowrap
                        {{ $isActive
                            ? 'bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 shadow-md'
                            : 'bg-white dark:bg-slate-800 text-gray-500 border border-gray-200 dark:border-slate-700 hover:bg-gray-50' }}">
                            <div class="w-2 h-2 rounded-full bg-{{ $case->color() }}-500"></div>
                            {{ $case->label() }}
                            <span class="opacity-60 text-xs">({{ Freight::where('status', $case->value)->count() }})</span>
                        </button>
                    @endforeach
                </nav>
            @else
                <div class="pb-4 border-b border-gray-100 dark:border-slate-800">
                    <flux:heading size="lg">Available Shipments</flux:heading>
                </div>
            @endcan

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @forelse ($this->getFreights as $freight)
                    <div
                        class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-5 group hover:border-lime-500 transition-all shadow-sm flex flex-col">

                        <div class="flex justify-between items-start mb-3">
                            <div class="flex flex-col gap-1">
                                <flux:badge :color="$freight->status->color()" size="sm" variant="pill">
                                    {{ $freight->status->label() }}
                                </flux:badge>
                                <span class="text-[10px] text-gray-400 font-medium ml-1">
                                    Listed {{ $freight->created_at->diffForHumans() }}
                                </span>
                            </div>

                            <flux:dropdown>
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                <flux:menu>
                                    <flux:menu.item href="{{ route('freights.edit', $freight) }}" icon="pencil-square">
                                        Edit</flux:menu.item>
                                    <flux:menu.item wire:click="deleteFreight('{{ $freight->id }}')"
                                        wire:confirm="Archive this shipment?" icon="trash" variant="danger">Archive
                                    </flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </div>

                        <div class="mb-4">
                            <h3 class="font-bold text-lg dark:text-white leading-tight">
                                {{ $freight->name ?? 'Cargo Shipment' }}
                            </h3>
                            @if ($freight->description)
                                <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-1 italic mt-1">
                                    "{{ $freight->description }}"
                                </p>
                            @endif
                        </div>

                        <div class="bg-gray-50 dark:bg-slate-900/40 rounded-lg p-4 mb-4 space-y-3">
                            <div class="flex gap-3">
                                <flux:icon name="map-pin" variant="mini" class="text-lime-600 shrink-0" />
                                <div class="text-xs">
                                    <span class="font-bold block dark:text-gray-600">{{ $freight->cityfrom }},
                                        {{ $freight->countryfrom }}</span>
                                    <span class="text-gray-500">{{ $freight->pickup_address }}</span>
                                </div>
                            </div>

                            <div class="flex gap-3">
                                <flux:icon name="flag" variant="mini" class="text-red-500 shrink-0" />
                                <div class="text-xs">
                                    <span class="font-bold block dark:text-gray-600">{{ $freight->cityto }},
                                        {{ $freight->countryto }}</span>
                                    <span class="text-gray-500">{{ $freight->delivery_address }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-y-4 gap-x-2 text-xs mb-4">
                            <div>
                                <span class="text-gray-400 block uppercase text-[10px] font-bold">Timeline</span>
                                <span class="dark:text-white font-medium">
                                    {{ $freight->datefrom->format('d M') }} — {{ $freight->dateto->format('d M, Y') }}
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-400 block uppercase text-[10px] font-bold">Vehicle Pref</span>
                                <div class="flex items-center gap-1 dark:text-white font-medium">
                                    <flux:icon name="truck" variant="mini" class="w-3 h-3 opacity-50" />
                                    {{ $freight->vehicle_type?->label() ?? 'Any' }}
                                </div>
                            </div>
                            <div>
                                <span class="text-gray-400 block uppercase text-[10px] font-bold">{{ $freight->payment_option?->label() ?? 'Negotiable' }}</span>
                                                <div class="font-bold mt-1">
                    US${{ number_format((float) $freight->carriage_rate, 2) }}
                     <span class="font-normal text-cyan-100">
                        {{ $freight->payment_option->value == 'rate_of_carriage' ? ' $/km' : 'total' }}</span> 
                </div>
                            </div>
                            <div>
                                <span class="text-gray-400 block uppercase text-[10px] font-bold">Capacity</span>
                                <span class="text-lime-600 font-bold">
                                    {{ number_format((float) $freight->weight) }}
                                    {{ strtoupper($freight->capacity_unit?->value ?? 'Units') }}
                                </span>
                            </div>
                            <flux:button 
                                href="{{ route('freights.show', $freight->uuid) }}" 
                                variant="filled" 
                                color="lime" 
                                size="sm" 
                                icon-trailing="chevron-right"
                                wire:navigate
                            >
                                View Details
                            </flux:button>                            
                        </div>

                        <div
                            class="flex flex-wrap gap-1 mt-auto pt-4 border-t border-gray-100 dark:border-slate-700/50">
                            @foreach ($freight->goods as $good)
                                <flux:badge size="sm" variant="ghost" class="text-[10px]">
                                    {{ $good->name }}
                                </flux:badge>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div
                        class="md:col-span-2 py-20 bg-gray-50 dark:bg-slate-900/50 rounded-2xl border-2 border-dashed border-gray-200 dark:border-slate-800 text-center">
                        <flux:icon name="truck" class="w-12 h-12 text-gray-300 mx-auto mb-4" />
                        <p class="text-gray-500 font-medium">No freight listings match your criteria.</p>
                    </div>
                @endforelse
            </div>
        </main>

        <aside class="lg:col-span-4 space-y-6">
            @can('viewAny', App\Models\Freight::class)
                <div class="bg-gray-50 dark:bg-slate-900/50 rounded-2xl p-6 border border-gray-200 dark:border-slate-800">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-2">
                            <flux:icon name="clock" class="w-5 h-5 text-gray-400" />
                            <flux:heading size="lg">Recent Activity</flux:heading>
                        </div>
                    </div>

                    <div class="space-y-8 relative">
                        <div class="absolute left-3 top-2 bottom-2 w-0.5 bg-gray-200 dark:bg-slate-700"></div>

                        @foreach ($this->getActivity as $activity)
                            <div class="relative pl-10">
                                <div
                                    class="absolute left-0 top-1 w-6 h-6 rounded-full bg-white dark:bg-slate-800 border-2 border-{{ $activity->status->color() }}-500 z-10 flex items-center justify-center">
                                    <div class="w-1.5 h-1.5 rounded-full bg-{{ $activity->status->color() }}-500"></div>
                                </div>

                                <div class="text-sm">
                                    <div class="flex justify-between items-start">
                                        <span class="font-bold text-gray-900 dark:text-white">Status Update</span>
                                        <span class="text-[10px] text-gray-400 uppercase tracking-tighter">
                                            {{ $activity->updated_at->diffForHumans() }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                                        <span class="text-gray-700 dark:text-gray-300 font-medium">
                                            {{ $activity->name }}
                                        </span> was modified by {{ $activity->creator->contact_person ?? 'System' }}.
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="bg-lime-600 rounded-2xl p-6 text-white overflow-hidden relative group">
                    <flux:icon name="information-circle"
                        class="w-24 h-24 absolute -bottom-6 -right-6 text-lime-500 opacity-50" />
                    <h4 class="font-bold text-lg mb-2 relative z-10">Market Insights</h4>
                    <p class="text-sm text-lime-100 relative z-10">
                        You are currently viewing active public shipments. Log in as staff to access the Freight Control
                        Centre and audit logs.
                    </p>
                </div>
            @endcan
        </aside>

    </div>
</div>
