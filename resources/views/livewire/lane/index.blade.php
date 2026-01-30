<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use App\Models\Lane;
use App\Enums\LaneStatus;
use App\Enums\VehiclePositionStatus;
use App\Enums\TrailerType;
use App\Services\LaneService;
use Livewire\WithPagination;
use Livewire\Attributes\Lazy;

new class extends Component {
    use WithPagination;

    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $carrier_search = '';

    #[Url]
    public $perPage = 12;

    #[Url]
    public $status = '';

    #[Url]
    public $vehicle_status = '';

    #[Url]
    public $available_date = '';

    #[Url]
    public $min_rate = '';

    #[Url]
    public $max_rate = '';

    #[Url]
    public $rate_type = '';

    public $trailerFilters = [];

    public function setFilter($property, $value)
    {
        $this->{$property} = $this->{$property} === $value ? null : $value;
        $this->resetPage();
    }

    /**
     * Gets trailers currently in the DB for the filter dropdown
     */
    public function availableTrailers()
    {
        // Note: Using the Trailer Enum if available, otherwise raw pluck
        return Lane::distinct()->pluck('trailer')->filter()->values()->all();
    }

    public function toggleTrailer($value)
    {
        // Ensure it's an array before processing
        if (!is_array($this->trailerFilters)) {
            $this->trailerFilters = [];
        }

        if (in_array($value, $this->trailerFilters)) {
            // Remove the value
            $this->trailerFilters = array_diff($this->trailerFilters, [$value]);
        } else {
            // Add the value
            $this->trailerFilters[] = $value;
        }

        // Reset keys to prevent potential JSON object vs array issues
        $this->trailerFilters = array_values($this->trailerFilters);
    }

    /**
     * Generates stats for the Fleet Control Center (User's own lanes)
     */
    #[Computed]
    public function myStats()
    {
        $user = auth()->user();
        if (!$user) {
            return null;
        }

        // Use the exact same query logic defined in the Service
        // We pass empty filters [] because stats should represent the total available
        $base = app(LaneService::class)->getVisibleLanesQuery($user, []);

        return [
            'total_capacity' => (clone $base)->count(),

            'by_lane_status' => (clone $base)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status')->toArray(),

            'by_vehicle_status' => (clone $base)->selectRaw('vehicle_status, count(*) as total')->groupBy('vehicle_status')->pluck('total', 'vehicle_status')->toArray(),
        ];
    }

    /**
     * The Main Query - Now using our updated Service
     */
    #[Computed]
    public function lanes()
    {
        return app(LaneService::class)->getVisibleLanes(
            auth()->user(),
            [
                'search' => $this->search,
                'carrier_search' => $this->carrier_search,
                'status' => $this->status,
                'vehicle_status' => $this->vehicle_status,
                'available_date' => $this->available_date,
                'min_rate' => $this->min_rate,
                'max_rate' => $this->max_rate,
                'rate_type' => $this->rate_type,
                'trailerFilters' => $this->trailerFilters,
            ],
            (int) $this->perPage,
        );
    }

    public function highlight($text, $search)
    {
        if (!$search || empty($text)) {
            return $text;
        }

        return preg_replace('/(' . preg_quote($search, '/') . ')/i', '<span class="bg-lime-100 dark:bg-lime-900/50 text-lime-900 dark:text-lime-200 px-0.5 rounded">$1</span>', $text);
    }

    public function clearFilters()
    {
        $this->reset(['search', 'carrier_search', 'status', 'vehicle_status', 'available_date', 'min_rate', 'max_rate', 'rate_type', 'trailerFilters']);
        // Explicitly re-initialize to be safe
        $this->trailerFilters = [];
        $this->resetPage();
    }
    public function removeFilter($key, $value = null)
    {
        if ($key === 'trailerFilters') {
            // Remove just this specific trailer from the array
            $this->trailerFilters = array_diff($this->trailerFilters, [$value]);
        } else {
            // Reset specific property
            $this->reset($key);
        }
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div class="p-6 max-w-5xl mx-auto animate-pulse">
            <div class="h-64 w-full bg-zinc-100 dark:bg-zinc-800/50 rounded-3xl mb-8"></div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="md:col-span-2 space-y-6">
                    <div class="h-10 w-3/4 bg-zinc-200 dark:bg-zinc-800 rounded"></div>
                    <div class="h-4 w-full bg-zinc-100 dark:bg-zinc-800 rounded"></div>
                    <div class="h-4 w-5/6 bg-zinc-100 dark:bg-zinc-800 rounded"></div>
                </div>

                <div class="h-48 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl p-6">
                    <div class="h-4 w-full bg-zinc-100 dark:bg-zinc-800 rounded mb-4"></div>
                    <div class="h-10 w-full bg-cyan-100 dark:bg-cyan-900/30 rounded-xl"></div>
                </div>
            </div>
        </div>
        HTML;
    }
}; ?>

<div class="space-y-6">
    {{-- 1. DASHBOARD / METRICS SECTION --}}
    @auth
        @if ($this->myStats)
            <div class="bg-white dark:bg-slate-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-zinc-900 dark:text-white">Fleet Control Center</h2>
                        <p class="text-sm text-zinc-500">Total Visible Capacity:
                            <strong>{{ $this->myStats['total_capacity'] }}</strong>
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    {{-- Status Filter Buttons --}}
                    <div class="space-y-3">
                        <p class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Listing Status</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach (App\Enums\LaneStatus::cases() as $lStatus)
                                @php $count = $this->myStats['by_lane_status'][$lStatus->value] ?? 0; @endphp
                                <button wire:click="setFilter('status', '{{ $lStatus->value }}')"
                                    class="flex items-center gap-2 px-3 py-2 rounded-xl border transition-all {{ $status === $lStatus->value ? 'bg-zinc-900 text-white border-zinc-900 ring-2 ring-zinc-900/20' : 'bg-white hover:bg-zinc-50 border-zinc-200' }}">
                                    <span class="size-2 rounded-full"
                                        style="background-color: {{ $lStatus->color() }}"></span>
                                    <span class="text-sm font-bold">{{ $lStatus->label() }}</span>
                                    <span
                                        class="px-2 py-0.5 text-xs rounded-lg {{ $status === $lStatus->value ? 'bg-white/20' : 'bg-zinc-100 text-zinc-600' }}">
                                        {{ $count }}
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Activity Filter Buttons --}}
                    <div class="space-y-3">
                        <p class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Vehicle Activity</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach (App\Enums\VehiclePositionStatus::cases() as $vStatus)
                                @php $count = $this->myStats['by_vehicle_status'][$vStatus->value] ?? 0; @endphp
                                <button wire:click="setFilter('vehicle_status', '{{ $vStatus->value }}')"
                                    class="flex items-center gap-2 px-3 py-2 rounded-xl border transition-all {{ $vehicle_status === $vStatus->value ? 'bg-lime-600 text-white border-lime-600 ring-2 ring-lime-600/20' : 'bg-white hover:bg-zinc-50 border-zinc-200' }}">
                                    <span class="text-sm font-bold">{{ $vStatus->label() }}</span>
                                    <span
                                        class="px-2 py-0.5 text-xs rounded-lg {{ $vehicle_status === $vStatus->value ? 'bg-black/10' : 'bg-lime-50 text-lime-700' }}">
                                        {{ $count }}
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @else
        {{-- Guest Section: Public Aggregate Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-6 bg-zinc-900 rounded-2xl text-white flex items-center justify-between shadow-lg">
                <div>
                    <p class="text-zinc-400 text-xs font-bold uppercase tracking-widest mb-1">Live Market Vehicles</p>
                    <h3 class="text-4xl font-black">{{ $this->lanes->total() }}</h3>
                </div>
                <div class="p-3 bg-zinc-800 rounded-xl">
                    <flux:icon name="truck" class="size-8 text-lime-500" />
                </div>
            </div>
            <div class="p-6 bg-white border border-zinc-200 rounded-2xl flex items-center justify-between shadow-sm">
                <div>
                    <p class="text-zinc-500 text-xs font-bold uppercase tracking-widest mb-1">Active Trailer Types</p>
                    <h3 class="text-4xl font-black text-zinc-900">{{ count($this->availableTrailers()) }}</h3>
                </div>
                <div class="p-3 bg-zinc-50 rounded-xl text-zinc-400">
                    <flux:icon name="adjustments-horizontal" class="size-8" />
                </div>
            </div>
        </div>
    @endauth

    {{-- 2. ENHANCED SEARCH & FILTER BAR --}}
    <div
        class="bg-white dark:bg-slate-900 p-5 rounded-3xl shadow-sm border border-zinc-200 dark:border-zinc-800 space-y-4">
        {{-- ROW 1: PRIMARY SEARCH --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
            {{-- General Search --}}
            <div class="lg:col-span-4">
                <flux:input icon="magnifying-glass" wire:model.live.debounce.300ms="search"
                    placeholder="City, Country, Capacity..." />
            </div>

            {{-- Carrier Search (Staff Only) --}}
            <div class="lg:col-span-4">
                @if (auth()->user()
                        ?->hasAnyRole(['admin', 'superadmin', 'operations logistics associate', 'procurement executive associate']))
                    <flux:input icon="user-group" wire:model.live.debounce.300ms="carrier_search"
                        placeholder="Search Carrier/Staff..." />
                @else
                    <flux:input icon="user-group" disabled placeholder="Staff search restricted" />
                @endif
            </div>

            {{-- Pagination & Date --}}
            <div class="lg:col-span-4 flex gap-2 items-center lg:justify-end">
                <flux:input type="date" wire:model.live="available_date" class="flex-1 lg:max-w-[160px]" />

                <flux:select wire:model.live="perPage" class="w-[100px]">
                    <option value="12">12 / page</option>
                    <option value="24">24 / page</option>
                    <option value="48">48 / page</option>
                </flux:select>
            </div>
        </div>

        {{-- ROW 2: TECHNICAL FILTERS --}}
        <div class="flex flex-wrap items-center gap-3 pt-4 border-t border-zinc-100 dark:border-zinc-800">

            {{-- Rate Range Group --}}
            <div
                class="flex items-center gap-1 bg-zinc-50 dark:bg-white/5 p-1 rounded-xl border border-zinc-200 dark:border-zinc-700">
                <flux:input type="number" step="0.01" placeholder="Min $" wire:model.live.debounce.400ms="min_rate"
                    class="w-[90px] border-none shadow-none !bg-transparent focus:ring-0" />

                <span class="text-zinc-300 text-xs">-</span>

                <flux:input type="number" step="0.01" placeholder="Max $" wire:model.live.debounce.400ms="max_rate"
                    class="w-[90px] border-none shadow-none !bg-transparent focus:ring-0" />

                <div class="w-[1px] h-4 bg-zinc-200 dark:bg-zinc-700 mx-2"></div>

                <flux:select wire:model.live="rate_type" class="w-[120px] !border-none !shadow-none !bg-transparent">
                    <option value="">All Rates</option>
                    @foreach (App\Enums\RateType::cases() as $type)
                        <option value="{{ $type->value }}">{{ $type->label() }}</option>
                    @endforeach
                </flux:select>
            </div>

            {{-- Trailer Dropdown --}}
            <flux:dropdown>
                <flux:button icon:trailing="chevron-down" variant="subtle">
                    Trailers
                    {{-- Use is_array check to be bulletproof --}}
                    @if (is_array($trailerFilters) && count($trailerFilters) > 0)
                        <span class="ml-1 px-1.5 py-0.5 rounded-full bg-lime-100 text-lime-700 text-[10px] font-bold">
                            {{ count($trailerFilters) }}
                        </span>
                    @endif
                </flux:button>

                <flux:menu class="min-w-[200px] max-h-[300px] overflow-y-auto">
                    @foreach (\App\Enums\TrailerType::cases() as $type)
                        {{-- Use the new PHP method instead of $toggle --}}
                        <flux:menu.item wire:click="toggleTrailer('{{ $type->value }}')">
                            <span class="flex-1 text-sm">{{ $type->label() }}</span>

                            @if (is_array($trailerFilters) && in_array($type->value, $trailerFilters))
                                <flux:icon name="check" class="size-4 text-lime-600" />
                            @endif
                        </flux:menu.item>
                    @endforeach
                </flux:menu>
            </flux:dropdown>

            {{-- Reset Button --}}
            @if (
                $search ||
                    $carrier_search ||
                    $available_date ||
                    $min_rate ||
                    $max_rate ||
                    $rate_type ||
                    $status ||
                    $vehicle_status ||
                    !empty($trailerFilters))
                <flux:button variant="ghost" color="red" icon="x-mark" wire:click="clearFilters" class="ml-auto">
                    Reset Filters
                </flux:button>
            @endif
        </div>

    </div>

    <div class="flex flex-wrap gap-2 mb-6">
        {{-- Search Text Chip --}}
        @if ($search)
            <x-filter-chip label="Search: {{ $search }}" wire:click="removeFilter('search')" />
        @endif

        {{-- Rate Range Chip --}}
        @if ($min_rate || $max_rate)
            <x-filter-chip label="Rate: ${{ $min_rate ?? 0 }} - {{ $max_rate ? '$' . $max_rate : '∞' }}"
                wire:click="removeFilter('min_rate'); removeFilter('max_rate');" />
        @endif

        {{-- Trailer Chips (Individual) --}}
        @foreach ($trailerFilters as $value)
            @php $trailerEnum = \App\Enums\TrailerType::tryFrom($value); @endphp
            <x-filter-chip label="{{ $trailerEnum ? $trailerEnum->label() : $value }}"
                wire:click="removeFilter('trailerFilters', '{{ $value }}')" />
        @endforeach

        {{-- Date Chip --}}
        @if ($available_date)
            <x-filter-chip label="Date: {{ $available_date }}" wire:click="removeFilter('available_date')" />
        @endif
    </div>

    {{-- 3. RESULTS GRID --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse($this->lanes as $lane)
            <div
                class="bg-white dark:bg-slate-900 rounded-3xl border border-zinc-200 dark:border-zinc-800 overflow-hidden hover:border-lime-500 transition-all duration-300 group shadow-sm hover:shadow-xl">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        @php
                            // Check if this specific trailer type is currently being filtered
                            $isSelected = in_array($lane->trailer->value, (array) $this->trailerFilters);
                        @endphp
                        <div
                            class="flex flex-col items-center justify-center p-6 rounded-3xl border transition-all duration-300 group
    {{ $isSelected
        ? 'bg-lime-50 border-lime-400 dark:bg-lime-900/20 dark:border-lime-500 shadow-sm ring-1 ring-lime-400'
        : 'bg-zinc-50 dark:bg-zinc-800/50 border-zinc-100 dark:border-zinc-800 group-hover:bg-lime-50 group-hover:border-lime-100' }}">
                            <div class="relative">
                                <x-graphic :name="$lane->trailer->iconName()"
                                    class="size-24 text-zinc-600 dark:text-zinc-400 group-hover:text-lime-600 group-hover:scale-110 transition-transform duration-500 {{ $isSelected ? 'text-lime-600 scale-110' : 'text-zinc-600 dark:text-zinc-400 group-hover:text-lime-600 group-hover:scale-110' }}" />
                                {{-- Optional: A small checkmark badge if selected --}}
                                @if ($isSelected)
                                    <div
                                        class="absolute -top-2 -right-2 bg-lime-600 text-white rounded-full p-1 shadow-md">
                                        <flux:icon name="check" variant="mini" class="size-3" />
                                    </div>
                                @endif
                            </div>
                            {{-- The Trailer Label --}}
                            <div class="mt-3 text-center">
                                <span
                                    class="text-[11px] font-black uppercase tracking-[0.15em] transition-colors
            {{ $isSelected ? 'text-lime-700 dark:text-lime-400' : 'text-zinc-400 dark:text-zinc-500 group-hover:text-lime-700' }}">
                                    {{ $lane->trailer->label() }}
                                </span>
                            </div>
                        </div>
                        <div class="text-right">
                            {{-- Status Badge (Interactive) --}}
                            @auth
                                @if (auth()->user()->hasAnyRole(['admin', 'superadmin', 'operations logistics associate', 'procurement executive associate']) ||
                                        auth()->user()->id === $lane->creator_id ||
                                        auth()->user()->id === $lane->carrier_id)
                                    <div class="mb-2">
                                        <button type="button"
                                            wire:click="setFilter('status', '{{ $lane->status->value }}')"
                                            class="group/status flex items-center gap-1.5 px-2 py-1 rounded-md text-[9px] font-black uppercase tracking-widest border shadow-sm transition-all active:scale-95 hover:brightness-95 {{ $status === $lane->status->value ? 'ring-2 ring-zinc-400 ring-offset-1' : '' }}"
                                            style="border-color: {{ $lane->status->color() }}40; background-color: {{ $lane->status->color() }}10; color: {{ $lane->status->color() }}"
                                            title="Click to filter by {{ $lane->status->label() }}">
                                            <span class="size-1 rounded-full"
                                                style="background-color: {{ $lane->status->color() }}"></span>

                                            {{ $lane->status->label() }}

                                            @if ($status === $lane->status->value)
                                                <flux:icon name="x-mark" variant="mini"
                                                    class="size-2.5 opacity-60 group-hover/status:opacity-100" />
                                            @endif
                                        </button>
                                    </div>
                                @endif
                            @endauth
                            <span class="text-2xl font-black text-zinc-900 dark:text-white">
                                ${{ number_format((float) $lane->rate, 2) }}
                            </span>
                            <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-tighter">
                                {{ $lane->rate_type?->label() }}</p>
                        </div>
                    </div>

                    <div class="space-y-1 mb-4">
                        <h3 class="text-lg font-bold flex items-center gap-2 text-zinc-900 dark:text-zinc-100">
                            {{-- Legacy Fallback Logic --}}
                            {!! $this->highlight($lane->cityfrom ?: $lane->location, $this->search) !!}
                            <flux:icon name="arrow-right" class="size-4 text-zinc-300" />
                            {!! $this->highlight($lane->cityto ?: $lane->destination, $this->search) !!}
                        </h3>
                        <p class="text-[11px] text-zinc-500 font-medium lowercase italic">
                            @if ($lane->countryfrom)
                                {!! $this->highlight($lane->countryfrom, $this->search) !!} to {{ $lane->countryto }}
                            @else
                                legacy route mapping
                            @endif
                        </p>
                    </div>

                    <div class="mt-4 pt-4 border-t border-zinc-100 dark:border-zinc-800 space-y-2">
                        @php
                            $user = auth()->user();

                            // PRIVILEGE CHECK:
                            // 1. Is the user the Owner?
                            $isOwner = $user && ($user->id === $lane->creator_id || $user->id === $lane->carrier_id);
                            // 2. Is the user an Admin?
                            $isAdmin = $user && $user->hasAnyRole(['admin', 'superadmin']);
                            // 3. Is the user Staff in the correct territory? (The "getVisibleLanes" pass)
                            $isStaffWithAccess =
                                $user &&
                                $user->hasAnyRole([
                                    'operations logistics associate',
                                    'procurement executive associate',
                                ]) &&
                                in_array($lane->creator_id, $this->getTerritoryIds());

                            $hasFullAccess = $isOwner || $isAdmin || $isStaffWithAccess;
                        @endphp

                        {{-- Carrier Info --}}
                        <div class="flex items-center gap-2">
                            <flux:icon name="truck" variant="mini" class="size-3 text-zinc-400" />
                            <span class="text-[10px] text-zinc-500 font-medium">
                                Carrier:
                                @if ($hasFullAccess)
                                    {{-- FULL ACCESS: Show "Me" or Name + ID --}}
                                    @if ($user && $user->id === $lane->carrier_id)
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full bg-lime-500 text-white text-[9px] font-black uppercase tracking-widest shadow-sm">Me</span>
                                    @else
                                        <a href="{{ route('users.show', $lane->carrier->slug ?? 'deleted-user') }}"
                                            class="text-zinc-900 dark:text-zinc-200 font-bold hover:text-lime-600 underline decoration-zinc-200 underline-offset-2">
                                            {!! $this->highlight(
                                                $lane->carrier?->organisation ?? ($lane->carrier?->contact_person ?? 'Private Carrier'),
                                                $this->carrier_search,
                                            ) !!}
                                        </a>
                                    @endif

                                    {{-- Show ID alongside name for those with full access --}}
                                    @if ($lane->identification_number)
                                        <span
                                            class="ml-1 px-1.5 py-0.5 bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 rounded text-[8px] uppercase font-black">
                                            ID: {!! $this->highlight($lane->identification_number, $this->carrier_search) !!}
                                        </span>
                                    @endif
                                @else
                                    {{-- RESTRICTED ACCESS: Show identification_number from the lanes table --}}
                                    <span
                                        class="px-1.5 py-0.5 bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 border border-amber-100 dark:border-amber-800/50 rounded text-[9px] font-bold">
                                        Vehicle ID: {{ $lane->identification_number ?? 'N/A' }}
                                    </span>
                                @endif
                            </span>
                        </div>

                        {{-- Agent/Creator Info --}}
                        @if ($lane->creator_id !== $lane->carrier_id)
                            <div class="flex items-center gap-2">
                                <flux:icon name="pencil-square" variant="mini" class="size-3 text-zinc-400" />
                                <span class="text-[10px] text-zinc-400 italic">
                                    Agent:
                                    @if ($hasFullAccess)
                                        @if ($user && $user->id === $lane->creator_id)
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-full bg-blue-500 text-white text-[9px] font-black uppercase tracking-widest shadow-sm">Me</span>
                                        @else
                                            <a href="{{ route('users.show', $lane->createdBy->slug ?? 'deleted-user') }}"
                                                class="hover:text-lime-600 font-medium transition-colors">
                                                {!! $this->highlight($lane->createdBy?->contact_person ?? 'Staff', $this->carrier_search) !!}
                                            </a>
                                        @endif
                                    @else
                                        <span class="text-zinc-300">Staff Managed</span>
                                    @endif
                                </span>
                            </div>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-4 py-4 mt-4 border-t border-zinc-100 dark:border-zinc-800">
                        <div>
                            <p class="text-[10px] font-bold text-zinc-400 uppercase">Available</p>
                            <p class="text-xs font-bold text-zinc-800 dark:text-zinc-200">
                                {{ $lane->availability_date->diffForHumans() }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-bold text-zinc-400 uppercase">Capacity</p>
                            <p class="text-sm font-bold text-zinc-800 dark:text-zinc-200">
                                {!! $this->highlight($lane->capacity, $this->search) !!} {{ $lane->capacity_unit?->value }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="px-6 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-t border-zinc-100 dark:border-zinc-800 flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <div
                            class="size-2 rounded-full {{ $lane->status->value === 'published' ? 'bg-lime-500 animate-pulse' : 'bg-zinc-300' }}">
                        </div>
                        <span class="text-xs font-bold text-zinc-700 dark:text-zinc-300">
                            {{ $lane->vehicle_status->label() }}
                        </span>
                    </div>
                    <flux:button variant="filled" size="sm" class="rounded-xl"
                        href="{{ route('lanes.show', $lane->uuid) }}">
                        View Details
                    </flux:button>
                </div>
            </div>
        @empty
            <div
                class="col-span-full py-20 text-center bg-zinc-50 dark:bg-zinc-900/50 rounded-3xl border-2 border-dashed border-zinc-200 dark:border-zinc-800">
                <flux:icon name="magnifying-glass" class="size-10 text-zinc-300 mx-auto mb-4" />
                <p class="text-zinc-500 font-bold">No results found.</p>
                <p class="text-xs text-zinc-400">Try adjusting your filters or search terms.</p>
                <flux:button variant="ghost" class="mt-4" wire:click="clearFilters">Clear All Filters</flux:button>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $this->lanes->links() }}
    </div>
</div>
