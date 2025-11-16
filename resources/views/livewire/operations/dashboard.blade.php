<?php

use App\Models\User;
use App\Models\Freight;
use App\Models\Buslocation;
use App\Models\Territory;
use Illuminate\Support\Collection;
use Livewire\Volt\Component;

new class extends Component {
    public User $user;    
    public Collection $territories;
    public Collection $shippers;
    public Collection $carriers;
    public Collection $marketingAssociates;
    public Collection $procurementAssociates;
    public Collection $activeShipments;
    public Collection $recentShippers;
    public Collection $recentCarriers;
    public Collection $recentMarketingAssociates;
    public Collection $recentProcurementAssociates;
    
    public int $totalShippers = 0;
    public int $totalCarriers = 0;
    public int $totalMarketingAssociates = 0;
    public int $totalProcurementAssociates = 0;
    public int $activeShipmentsCount = 0;
    
    private function loadTerritories(): void
    {
        $this->territories = $this->user->territories()->with(['zimbabweCities', 'countries'])->get();
    }
    
    private function loadShippers(): void
    {
        // Get shippers (users with shipper role) in user's territories
        $shipperUsers = User::whereHas('roles', function ($query) {
            $query->where('name', 'shipper');
        })->get();
        
        $this->shippers = $shipperUsers->filter(function ($shipper) {
            return $this->isUserInTerritory($shipper);
        });
        
        $this->totalShippers = $this->shippers->count();
    }
    
    private function loadCarriers(): void
    {
        // Get carriers (users with carrier role) in user's territories
        $carrierUsers = User::whereHas('roles', function ($query) {
            $query->where('name', 'carrier');
        })->get();
        
        $this->carriers = $carrierUsers->filter(function ($carrier) {
            return $this->isUserInTerritory($carrier);
        });
        
        $this->totalCarriers = $this->carriers->count();
    }
    
    private function loadMarketingAssociates(): void
    {
        // Get marketing logistics associates in user's territories
        $marketingUsers = User::whereHas('roles', function ($query) {
            $query->where('name', 'marketing logistics associate');
        })->get();
        
        $this->marketingAssociates = $marketingUsers->filter(function ($marketingUser) {
            return $this->isUserInTerritory($marketingUser);
        });
        
        $this->totalMarketingAssociates = $this->marketingAssociates->count();
    }
    
    private function loadProcurementAssociates(): void
    {
        // Get procurement logistics associates in user's territories
        $procurementUsers = User::whereHas('roles', function ($query) {
            $query->where('name', 'procurement logistics associate');
        })->get();
        
        $this->procurementAssociates = $procurementUsers->filter(function ($procurementUser) {
            return $this->isUserInTerritory($procurementUser);
        });
        
        $this->totalProcurementAssociates = $this->procurementAssociates->count();
    }
    
    private function loadActiveShipments(): void
    {
        // Get territories IDs for the current user
        $territoryIds = $this->territories->pluck('id');
        
        // Get users in these territories (including the current user)
        $territoryUsers = User::whereHas('territories', function ($query) use ($territoryIds) {
            $query->whereIn('territories.id', $territoryIds);
        })->pluck('id');
        
        // Add current user ID to ensure their shipments are included
        $territoryUsers->push($this->user->id);
        
        $this->activeShipments = Freight::whereIn('creator_id', $territoryUsers)
            ->where('status', 'published')
            ->whereIn('shipment_status', ['loading', 'in transit'])
            ->with(['createdBy'])
            ->get();
            
        $this->activeShipmentsCount = $this->activeShipments->count();
    }
    
    private function loadRecentShippers(): void
    {
        $this->recentShippers = $this->shippers
            ->sortByDesc('created_at')
            ->take(3);
    }
    
    private function loadRecentCarriers(): void
    {
        $this->recentCarriers = $this->carriers
            ->sortByDesc('created_at')
            ->take(3);
    }
    
    private function loadRecentMarketingAssociates(): void
    {
        $this->recentMarketingAssociates = $this->marketingAssociates
            ->sortByDesc('created_at')
            ->take(3);
    }
    
    private function loadRecentProcurementAssociates(): void
    {
        $this->recentProcurementAssociates = $this->procurementAssociates
            ->sortByDesc('created_at')
            ->take(3);
    }
    
    private function isUserInTerritory(User $targetUser): bool
    {
        // Check if user is registered by current user (direct relationship)
        if ($targetUser->createdBy && $targetUser->createdBy->id === $this->user->id) {
            return true;
        }
        
        // Get target user's business locations
        $targetUserLocations = $targetUser->buslocation;
        
        if ($targetUserLocations->isEmpty()) {
            return false;
        }
        
        // Check if any of the target user's locations match any location in current user's territories
        foreach ($this->territories as $territory) {
            // Check Zimbabwe cities
            $territoryCities = $territory->zimbabweCities->pluck('name');
            $territoryCountries = $territory->countries->pluck('name');
            
            foreach ($targetUserLocations as $location) {
                // Check if location city matches territory cities
                if ($location->city && $territoryCities->contains($location->city)) {
                    return true;
                }
                
                // Check if location country matches territory countries
                if ($location->country && $territoryCountries->contains($location->country)) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    public function getTerritoryName(): string
    {
        if ($this->territories->isEmpty()) {
            return 'No Territory Assigned';
        }
        
        return $this->territories->first()->name ?? 'Unnamed Territory';
    }
    
    public function getShipperIncrease(): int
    {
        // Calculate shipper increase for current month
        $currentMonthStart = now()->startOfMonth();
        return $this->shippers->where('created_at', '>=', $currentMonthStart)->count();
    }
    
    public function getCarrierIncrease(): int
    {
        // Calculate carrier increase for current month
        $currentMonthStart = now()->startOfMonth();
        return $this->carriers->where('created_at', '>=', $currentMonthStart)->count();
    }
    
    public function getMarketingAssociateIncrease(): int
    {
        // Calculate marketing associate increase for current month
        $currentMonthStart = now()->startOfMonth();
        return $this->marketingAssociates->where('created_at', '>=', $currentMonthStart)->count();
    }
    
    public function getProcurementAssociateIncrease(): int
    {
        // Calculate procurement associate increase for current month
        $currentMonthStart = now()->startOfMonth();
        return $this->procurementAssociates->where('created_at', '>=', $currentMonthStart)->count();
    }

    public function mount(User $user = null): void
    {
        $this->user = $user->load('roles');
        $this->loadTerritories();
        $this->loadShippers();
        $this->loadCarriers();
        $this->loadMarketingAssociates();
        $this->loadProcurementAssociates();
        $this->loadActiveShipments();
        $this->loadRecentShippers();
        $this->loadRecentCarriers();
        $this->loadRecentMarketingAssociates();
        $this->loadRecentProcurementAssociates();
    }    
};

?>

<div class="p-6 space-y-6">
    <!-- Header Section -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Operations Logistics Associate</h1>
            <p class="text-gray-600 dark:text-gray-400">Manage carriers and shippers in your sales territory</p>
            <div class="flex items-center gap-2 mt-1">
                <flux:icon name="map-pin" class="w-4 h-4 text-lime-600 dark:text-lime-400" />
                <span class="text-sm font-medium text-lime-600 dark:text-lime-400">Territory: {{ $this->getTerritoryName() }}</span>
            </div>
        </div>
        <div class="flex gap-3">
            <div class="flex gap-3">
                <flux:button type="submit" icon="user-plus" href="{{ route('users.create') }}" wire:navigation
                    variant='primary' color="emerald">
                    Register Shipper
                </flux:button>
                <flux:button type="submit" icon="truck" href="{{ route('users.create') }}" wire:navigation
                    variant='primary' color="sky">
                    Register Carrier
                </flux:button>
            </div>
        </div>
    </div>

    <!-- Territory Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Total Shippers -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-gray-200 dark:border-slate-700">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Shippers</h3>
                <flux:icon name="users" class="w-6 h-6 text-blue-500" />
            </div>
            <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ $totalShippers }}</div>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                @if($this->getShipperIncrease() > 0)
                    <span class="text-green-600 font-semibold">+{{ $this->getShipperIncrease() }}</span> this month
                @else
                    <span class="text-gray-600">No new shippers</span> this month
                @endif
            </div>
        </div>

        <!-- Total Carriers -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-gray-200 dark:border-slate-700">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Carriers</h3>
                <flux:icon name="building-library" class="w-6 h-6 text-green-500" />
            </div>
            <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ $totalCarriers }}</div>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                @if($this->getCarrierIncrease() > 0)
                    <span class="text-green-600 font-semibold">+{{ $this->getCarrierIncrease() }}</span> this month
                @else
                    <span class="text-gray-600">No new carriers</span> this month
                @endif
            </div>
        </div>

        <!-- Marketing Associates -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-gray-200 dark:border-slate-700">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Marketing Associates</h3>
                <flux:icon name="megaphone" class="w-6 h-6 text-purple-500" />
            </div>
            <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ $totalMarketingAssociates }}</div>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                @if($this->getMarketingAssociateIncrease() > 0)
                    <span class="text-green-600 font-semibold">+{{ $this->getMarketingAssociateIncrease() }}</span> this month
                @else
                    <span class="text-gray-600">No new associates</span> this month
                @endif
            </div>
        </div>

        <!-- Procurement Associates -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-gray-200 dark:border-slate-700">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Procurement Associates</h3>
                <flux:icon name="shopping-cart" class="w-6 h-6 text-orange-500" />
            </div>
            <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ $totalProcurementAssociates }}</div>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                @if($this->getProcurementAssociateIncrease() > 0)
                    <span class="text-green-600 font-semibold">+{{ $this->getProcurementAssociateIncrease() }}</span> this month
                @else
                    <span class="text-gray-600">No new associates</span> this month
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Actions Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <!-- Shipper Management -->
        <div class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 hover:border-blue-400 transition-colors group">
            <a href="{{ route('users.create') }}" wire:navigate>
            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-blue-200 transition-colors dark:bg-blue-900">
                <flux:icon name="user-plus" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
            </div>
            <div class="text-left">
                <div class="font-semibold text-gray-900 dark:text-white">Register Shipper</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Add new client</div>
            </div>                
            </a>

        </div>

        <!-- Carrier Management -->
        <button class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 hover:border-green-400 transition-colors group">
            <a  href="{{ route('users.create') }}" wire:navigate>
            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-green-200 transition-colors dark:bg-green-900">
                <flux:icon name="truck" class="w-6 h-6 text-green-600 dark:text-green-400" />
            </div>
            <div class="text-left">
                <div class="font-semibold text-gray-900 dark:text-white">Register Carrier</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Add new carrier</div>
            </div>                
            </a>

        </button>

        <!-- Create Shipment -->
        <button class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 hover:border-lime-400 transition-colors group">
            <a href="{{ route('freights.create') }}" wire:navigate>
            <div class="w-12 h-12 bg-lime-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-lime-200 transition-colors dark:bg-lime-900">
                <flux:icon name="document-plus" class="w-6 h-6 text-lime-600 dark:text-lime-400" />
            </div>
            <div class="text-left">
                <div class="font-semibold text-gray-900 dark:text-white">Create Shipment</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">New logistics order</div>
            </div>                
            </a>

        </button>

        <!-- Post Availability -->
        <button class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 hover:border-purple-400 transition-colors group">
            <a href="{{ route('lane.create') }}" wire:navigate>
            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-purple-200 transition-colors dark:bg-purple-900">
                <flux:icon name="clipboard-document-list" class="w-6 h-6 text-purple-600 dark:text-purple-400" />
            </div>
            <div class="text-left">
                <div class="font-semibold text-gray-900 dark:text-white">Post Availability</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Truck details</div>
            </div>
            </a>
        </button>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Shippers -->
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700">
            <div class="p-6 border-b border-gray-200 dark:border-slate-700">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Shippers</h3>
                    <button class="text-blue-600 hover:text-blue-700 text-sm font-medium dark:text-blue-400">
                        View All
                    </button>
                </div>
            </div>
            <div class="p-6 space-y-4">
                @forelse($recentShippers as $shipper)
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg dark:bg-slate-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center dark:bg-blue-900">
                            <flux:icon name="building-office" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white">{{ $shipper->company_name ?? $shipper->name }}</h4>
                            @php
                                $location = $shipper->buslocation->first();
                                $locationText = $location ? ($location->city ? $location->city . ', ' : '') . ($location->country ?? '') : 'Location not set';
                            @endphp
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $locationText }} • Registered: {{ $shipper->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full dark:bg-green-900 dark:text-green-200">
                        Active
                    </span>
                </div>
                @empty
                <div class="text-center py-4 text-gray-500 dark:text-gray-400">
                    No shippers found in your territory
                </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Carriers -->
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700">
            <div class="p-6 border-b border-gray-200 dark:border-slate-700">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Carriers</h3>
                    <button class="text-green-600 hover:text-green-700 text-sm font-medium dark:text-green-400">
                        View All
                    </button>
                </div>
            </div>
            <div class="p-6 space-y-4">
                @forelse($recentCarriers as $carrier)
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg dark:bg-slate-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center dark:bg-green-900">
                            <flux:icon name="truck" class="w-5 h-5 text-green-600 dark:text-green-400" />
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white">{{ $carrier->company_name ?? $carrier->name }}</h4>
                            @php
                                $location = $carrier->buslocation->first();
                                $locationText = $location ? ($location->city ? $location->city . ', ' : '') . ($location->country ?? '') : 'Location not set';
                            @endphp
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $locationText }} • Registered: {{ $carrier->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full dark:bg-green-900 dark:text-green-200">
                        Complete
                    </span>
                </div>
                @empty
                <div class="text-center py-4 text-gray-500 dark:text-gray-400">
                    No carriers found in your territory
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Team Associates Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Marketing Associates -->
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700">
            <div class="p-6 border-b border-gray-200 dark:border-slate-700">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Marketing Associates</h3>
                    <button class="text-purple-600 hover:text-purple-700 text-sm font-medium dark:text-purple-400">
                        View All ({{ $totalMarketingAssociates }})
                    </button>
                </div>
            </div>
            <div class="p-6 space-y-4">
                @forelse($recentMarketingAssociates as $associate)
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg dark:bg-slate-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center dark:bg-purple-900">
                            <flux:icon name="megaphone" class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white">{{ $associate->name }}</h4>
                            @php
                                $location = $associate->buslocation->first();
                                $locationText = $location ? ($location->city ? $location->city . ', ' : '') . ($location->country ?? '') : 'Location not set';
                            @endphp
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $locationText }} • Joined: {{ $associate->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    <span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs rounded-full dark:bg-purple-900 dark:text-purple-200">
                        Active
                    </span>
                </div>
                @empty
                <div class="text-center py-4 text-gray-500 dark:text-gray-400">
                    No marketing associates found in your territory
                </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Procurement Associates -->
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700">
            <div class="p-6 border-b border-gray-200 dark:border-slate-700">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Procurement Associates</h3>
                    <button class="text-orange-600 hover:text-orange-700 text-sm font-medium dark:text-orange-400">
                        View All ({{ $totalProcurementAssociates }})
                    </button>
                </div>
            </div>
            <div class="p-6 space-y-4">
                @forelse($recentProcurementAssociates as $associate)
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg dark:bg-slate-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center dark:bg-orange-900">
                            <flux:icon name="shopping-cart" class="w-5 h-5 text-orange-600 dark:text-orange-400" />
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white">{{ $associate->name }}</h4>
                            @php
                                $location = $associate->buslocation->first();
                                $locationText = $location ? ($location->city ? $location->city . ', ' : '') . ($location->country ?? '') : 'Location not set';
                            @endphp
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $locationText }} • Joined: {{ $associate->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    <span class="px-2 py-1 bg-orange-100 text-orange-800 text-xs rounded-full dark:bg-orange-900 dark:text-orange-200">
                        Active
                    </span>
                </div>
                @empty
                <div class="text-center py-4 text-gray-500 dark:text-gray-400">
                    No procurement associates found in your territory
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Rest of your existing HTML for Active Shipments, Truck Availability, and Notifications -->
    <!-- Active Shipments in Territory -->
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700">
        <div class="p-6 border-b border-gray-200 dark:border-slate-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Active Shipments in Territory</h3>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Currently active shipments within {{ $this->getTerritoryName() }}</p>
        </div>
        <div class="p-6">
            <!-- Your existing table structure -->
        </div>
    </div>

    <!-- Territory Management -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Truck Availability in Territory -->
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700">
            <div class="p-6 border-b border-gray-200 dark:border-slate-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Truck Availability in Territory</h3>
            </div>
            <div class="p-6 space-y-4">
                <!-- Your existing truck availability content -->
            </div>
        </div>

        <!-- Territory Notifications -->
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700">
            <div class="p-6 border-b border-gray-200 dark:border-slate-700">
                <div class="flex items-center gap-2">
                    <flux:icon name="bell-alert" class="w-5 h-5 text-lime-600 dark:text-lime-400" />
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Territory Notifications</h3>
                </div>
            </div>
            <div class="p-6 space-y-4">
                <!-- Your existing notifications content -->
            </div>
        </div>
    </div>

    <!-- Contracting Notice -->
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-6 dark:bg-amber-900/20 dark:border-amber-700/30">
        <div class="flex items-center gap-3">
            <flux:icon name="exclamation-triangle" class="w-6 h-6 text-amber-600 dark:text-amber-400" />
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