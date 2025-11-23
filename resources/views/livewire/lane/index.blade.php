<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use App\Models\Lane;

new class extends Component {
    #[Locked]
    public $freightId;

    #[Computed]
    public function getLanes()
    {
        return Lane::orderBy('updated_at')
            ->with(['contacts', 'createdBy'])
            ->get();
    }

    public function deleteLane($laneId)
    {
        $this->laneId = $laneId;
        $lane = Lane::findOrFail($this->laneId);
        $lane->delete();
        session()->flash('message', 'The VEHICLE was successfully deleted');
    }
}; ?>

<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Available Lanes</h1>
            <p class="text-gray-600 dark:text-gray-400">Browse and bid on available shipping lanes</p>
        </div>
<div class="flex flex-wrap gap-3">
    <!-- Status Filter Dropdown -->
    <flux:dropdown class="flex-grow">
        <flux:button icon:trailing="chevron-down" variant="outline" class="border-gray-300 dark:border-slate-600 w-full">
            Status
        </flux:button>
        <flux:menu>
            <flux:menu.checkbox keep-open checked>Active</flux:menu.checkbox>
            <flux:menu.checkbox keep-open>Urgent</flux:menu.checkbox>
            <flux:menu.checkbox keep-open>Expiring Soon</flux:menu.checkbox>
            <flux:menu.checkbox keep-open>Heavy Load</flux:menu.checkbox>
            <flux:menu.separator />
            <flux:menu.item variant="danger">Clear Filters</flux:menu.item>
        </flux:menu>
    </flux:dropdown>

    <!-- Vehicle Type Filter Dropdown -->
    <flux:dropdown class="flex-grow">
        <flux:button icon:trailing="chevron-down" variant="outline" class="border-gray-300 dark:border-slate-600 w-full">
            Vehicle Type
        </flux:button>
        <flux:menu>
            <flux:menu.checkbox keep-open checked>Dry Van</flux:menu.checkbox>
            <flux:menu.checkbox keep-open checked>Refrigerated</flux:menu.checkbox>
            <flux:menu.checkbox keep-open>Flatbed</flux:menu.checkbox>
            <flux:menu.checkbox keep-open>Tanker</flux:menu.checkbox>
            <flux:menu.checkbox keep-open>Container</flux:menu.checkbox>
            <flux:menu.separator />
            <flux:menu.item variant="danger">Clear Filters</flux:menu.item>
        </flux:menu>
    </flux:dropdown>

    <!-- Route Type Filter Dropdown -->
    <flux:dropdown class="flex-grow">
        <flux:button icon:trailing="chevron-down" variant="outline" class="border-gray-300 dark:border-slate-600 w-full">
            Route Type
        </flux:button>
        <flux:menu>
            <flux:menu.checkbox keep-open checked>Regional</flux:menu.checkbox>
            <flux:menu.checkbox keep-open>National</flux:menu.checkbox>
            <flux:menu.checkbox keep-open>International</flux:menu.checkbox>
            <flux:menu.checkbox keep-open>Cross-border</flux:menu.checkbox>
            <flux:menu.separator />
            <flux:menu.item variant="danger">Clear Filters</flux:menu.item>
        </flux:menu>
    </flux:dropdown>

    <!-- Search Input -->
    <div class="relative flex-grow w-full">
        <flux:icon name="magnifying-glass" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2" />
        <input 
            type="text" 
            placeholder="Search lanes..." 
            class="pl-10 pr-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-lime-500 focus:border-lime-500 dark:focus:ring-lime-600 dark:focus:border-lime-600 w-full sm:w-64"
        />
    </div>
</div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-lg p-4 border border-gray-200 dark:border-slate-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Lanes</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">24</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <flux:icon name="map" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-lg p-4 border border-gray-200 dark:border-slate-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Your Bids</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">8</p>
                </div>
                <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                    <flux:icon name="scale" class="w-5 h-5 text-green-600 dark:text-green-400" />
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-lg p-4 border border-gray-200 dark:border-slate-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Winning Bids</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">5</p>
                </div>
                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                    <flux:icon name="trophy" class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-lg p-4 border border-gray-200 dark:border-slate-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Expiring Soon</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">3</p>
                </div>
                <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900 rounded-lg flex items-center justify-center">
                    <flux:icon name="clock" class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                </div>
            </div>
        </div>
    </div>

    <!-- Active Filters Bar -->
    <div class="flex items-center gap-2 p-3 bg-gray-50 dark:bg-slate-700 rounded-lg">
        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Active filters:</span>
        <div class="flex flex-wrap gap-2">
            <flux:badge color="blue" size="sm" class="flex items-center gap-1">
                Dry Van
                <button class="hover:text-blue-800">
                    <flux:icon name="x-mark" class="w-3 h-3" />
                </button>
            </flux:badge>
            <flux:badge color="blue" size="sm" class="flex items-center gap-1">
                Refrigerated
                <button class="hover:text-blue-800">
                    <flux:icon name="x-mark" class="w-3 h-3" />
                </button>
            </flux:badge>
            <flux:badge color="green" size="sm" class="flex items-center gap-1">
                Active
                <button class="hover:text-green-800">
                    <flux:icon name="x-mark" class="w-3 h-3" />
                </button>
            </flux:badge>
            <flux:badge color="purple" size="sm" class="flex items-center gap-1">
                Regional
                <button class="hover:text-purple-800">
                    <flux:icon name="x-mark" class="w-3 h-3" />
                </button>
            </flux:badge>
            <button class="text-sm text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 font-medium">
                Clear all
            </button>
        </div>
    </div>

    <!-- Lanes Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
        <!-- Lane Card 1 -->
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 shadow-sm hover:shadow-lg transition-all duration-300 group">
            <!-- Header -->
            <div class="p-6 pb-4">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white text-lg">Nairobi → Mombasa</h3>
                        <p class="text-gray-600 dark:text-gray-400 text-sm">Dry Van • 24 tons</p>
                    </div>
                    <flux:badge color="green" size="sm">Active</flux:badge>
                </div>
                
                <!-- Route Visualization -->
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <flux:icon name="map-pin" class="w-4 h-4 text-green-500" />
                        <span class="text-sm font-medium text-gray-900 dark:text-white">Nairobi</span>
                    </div>
                    <div class="flex-1 mx-4 border-t border-dashed border-gray-300 dark:border-slate-600"></div>
                    <div class="flex items-center gap-2">
                        <flux:icon name="flag" class="w-4 h-4 text-orange-500" />
                        <span class="text-sm font-medium text-gray-900 dark:text-white">Mombasa</span>
                    </div>
                </div>
            </div>

            <!-- Lane Details -->
            <div class="px-6 space-y-3">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Distance</span>
                    <span class="font-medium text-gray-900 dark:text-white">485 km</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Est. Duration</span>
                    <span class="font-medium text-gray-900 dark:text-white">8-10 hours</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Load Type</span>
                    <span class="font-medium text-gray-900 dark:text-white">General Cargo</span>
                </div>
            </div>

            <!-- Bidding Section -->
            <div class="p-6 pt-4 border-t border-gray-100 dark:border-slate-700 mt-4">
                <!-- Current Bid Info -->
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-500">Current Bid Range</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">$1,200 - $1,800</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-500 dark:text-gray-500">Bids Placed</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">12 carriers</p>
                    </div>
                </div>

                <!-- Bid Action -->
                <div class="space-y-3">
                    <flux:button 
                        class="w-full bg-lime-500 hover:bg-lime-600 text-white"
                        wire:click="openBidModal('lane-001')"
                    >
                        <flux:icon name="scale" class="w-4 h-4 mr-2" />
                        Place Bid
                    </flux:button>
                    
                    <!-- Quick Bid Options -->
                    <div class="flex gap-2">
                        <button class="flex-1 px-3 py-2 text-xs border border-gray-300 dark:border-slate-600 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
                            $1,250
                        </button>
                        <button class="flex-1 px-3 py-2 text-xs border border-gray-300 dark:border-slate-600 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
                            $1,500
                        </button>
                        <button class="flex-1 px-3 py-2 text-xs border border-gray-300 dark:border-slate-600 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
                            $1,750
                        </button>
                    </div>
                </div>

                <!-- Time Remaining -->
                <div class="mt-3 flex items-center justify-between text-xs text-gray-500 dark:text-gray-500">
                    <div class="flex items-center gap-1">
                        <flux:icon name="clock" class="w-3 h-3" />
                        <span>Closes in 2 days</span>
                    </div>
                    <button class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                        View Details
                    </button>
                </div>
            </div>
        </div>

        <!-- Additional Lane Cards would continue here... -->
    </div>

    <!-- Load More -->
    <div class="flex justify-center">
        <flux:button variant="outline" class="border-lime-300 text-lime-600 hover:bg-lime-50 dark:border-lime-600 dark:text-lime-400 dark:hover:bg-lime-900/20">
            <flux:icon name="arrow-down" class="w-4 h-4 mr-2" />
            Load More Lanes
        </flux:button>
    </div>
</div>
