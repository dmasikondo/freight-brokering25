<?php

use Livewire\Volt\Component;
use App\Models\User; 
use App\Models\Territory; 
use App\Models\Freight;
use App\Models\Lane;
use Illuminate\Database\Eloquent\Builder;

new class extends Component {
    public array $stats = [];
    public User $user;

    protected $listeners = ['refreshDashboard' => '$refresh'];

    public function mount(User $user = null)
    {
        $this->user = $user->load('roles');
        $this->loadStats();
    }

    public function loadStats()
    {
        $this->stats = [
            'users' => $this->getUserStats(),
            'territories' => $this->getTerritoryStats(),
            'available_loads' => $this->getAvailableLoadsCount(),
            'available_lanes' => $this->getAvailableLanesCount(),
            'active_contracts' => $this->getActiveContractsStats(),
        ];
    }

    private function getUserStats(): array
    {
        $baseQuery = User::query();
        
        if (!$this->user->hasRole('superadmin')) {
            $baseQuery->whereDoesntHave('roles', fn($q) => $q->where('name', 'superadmin'));
        }

        $roles = [
            'carrier', 'shipper', 'marketing logistics associate', 
            'procurement logistics associate', 'operations logistics associate', 
            'logistics operations executive', 'admin'
        ];

        if ($this->user->hasRole('superadmin')) {
            $roles[] = 'superadmin';
        }

        // Aggregate counts efficiently
        $roleCounts = collect($roles)->mapWithKeys(fn($role) => [
            str_replace(' ', '_', $role) => User::whereHas('roles', fn($q) => $q->where('name', $role))->count()
        ]);

        return [
            'total' => $baseQuery->count(),
            'carriers' => $roleCounts['carrier'],
            'fully_registered_carriers' => $this->getFullyRegisteredCarriersCount(),
            'shippers' => $roleCounts['shipper'],
            'backend_staff' => $roleCounts->except(['carrier', 'shipper'])->toArray(),
        ];
    }

    private function getFullyRegisteredCarriersCount(): int
    {
        return User::whereHas('roles', fn($q) => $q->where('name', 'carrier'))
            ->has('traderefs', '>=', 2)
            ->has('directors', '>=', 2)
            ->has('buslocation')
            ->has('fleets')
            ->count();
    }

    private function getTerritoryStats(): array
    {
        return [
            'total' => Territory::count(),
            'recent' => Territory::latest()->take(5)->get(),
        ];
    }

    private function getAvailableLoadsCount(): int
    {
        return Freight::where('status', 'published')
            ->where('shipment_status', '!=', 'delivered')
            ->count();
    }

    private function getAvailableLanesCount(): int
    {
        return Lane::where('status', 'published')
            ->where('vehicle_status', 'inapplicable')
            ->count();
    }

    private function getActiveContractsStats(): array
    {
        return [
            'loads_in_progress' => Freight::whereIn('shipment_status', ['loading', 'in transit'])->count(),
        ];
    }
}; ?>

<div class="min-h-screen bg-gray-50 pb-12">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Admin Dashboard</h1>
                <p class="text-sm text-gray-500">Platform activity at a glance</p>
            </div>
            <flux:button wire:click="loadStats" variant="primary" icon="arrow-path">Refresh</flux:button>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
        <!-- Main Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <flux:link href="{{ route('users.index') }}" wire:navigate class="block">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:border-blue-300 transition-colors">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-50 rounded-lg text-blue-600">
                            <x-graphic name="users" class="w-6 h-6" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Users</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['users']['total'] }}</p>
                        </div>
                    </div>
                </div>
            </flux:link>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-green-50 rounded-lg text-green-600">
                        <x-graphic name="cube" class="w-6 h-6" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Available Loads</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['available_loads'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-orange-50 rounded-lg text-orange-600">
                        <x-graphic name="van" class="w-6 h-6" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Available Vehicles</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['available_lanes'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-red-50 rounded-lg text-red-600">
                        <x-graphic name="clipboard-list" class="w-6 h-6" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Pending Invoices</p>
                        <p class="text-2xl font-bold text-gray-900">--</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- User Statistics Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-bold text-gray-900">User Segments</h3>
                    <x-graphic name="user-group" class="w-5 h-5 text-gray-400" />
                </div>
                <div class="p-6 space-y-4">
                    
                    <flux:link href="{{ route('users.index', ['search' => 'carrier']) }}" wire:navigate>
                        <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                            <div class="flex gap-3">
                            <x-graphic name="van" class="w-5 h-5 text-blue-500" />
                            <span class="text-sm font-semibold text-gray-700">Total Carriers</span>
                        </div>
                        <span class="text-lg font-bold text-blue-600">{{ $stats['users']['carriers'] }}</span>
                        </div>
                    </flux:link>

                    <div class="flex justify-between items-center p-3 bg-indigo-50 border border-indigo-100 rounded-lg">
                        <div class="flex items-center gap-3">
                            <x-graphic name="shield-check" class="w-5 h-5 text-indigo-500" />
                            <span class="text-sm font-semibold text-gray-700">Fully Registered Carriers</span>
                        </div>
                        <span class="text-lg font-bold text-indigo-600">{{ $stats['users']['fully_registered_carriers'] }}</span>
                    </div>

                    <flux:link href="{{ route('users.index', ['search' => 'shipper']) }}" wire:navigate>
                        <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                            <div class="flex gap-3">
                            <x-graphic name="building-office-2" class="w-5 h-5 text-green-500" />
                            <span class="text-sm font-semibold text-gray-700">Shippers</span>                                
                            </div>

                        <span class="text-lg font-bold text-green-600">{{ $stats['users']['shippers'] }}</span>
                        </div>
                    </flux:link>

                                        

                    <div class="pt-4">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Support & Logistics Staff</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach ($stats['users']['backend_staff'] as $role => $count)
                                <flux:link href="{{ route('users.index', ['search' => str_replace('_', ' ', $role)]) }}" wire:navigate class="flex justify-between items-center p-2 bg-gray-50 rounded border border-gray-100 hover:bg-white transition-colors">
                                    <span class="text-xs text-gray-600 capitalize font-medium">{{ str_replace('_', ' ', $role) }}</span>
                                    <span class="text-xs font-bold text-gray-900">{{ $count }}</span>
                                </flux:link>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Territory & Activity -->
            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="font-bold text-gray-900">Territory Distribution</h3>
                        <div class="flex gap-2">
                            <flux:button href="{{ route('territories.index') }}" variant="subtle" size="sm" wire:navigate>View All</flux:button>
                            <flux:button href="{{ route('territories.create') }}" variant="primary" size="sm" icon="plus" wire:navigate>Add</flux:button>
                        </div>
                    </div>
                    <div class="flex items-center gap-6 mb-6">
                        <div class="p-4 bg-purple-50 rounded-xl text-purple-600">
                            <x-graphic name="globe-alt" class="w-8 h-8" />
                        </div>
                        <div>
                            <p class="text-3xl font-bold text-gray-900">{{ $stats['territories']['total'] }}</p>
                            <p class="text-sm text-gray-500 font-medium">Active Territories</p>
                        </div>
                    </div>
                    <div class="space-y-2">
                        @foreach ($stats['territories']['recent'] as $territory)
                            <div class="flex justify-between items-center p-2 bg-gray-50 rounded text-xs">
                                <span class="font-medium text-gray-700">{{ $territory->name }}</span>
                                <span class="text-gray-400">{{ $territory->created_at->diffForHumans() }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Quick Actions Grid -->
                <div class="grid grid-cols-2 gap-4">
                    <flux:link href="{{ route('users.create') }}" wire:navigate class="p-4 bg-white border border-gray-200 rounded-xl flex flex-col items-center gap-2 hover:shadow-md transition-shadow">
                        <x-graphic name="user-add" class="w-6 h-6 text-blue-500" />
                        <span class="text-xs font-bold text-gray-700">Add User</span>
                    </flux:link>
                    <flux:link href="{{ route('freights.index') }}" wire:navigate class="p-4 bg-white border border-gray-200 rounded-xl flex flex-col items-center gap-2 hover:shadow-md transition-shadow">
                        <x-graphic name="cube" class="w-6 h-6 text-green-500" />
                        <span class="text-xs font-bold text-gray-700">Manage Loads</span>
                    </flux:link>
                </div>
            </div>
        </div>
    </div>
</div>