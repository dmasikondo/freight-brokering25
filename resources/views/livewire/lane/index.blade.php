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

new class extends Component {
    use WithPagination;
    // The public property to hold the search term from the input field.
    // #[Url] makes the search term appear in the URL.
    #[Url]
    public string $search = '';

    public $statusFilters = [];
    public $trailerFilters = [];
    public $routeFilters = [];
    public $trailertype = [];

    #[Locked]
    public $laneId;

    #[Computed]
    // public function getLanes()
    // {
    //     return Lane::orderBy('updated_at')
    //         ->with(['contacts', 'createdBy'])
    //         ->get();
    // }
    protected LaneService $laneService;
    #[Computed]
    public function lanes()
    {
        return Lane::query()
            ->where(function ($query) {
                $query->whereIn('vehicle_status', [VehiclePositionStatus::NOT_CONTRACTED, VehiclePositionStatus::INAPPLICABLE])->whereIn('status', [LaneStatus::PUBLISHED, LaneStatus::EXPIRED]);
            })
            ->when($this->search, function ($query, $search) {
                $query->whereAny(['destination', 'location', 'cityfrom', 'cityto', 'countryfrom', 'countryto', 'trailer', 'capacity'], 'LIKE', "%{$search}%");
            })
            ->when(!empty($this->trailerFilters), function ($query) {
                $query->whereIn('trailer', $this->trailerFilters);
            })
            ->latest()
            ->paginate(20);
    }

    #[Comupted]
    public function availableTrailers()
    {
        // Get distinct trailers from the Lane model
        return Lane::distinct()->pluck('trailer')->toArray();
    }

    /**
     * A helper method to highlight the search term in a given string.
     * @param string $text The text to be highlighted.
     * @param string $search The search term.
     * @return \Illuminate\Support\Stringable
     */
    private function highlight(string $text = null, string $search = null)
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

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilters = [];
        $this->trailerFilters = [];
        $this->routeFilters = [];
        $this->resetPage();
    }

    public function updated($property)
    {
        if (in_array($property, ['search', 'statusFilters', 'trailerFilters', 'routeFilters'])) {
            $this->resetPage();
        }
    }
    public function deleteLane($laneId)
    {
        $this->laneId = $laneId;
        $lane = Lane::findOrFail($this->laneId);
        $lane->delete();
        session()->flash('message', 'The VEHICLE was successfully deleted');
    }

    public function mount()
    {
        $this->laneService = app(LaneService::class);
    }
}; ?>


<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Available Lanes</h1>
            <p class="text-gray-600 dark:text-gray-400">Browse and bid on available shipping lanes (vehicles)</p>
        </div>

        <div class="flex flex-wrap gap-3">

            <div>
                <flux:dropdown>
                    <flux:button icon:trailing="chevron-down">Vehicle Type</flux:button>
                    <flux:menu>

                        <flux:menu.radio.group wire:model.live="search">
                            @foreach($this->availableTrailers() as $trailer)
                                <flux:menu.radio>{{ $trailer->label() }}</flux:menu.radio>
                            @endforeach
                        </flux:menu.radio.group>
                    </flux:menu>
                </flux:dropdown>



            </div>

            <!-- Vehicle Type Filter Dropdown -->
            <div>
                <flux:text class="mt-2">
                Search Vehicles by location, destination, capacity, type
                </flux:text>
            </div>

           

            <!-- Search Input -->
            <div class="relative flex-grow w-full">
                <flux:icon name="magnifying-glass"
                    class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2" />
                <input type="text" placeholder="Search lanes..." wire:model.live.debounce.300ms="search"
                    class="pl-10 pr-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-lime-500 focus:border-lime-500 dark:focus:ring-lime-600 dark:focus:border-lime-600 w-full " />
            </div>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-lg p-4 border border-gray-200 dark:border-slate-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Lanes</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->lanes->total() }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <flux:icon name="map" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-lg p-4 border border-gray-200 dark:border-slate-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Published</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $this->lanes->where('status', App\Enums\LaneStatus::PUBLISHED)->count() }}
                    </p>
                </div>
                <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                    <flux:icon name="eye" class="w-5 h-5 text-green-600 dark:text-green-400" />
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-lg p-4 border border-gray-200 dark:border-slate-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Expired</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $this->lanes->where('status', App\Enums\LaneStatus::EXPIRED)->count() }}
                    </p>
                </div>
                <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900 rounded-lg flex items-center justify-center">
                    <flux:icon name="clock" class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-lg p-4 border border-gray-200 dark:border-slate-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Available Types</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $this->lanes->pluck('trailer')->unique()->count() }}
                    </p>
                </div>
                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                    <flux:icon name="truck" class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                </div>
            </div>
        </div>
    </div>

    <!-- Active Filters Bar -->
    @if ($search || !empty($statusFilters) || !empty($trailerFilters) || !empty($routeFilters))
        <div class="flex items-center gap-2 p-3 bg-gray-50 dark:bg-slate-700 rounded-lg">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Active filters:</span>
            <div class="flex flex-wrap gap-2">
                @if ($search)
                    <flux:badge color="blue" size="sm" class="flex items-center gap-1">
                        Search: "{{ $search }}"
                        <button wire:click="$set('search', '')" class="hover:text-blue-800">
                            <flux:icon name="x-mark" class="w-3 h-3" />
                        </button>
                    </flux:badge>
                @endif

                @foreach ($statusFilters as $filter)
                    <flux:badge color="green" size="sm" class="flex items-center gap-1">
                        {{ ucfirst($filter) }}
                        <button wire:click="$remove('statusFilters', '{{ $filter }}')"
                            class="hover:text-green-800">
                            <flux:icon name="x-mark" class="w-3 h-3" />
                        </button>
                    </flux:badge>
                @endforeach



                @foreach ($routeFilters as $filter)
                    <flux:badge color="purple" size="sm" class="flex items-center gap-1">
                        {{ ucfirst($filter) }}
                        <button wire:click="$remove('routeFilters', '{{ $filter }}')"
                            class="hover:text-purple-800">
                            <flux:icon name="x-mark" class="w-3 h-3" />
                        </button>
                    </flux:badge>
                @endforeach

                <button wire:click="clearFilters"
                    class="text-sm text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 font-medium">
                    Clear all
                </button>
            </div>
        </div>
    @endif

    <!-- Lanes Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse($this->lanes as $lane)
            <div
                class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 shadow-sm hover:shadow-lg transition-all duration-300 group">
                <!-- Header -->
                <div class="p-6 pb-4">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white text-lg">
                            @if ($lane->countryfrom && $lane->cityfrom && $lane->countryto && $lane->cityto)
                                {!! $this->highlight($lane->cityfrom, $this->search) !!}
                                 → {!! $this->highlight($lane->cityto, $this->search) !!}   
                            @else
                                {!! $this->highlight($lane->location, $this->search) !!}
                                 → {!! $this->highlight($lane->destination, $this->search) !!}  
                            @endif
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400 text-sm">
                                {!! $this->highlight($lane->trailer->label(), $this->search) !!} •
                                {!! $this->highlight($lane->capacity ?? 'N/A', $this->search) !!}
                            </p>
                        </div>
                        <flux:badge :color="$lane->status->color()" size="sm">
                            {{ $lane->status->label() }}
                        </flux:badge>
                    </div>

                    <!-- Route Visualization -->
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <flux:icon name="map-pin" class="w-4 h-4 text-green-500" />
                            <span
                                class="text-sm font-medium text-gray-900 dark:text-white">
                            @if ($lane->countryfrom && $lane->cityfrom && $lane->countryto && $lane->cityto)
                                {!! $this->highlight($lane->cityfrom, $this->search) !!}  
                            @else
                                {!! $this->highlight($lane->location, $this->search) !!}
                            @endif 
                            </span>
                        </div>
                        <div class="flex-1 mx-4 border-t border-dashed border-gray-300 dark:border-slate-600"></div>
                        <div class="flex items-center gap-2">
                            <flux:icon name="flag" class="w-4 h-4 text-orange-500" />
                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                            @if ($lane->countryfrom && $lane->cityfrom && $lane->countryto && $lane->cityto)
                                {!! $this->highlight($lane->cityto, $this->search) !!}  
                            @else
                                {!! $this->highlight($lane->destination, $this->search) !!}
                            @endif 
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Lane Details -->
                <div class="px-6 space-y-3">
                @if ($lane->countryfrom && $lane->cityfrom && $lane->countryto && $lane->cityto)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">From Country</span>
                        <span class="font-medium text-gray-900 dark:text-white">{!! $this->highlight($lane->countryfrom, $this->search) !!}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">To Country</span>
                        <span class="font-medium text-gray-900 dark:text-white">{!! $this->highlight($lane->countryto, $this->search) !!}</span>
                    </div>
                @endif
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Rate</span>
                        <span class="font-medium text-gray-900 dark:text-white">
                            @if ($lane->rate)
                                ${{ $lane->rate }}
                            @else
                                N/A
                            @endif
                        </span>
                    </div>
                    @if ($lane->availability_date)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">Available Date</span>
                            <span
                                class="font-medium text-gray-900 dark:text-white">{{ $lane->availability_date->format('M d, Y') }}</span>
                        </div>
                    @endif
                </div>

                <!-- Bidding Section -->
                <div class="p-6 pt-4 border-t border-gray-100 dark:border-slate-700 mt-4">
                    <!-- Vehicle Status Info -->
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-500">Vehicle Status</p>
                            <p class="text-sm font-medium text-gray-900 dark:text-white capitalize">
                                {{ str_replace('_', ' ', $lane->vehicle_status->label()) }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-500 dark:text-gray-500">Trailer Type</p>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{!! $this->highlight($lane->trailer->label(), $this->search) !!}
                            </p>
                        </div>
                    </div>

                    <!-- Bid Action -->
                    <div class="space-y-3">
                        <flux:button class="w-full bg-lime-500 hover:bg-lime-600 text-white"
                            wire:click="openBidModal({{ $lane->id }})">
                            <flux:icon name="scale" class="w-4 h-4 mr-2" />
                            Place Bid
                        </flux:button>

                        <!-- Quick Bid Options -->
                        <div class="flex gap-2">
                            <button
                                class="flex-1 px-3 py-2 text-xs border border-gray-300 dark:border-slate-600 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
                                Quick Bid 1
                            </button>
                            <button
                                class="flex-1 px-3 py-2 text-xs border border-gray-300 dark:border-slate-600 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
                                Quick Bid 2
                            </button>
                            <button
                                class="flex-1 px-3 py-2 text-xs border border-gray-300 dark:border-slate-600 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
                                Quick Bid 3
                            </button>
                        </div>
                    </div>

                    <!-- Additional Info -->
                    <div class="mt-3 flex items-center justify-between text-xs text-gray-500 dark:text-gray-500">
                        <div class="flex items-center gap-1">
                            <flux:icon name="calendar" class="w-3 h-3" />
                            <span>Created {{ $lane->created_at->diffForHumans() }}</span>
                        </div>
                        <button class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                            View Details
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <flux:icon name="map" class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No lanes found</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-4">No available lanes match your current filters.</p>
                <flux:button variant="outline" wire:click="clearFilters"
                    class="border-lime-300 text-lime-600 hover:bg-lime-50 dark:border-lime-600 dark:text-lime-400 dark:hover:bg-lime-900/20">
                    Clear Filters
                </flux:button>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if ($this->lanes->hasPages())
        <div class="flex justify-center">
            {{ $this->lanes->links() }}
        </div>
    @endif

    <!-- Load More (Alternative to pagination) -->
    @if ($this->lanes->hasMorePages())
        <div class="flex justify-center">
            <flux:button variant="outline"
                class="border-lime-300 text-lime-600 hover:bg-lime-50 dark:border-lime-600 dark:text-lime-400 dark:hover:bg-lime-900/20"
                wire:click="loadMore">
                <flux:icon name="arrow-down" class="w-4 h-4 mr-2" />
                Load More Lanes
            </flux:button>
        </div>
    @endif
</div>
