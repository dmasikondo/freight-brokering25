<?php


use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Territory;
use App\Models\Freight;
use App\Models\Lane;
// use App\Models\Invoice;
// use App\Models\Contract;
use Illuminate\Support\Facades\Auth;

new class  extends Component
{
    public $stats = [];
    public $user;
    
    protected $listeners = ['refreshDashboard' => '$refresh'];



    public function loadStats()
    {
        $this->stats = [
            'users' => $this->getUserStats(),
            'territories' => $this->getTerritoryStats(),
            'available_loads' => $this->getAvailableLoadsCount(),
            'available_lanes' => $this->getAvailableLanesCount(),
            //'pending_invoices' => $this->getPendingInvoicesStats(),
            'active_contracts' => $this->getActiveContractsStats(),
        ];
    }

private function getUserStats()
{
    $totalUsers = User::whereDoesntHave('roles', function($q) {
        $q->where('name', 'superadmin');
    })->count();

    return [
        'total' => $totalUsers,
        'carriers' => User::whereHas('roles', function($q) {
            $q->where('name', 'carrier');
        })->count(),
        'shippers' => User::whereHas('roles', function($q) {
            $q->where('name', 'shipper');
        })->count(),
        'backend_staff' => [
            'marketing_logistics_associate' => User::whereHas('roles', function($q) {
                $q->where('name', 'marketing logistics associate');
            })->count(),
            'procurement_associate' => User::whereHas('roles', function($q) {
                $q->where('name', 'procurement associate');
            })->count(),
            'operations_logistics_associate' => User::whereHas('roles', function($q) {
                $q->where('name', 'operations logistics associate');
            })->count(),
            'logistics_operations_executive' => User::whereHas('roles', function($q) {
                $q->where('name', 'logistics operations executive');
            })->count(),
            'admins' => User::whereHas('roles', function($q) {
                $q->where('name', 'admin');
            })->count(),
        ]
    ];
}

    private function getTerritoryStats()
    {
        return [
            'total' => Territory::count(),
            'recent' => Territory::latest()->take(5)->get()
        ];
    }

    private function getAvailableLoadsCount()
    {
        return Freight::where('status', 'published')
            ->where('shipment_status', '!=', 'delivered')
            ->count();
    }

    private function getAvailableLanesCount()
    {
        return Lane::where('status', 'available')->count();
    }

    // private function getPendingInvoicesStats()
    // {
    //     $pendingInvoices = Invoice::where('status', 'pending')->get();
        
    //     return [
    //         'count' => $pendingInvoices->count(),
    //         'total_value' => $pendingInvoices->sum('amount'),
    //         'formatted_value' => number_format($pendingInvoices->sum('amount'), 2)
    //     ];
    // }

    private function getActiveContractsStats()
    {
        return [
            //'count' => Contract::where('status', 'active')->count(),
            'loads_in_progress' => Freight::whereIn('shipment_status', ['loading', 'in transit'])->count()
        ];
    }

    public function mount(User $user = null)
    {
        $this->user = $user->load('roles');
        $this->loadStats();
    }
};?>

<div>
    {{-- <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Admin Dashboard</h1>
            <p class="text-gray-600 dark:text-gray-400">Overview of platform statistics and activities and user management</p>
        </div>
        <div class="flex gap-3">
            <button class="px-4 py-2 bg-lime-500 text-white rounded-lg hover:bg-lime-600 transition-colors flex items-center gap-2">
                <flux:icon name="document-plus" class="w-5 h-5" />
                Generate Load Number
            </button>
            <button class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors flex items-center gap-2">
                <flux:icon name="lane" class="w-5 h-5" />
                Register Carrier
            </button>
        </div>
    </div> --}}

<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
                    <p class="mt-1 text-sm text-gray-500">Overview of platform statistics and activities</p>
                </div>
                <div class="flex items-center space-x-4">
                    <button wire:click="loadStats" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Users -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Users</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['users']['total'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Available Loads -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Available Loads</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['available_loads'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Available Lanes -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-orange-100 rounded-lg">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Available Vehicles</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['available_lanes'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Pending Invoices -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-red-100 rounded-lg">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Pending Invoices</p>
                        <p class="text-2xl font-bold text-gray-900">0</p>
                        <p class="text-sm text-red-600 font-medium">$--</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Sections -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- User Statistics -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">User Statistics</h3>
                    <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View All</a>
                </div>
                
                <div class="space-y-4">
                    <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg">
                        <span class="text-sm font-medium text-gray-700">Carriers</span>
                        <span class="text-lg font-bold text-blue-600">{{ $stats['users']['carriers'] ?? 0 }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                        <span class="text-sm font-medium text-gray-700">Shippers</span>
                        <span class="text-lg font-bold text-green-600">{{ $stats['users']['shippers'] ?? 0 }}</span>
                    </div>

                    <!-- Backend Staff -->
                    <div class="mt-4">
                        <h4 class="text-md font-semibold text-gray-800 mb-3">Backend Staff</h4>
                        <div class="grid grid-cols-1 gap-2">
                            @foreach($stats['users']['backend_staff'] ?? [] as $role => $count)
                                <div class="flex justify-between items-center py-2 px-3 bg-gray-50 rounded">
                                    <span class="text-sm text-gray-600 capitalize">{{ str_replace('_', ' ', $role) }}</span>
                                    <span class="text-sm font-bold text-gray-800">{{ $count }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contracts & Territories -->
            <div class="space-y-6">
                <!-- Active Contracts -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Active Contracts</h3>
                        <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View All</a>
                    </div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['active_contracts']['count'] ?? 0 }}</p>
                            <p class="text-sm text-gray-600">Total Active Contracts</p>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-bold text-orange-600">{{ $stats['active_contracts']['loads_in_progress'] ?? 0 }}</p>
                            <p class="text-sm text-gray-600">Loads in Progress</p>
                        </div>
                    </div>
                </div>

                <!-- Territories -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Territories</h3>
                        <div class="flex space-x-2">
                            <a href="{{ route('territories.create') }}" class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700">
                                Add Territory
                            </a>
                            <a href="{{ route('territories.index') }}" class="inline-flex items-center px-3 py-1 border border-gray-300 text-gray-700 text-sm rounded-md hover:bg-gray-50">
                                View All
                            </a>
                        </div>
                    </div>
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['territories']['total'] ?? 0 }}</p>
                            <p class="text-sm text-gray-600">Total Territories</p>
                        </div>
                    </div>
                    
                    @if(isset($stats['territories']['recent']) && $stats['territories']['recent']->count() > 0)
                        <div class="space-y-2">
                            <h4 class="text-sm font-medium text-gray-700">Recent Territories</h4>
                            @foreach($stats['territories']['recent'] as $territory)
                                <div class="flex justify-between items-center py-2 px-3 bg-gray-50 rounded">
                                    <span class="text-sm text-gray-800">{{ $territory->name }}</span>
                                    <span class="text-xs text-gray-500">{{ $territory->created_at->diffForHumans() }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('users.create') }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="p-2 bg-blue-100 rounded-lg mr-4">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">Register User</p>
                        <p class="text-sm text-gray-500">Add new user to platform</p>
                    </div>
                </a>
                
                <a href="{{ route('freights.index') }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="p-2 bg-green-100 rounded-lg mr-4">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">Manage Loads</p>
                        <p class="text-sm text-gray-500">View and manage available loads</p>
                    </div>
                </a>
                
                <a href="#" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="p-2 bg-purple-100 rounded-lg mr-4">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">Process Invoices</p>
                        <p class="text-sm text-gray-500">Manage pending invoices</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>    
</div>
