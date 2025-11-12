<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Invoice;
use App\Models\Freight;
use App\Models\Territory;
use App\Models\Buslocation;
use App\Models\UserCreation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

new class extends Component {
    // Stats properties
    public $registeredShippers = 0;
    public $shippersThisMonth = 0;
    // public $pendingInvoicesCount = 0;
    // public $pendingInvoicesTotal = 0;
    // public $overdueInvoicesCount = 0;
    // public $overdueInvoicesTotal = 0;
    public $averageRating = 0;
    public $totalReviews = 0;

    // Data collections
    public $recentShippers = [];
    // public $pendingInvoices = [];
    public $notifications = [];

    public function mount()
    {
        $this->loadStats();
        $this->loadRecentShippers();
        // $this->loadPendingInvoices();
        $this->loadNotifications();
    }

    public function loadStats()
    {
        $currentUser = Auth::user();
        
        // Get shippers created by current user OR in user's assigned territories
        $shippersQuery = $this->getShippersQuery();
        $this->registeredShippers = $shippersQuery->count();

        // Shippers registered this month
        $this->shippersThisMonth = $shippersQuery
            ->whereMonth('users.created_at', now()->month)
            ->whereYear('users.created_at', now()->year)
            ->count();

        // Pending invoices for the user's shippers
        // $userShipperIds = $shippersQuery->pluck('users.id');
        // $pendingInvoices = Invoice::where('status', 'pending')
        //     ->whereIn('user_id', $userShipperIds)
        //     ->get();
            
        // $this->pendingInvoicesCount = $pendingInvoices->count();
        // $this->pendingInvoicesTotal = $pendingInvoices->sum('amount');

        // Overdue invoices (pending + due date passed)
        // $overdueInvoices = Invoice::where('status', 'pending')
        //     ->whereIn('user_id', $userShipperIds)
        //     ->where('due_date', '<', now())
        //     ->get();
            
        // $this->overdueInvoicesCount = $overdueInvoices->count();
        // $this->overdueInvoicesTotal = $overdueInvoices->sum('amount');

        // Mock rating data
        $this->averageRating = 4.2;
        $this->totalReviews = 18;
    }

    public function loadRecentShippers()
    {
        $shippersQuery = $this->getShippersQuery();
        
        $this->recentShippers = $shippersQuery
            ->with(['roles' => function($query) {
                $query->where('name', 'shipper');
            }])
            ->latest('users.created_at')
            ->take(5)
            ->get()
            ->map(function($shipper) {
                return [
                    'id' => $shipper->id,
                    'name' => $shipper->company_name ?? $shipper->name,
                    'registration_date' => $shipper->created_at->diffForHumans(),
                    'status' => $shipper->status ?? 'active',
                    'email' => $shipper->email,
                    'phone' => $shipper->phone,
                    'source' => $shipper->pivot_source ?? 'direct' // Track how the shipper is associated
                ];
            })->toArray();

        // Fallback mock data if no shippers exist
        if (empty($this->recentShippers)) {
            $this->recentShippers = [
                [
                    'name' => 'Global Traders Ltd',
                    'registration_date' => '2 days ago',
                    'status' => 'active',
                    'source' => 'direct'
                ],
                [
                    'name' => 'QuickShip Express',
                    'registration_date' => '1 week ago',
                    'status' => 'active',
                    'source' => 'territory'
                ],
                [
                    'name' => 'Metro Freight Co',
                    'registration_date' => '2 weeks ago',
                    'status' => 'pending',
                    'source' => 'direct'
                ]
            ];
        }
    }

    // public function loadPendingInvoices()
    // {
    //     $currentUser = Auth::user();
    //     $userShipperIds = $this->getShippersQuery()->pluck('users.id');

    //     $this->pendingInvoices = Invoice::with(['shipper' => function($query) {
    //             $query->select('id', 'name', 'company_name');
    //         }])
    //         ->where('status', 'pending')
    //         ->whereIn('user_id', $userShipperIds)
    //         ->orderBy('due_date')
    //         ->take(5)
    //         ->get()
    //         ->map(function($invoice) {
    //             $isOverdue = $invoice->due_date && $invoice->due_date->lt(now());
                
    //             return [
    //                 'id' => $invoice->id,
    //                 'invoice_number' => $invoice->invoice_number,
    //                 'shipper_name' => $invoice->shipper->company_name ?? $invoice->shipper->name,
    //                 'amount' => $invoice->amount,
    //                 'due_date' => $invoice->due_date,
    //                 'is_overdue' => $isOverdue,
    //                 'days_overdue' => $isOverdue ? now()->diffInDays($invoice->due_date) : null,
    //                 'due_in_days' => !$isOverdue ? now()->diffInDays($invoice->due_date, false) : null,
    //             ];
    //         })->toArray();

    //     // Fallback mock data if no invoices exist
    //     if (empty($this->pendingInvoices)) {
    //         $this->pendingInvoices = [
    //             [
    //                 'invoice_number' => 'INV-7842',
    //                 'shipper_name' => 'Global Traders Ltd',
    //                 'amount' => 8450,
    //                 'due_in_days' => 3,
    //                 'is_overdue' => false
    //             ],
    //             [
    //                 'invoice_number' => 'INV-7791',
    //                 'shipper_name' => 'QuickShip Express',
    //                 'amount' => 12450,
    //                 'days_overdue' => 5,
    //                 'is_overdue' => true
    //             ],
    //             [
    //                 'invoice_number' => 'INV-7823',
    //                 'shipper_name' => 'Metro Freight Co',
    //                 'amount' => 4780,
    //                 'due_in_days' => 7,
    //                 'is_overdue' => false
    //             ]
    //         ];
    //     }
    // }

    /**
     * Get shippers that are either:
     * 1. Created by the current user, OR
     * 2. Have buslocations in territories assigned to the current user
     */
    private function getShippersQuery()
    {
        $currentUser = Auth::user();
        
        // Get territories assigned to the current user
        $userTerritories = $currentUser->territories()->pluck('territories.id');
        
        // Base query for shippers (users with shipper role)
        $shippersQuery = User::whereHas('roles', function($q) {
            $q->where('name', 'shipper');
        });

        // Shippers created by current user
        $createdShippers = User::whereHas('createdBy', function($query) use ($currentUser) {
            $query->where('creator_user_id', $currentUser->id);
        })->whereHas('roles', function($q) {
            $q->where('name', 'shipper');
        });

        // Shippers with buslocations in user's territories
        $territoryShippers = User::whereHas('roles', function($q) {
            $q->where('name', 'shipper');
        })->whereHas('buslocation', function($query) use ($userTerritories) {
            $query->whereHas('city', function($q) use ($userTerritories) {
                $q->whereIn('territory_id', $userTerritories);
            });
        });

        // Combine both queries using UNION (to avoid duplicates)
        $shipperIds = $createdShippers->pluck('users.id')
            ->merge($territoryShippers->pluck('users.id'))
            ->unique();

        return User::whereIn('users.id', $shipperIds);
    }

    /**
     * Alternative method using subqueries (more efficient for counting)
     */
    private function getShippersCount()
    {
        $currentUser = Auth::user();
        
        // Get territories assigned to the current user
        $userTerritories = $currentUser->territories()->pluck('territories.id');
        
        // Count shippers created by current user
        $createdShippersCount = User::whereHas('createdBy', function($query) use ($currentUser) {
            $query->where('creator_user_id', $currentUser->id);
        })->whereHas('roles', function($q) {
            $q->where('name', 'shipper');
        })->count();

        // Count shippers with buslocations in user's territories
        $territoryShippersCount = User::whereHas('roles', function($q) {
            $q->where('name', 'shipper');
        })->whereHas('buslocation', function($query) use ($userTerritories) {
            $query->whereHas('city', function($q) use ($userTerritories) {
                $q->whereIn('territory_id', $userTerritories);
            });
        })->count();

        // Since there might be overlap, we need to get the unique count
        $createdShipperIds = User::whereHas('createdBy', function($query) use ($currentUser) {
            $query->where('creator_user_id', $currentUser->id);
        })->whereHas('roles', function($q) {
            $q->where('name', 'shipper');
        })->pluck('users.id');

        $territoryShipperIds = User::whereHas('roles', function($q) {
            $q->where('name', 'shipper');
        })->whereHas('buslocation', function($query) use ($userTerritories) {
            $query->whereHas('city', function($q) use ($userTerritories) {
                $q->whereIn('territory_id', $userTerritories);
            });
        })->pluck('users.id');

        return $createdShipperIds->merge($territoryShipperIds)->unique()->count();
    }

    public function loadNotifications()
    {
        // You can replace this with your actual notifications system
        $this->notifications = [
            [
                'type' => 'message',
                'title' => 'New message from Global Traders',
                'description' => 'Inquiry about shipment tracking for TRK-8923',
                'time' => '10 minutes ago',
                'icon' => 'chat-bubble-left-right',
                'color' => 'blue'
            ],
            [
                'type' => 'delivery',
                'title' => 'Shipment delivered',
                'description' => 'TRK-7845 delivered to QuickShip Express warehouse',
                'time' => '2 hours ago',
                'icon' => 'check-circle',
                'color' => 'green'
            ],
            // [
            //     'type' => 'overdue',
            //     'title' => 'Invoice overdue',
            //     'description' => 'INV-7791 from QuickShip Express is 5 days overdue',
            //     'time' => '1 day ago',
            //     'icon' => 'exclamation-triangle',
            //     'color' => 'red'
            // ]
        ];
    }

    // Action methods
    public function registerShipper()
    {
        return redirect()->route('shippers.create');
    }

    public function createShipment()
    {
        return redirect()->route('shipments.create');
    }

    // public function createInvoice()
    // {
    //     return redirect()->route('invoices.create');
    // }

    public function viewPaymentTracking()
    {
        return redirect()->route('payments.index');
    }

    public function uploadDocuments()
    {
        $this->dispatch('trigger-file-upload');
    }

    public function downloadTemplate($type)
    {
        match($type) {
            'contracts' => $this->downloadContractTemplate(),
            'agreements' => $this->downloadAgreementTemplate(),
            'guidelines' => $this->downloadGuidelineTemplate(),
        };
    }

    protected function downloadContractTemplate()
    {
        return response()->download(storage_path('templates/contract-template.pdf'));
    }

    protected function downloadAgreementTemplate()
    {
        return response()->download(storage_path('templates/agreement-template.pdf'));
    }

    protected function downloadGuidelineTemplate()
    {
        return response()->download(storage_path('templates/guideline-template.pdf'));
    }

    public function viewAllShippers()
    {
        return redirect()->route('shippers.index');
    }

    // public function viewAllInvoices()
    // {
    //     return redirect()->route('invoices.index');
    // }

    // Helper methods for the view
    public function getStatusColor($status)
    {
        return match($status) {
            'active' => 'green',
            'pending' => 'amber',
            'inactive' => 'red',
            default => 'gray'
        };
    }

    public function formatCurrency($amount)
    {
        return '$' . number_format($amount);
    }

    // Helper to show source badge in the view
    public function getSourceBadge($source)
    {
        return match($source) {
            'direct' => ['color' => 'blue', 'text' => 'Direct'],
            'territory' => ['color' => 'green', 'text' => 'Territory'],
            default => ['color' => 'gray', 'text' => 'Unknown']
        };
    }
}; ?>

<div>
    <div class="p-6 space-y-6">
        <!-- Header Section -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Marketing Associate Dashboard</h1>
                <p class="text-gray-600 dark:text-gray-400">Manage your registered shippers and logistics operations</p>
            </div>
            <div class="flex gap-3">
                <button 
                    wire:click="registerShipper"
                    class="px-4 py-2 bg-lime-500 text-white rounded-lg hover:bg-lime-600 transition-colors flex items-center gap-2"
                >
                    <flux:icon name="user-plus" class="w-5 h-5" />
                    Register Shipper
                </button>
                <button 
                    wire:click="createShipment"
                    class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors flex items-center gap-2"
                >
                    <flux:icon name="document-plus" class="w-5 h-5" />
                    New Shipment
                </button>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Total Registered Shippers -->
            <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-gray-200 dark:border-slate-700">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">My Shippers</h3>
                    <flux:icon name="users" class="w-6 h-6 text-blue-500" />
                </div>
                <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ $registeredShippers }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    <span class="text-green-600 font-semibold">+{{ $shippersThisMonth }}</span> this month
                </div>
            </div>

            <!-- Pending Invoices -->
            {{-- <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-gray-200 dark:border-slate-700">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Pending Invoices</h3>
                    <flux:icon name="document-text" class="w-6 h-6 text-amber-500" />
                </div>
                <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ $pendingInvoicesCount }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    <span class="font-semibold">{{ $this->formatCurrency($pendingInvoicesTotal) }}</span> total pending
                </div>
            </div> --}}

            <!-- Overdue Invoices -->
            {{-- <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-gray-200 dark:border-slate-700">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Overdue</h3>
                    <flux:icon name="exclamation-triangle" class="w-6 h-6 text-red-500" />
                </div>
                <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ $overdueInvoicesCount }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    <span class="text-red-600 font-semibold">{{ $this->formatCurrency($overdueInvoicesTotal) }}</span> overdue
                </div>
            </div> --}}

            <!-- Recent Feedback -->
            <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-gray-200 dark:border-slate-700">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Avg. Rating</h3>
                    <flux:icon name="star" class="w-6 h-6 text-yellow-500" />
                </div>
                <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ $averageRating }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    from <span class="font-semibold">{{ $totalReviews }}</span> reviews
                </div>
            </div>
        </div>

        <!-- Quick Actions Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <button 
                wire:click="registerShipper"
                class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 hover:border-lime-400 transition-colors group"
            >
                <div class="w-12 h-12 bg-lime-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-lime-200 transition-colors dark:bg-lime-900">
                    <flux:icon name="user-plus" class="w-6 h-6 text-lime-600 dark:text-lime-400" />
                </div>
                <div class="text-left">
                    <div class="font-semibold text-gray-900 dark:text-white">Register Shipper</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Add new client</div>
                </div>
            </button>

            <button 
                wire:click="createShipment"
                class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 hover:border-blue-400 transition-colors group"
            >
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-blue-200 transition-colors dark:bg-blue-900">
                    <flux:icon name="truck" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                </div>
                <div class="text-left">
                    <div class="font-semibold text-gray-900 dark:text-white">Create Shipment</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">New logistics order</div>
                </div>
            </button>

            {{-- <button 
                wire:click="createInvoice"
                class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 hover:border-green-400 transition-colors group"
            >
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-green-200 transition-colors dark:bg-green-900">
                    <flux:icon name="currency-dollar" class="w-6 h-6 text-green-600 dark:text-green-400" />
                </div>
                <div class="text-left">
                    <div class="font-semibold text-gray-900 dark:text-white">Create Invoice</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Generate bill</div>
                </div>
            </button> --}}

            <button 
                wire:click="viewPaymentTracking"
                class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 hover:border-purple-400 transition-colors group"
            >
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-purple-200 transition-colors dark:bg-purple-900">
                    <flux:icon name="chart-bar" class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                </div>
                <div class="text-left">
                    <div class="font-semibold text-gray-900 dark:text-white">Payment Tracking</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Monitor payments</div>
                </div>
            </button>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Shippers -->
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700">
                <div class="p-6 border-b border-gray-200 dark:border-slate-700">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">My Recent Shippers</h3>
                        <button 
                            wire:click="viewAllShippers"
                            class="text-blue-600 hover:text-blue-700 text-sm font-medium dark:text-blue-400"
                        >
                            View All
                        </button>
                    </div>
                </div>
                <div class="p-6 space-y-4">
                    @foreach($recentShippers as $shipper)
                        @php
                            $sourceBadge = $this->getSourceBadge($shipper['source'] ?? 'direct');
                        @endphp
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg dark:bg-slate-700">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center dark:bg-blue-900">
                                    <flux:icon name="building-office" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900 dark:text-white">{{ $shipper['name'] }}</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Registered: {{ $shipper['registration_date'] }}</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="px-2 py-1 bg-{{ $this->getStatusColor($shipper['status']) }}-100 text-{{ $this->getStatusColor($shipper['status']) }}-800 text-xs rounded-full dark:bg-{{ $this->getStatusColor($shipper['status']) }}-900 dark:text-{{ $this->getStatusColor($shipper['status']) }}-200">
                                    {{ ucfirst($shipper['status']) }}
                                </span>
                                <span class="px-2 py-1 bg-{{ $sourceBadge['color'] }}-100 text-{{ $sourceBadge['color'] }}-800 text-xs rounded-full dark:bg-{{ $sourceBadge['color'] }}-900 dark:text-{{ $sourceBadge['color'] }}-200">
                                    {{ $sourceBadge['text'] }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Pending Invoices -->
            {{-- <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700">
                <div class="p-6 border-b border-gray-200 dark:border-slate-700">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Pending Invoices</h3>
                        <button 
                            wire:click="viewAllInvoices"
                            class="text-blue-600 hover:text-blue-700 text-sm font-medium dark:text-blue-400"
                        >
                            View All
                        </button>
                    </div>
                </div>
                <div class="p-6 space-y-4">
                    @foreach($pendingInvoices as $invoice)
                        <div class="flex items-center justify-between p-4 bg-{{ $invoice['is_overdue'] ? 'red' : 'amber' }}-50 rounded-lg border border-{{ $invoice['is_overdue'] ? 'red' : 'amber' }}-200 dark:bg-{{ $invoice['is_overdue'] ? 'red' : 'amber' }}-900/20 dark:border-{{ $invoice['is_overdue'] ? 'red' : 'amber' }}-700/30">
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white">{{ $invoice['invoice_number'] }}</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $invoice['shipper_name'] }}</p>
                            </div>
                            <div class="text-right">
                                <div class="font-semibold text-{{ $invoice['is_overdue'] ? 'red' : 'amber' }}-600">{{ $this->formatCurrency($invoice['amount']) }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                    @if($invoice['is_overdue'])
                                        Overdue: {{ $invoice['days_overdue'] }} days
                                    @else
                                        Due in {{ $invoice['due_in_days'] }} days
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div> --}}
        </div>

        <!-- Rest of the component remains the same -->
        <!-- Notifications Section -->
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700">
            <div class="p-6 border-b border-gray-200 dark:border-slate-700">
                <div class="flex items-center gap-2">
                    <flux:icon name="bell-alert" class="w-5 h-5 text-lime-600 dark:text-lime-400" />
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Notifications</h3>
                </div>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($notifications as $notification)
                        <div class="flex items-start gap-4 p-4 bg-{{ $notification['color'] }}-50 rounded-lg dark:bg-{{ $notification['color'] }}-900/20">
                            <flux:icon name="{{ $notification['icon'] }}" class="w-5 h-5 text-{{ $notification['color'] }}-600 dark:text-{{ $notification['color'] }}-400 mt-0.5" />
                            <div class="flex-1">
                                <div class="font-medium text-gray-900 dark:text-white">{{ $notification['title'] }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">{{ $notification['description'] }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">{{ $notification['time'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Documents Section -->
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700">
            <div class="p-6 border-b border-gray-200 dark:border-slate-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Documents</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Upload Documents -->
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center dark:border-slate-600">
                        <flux:icon name="cloud-arrow-up" class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Upload Documents</h4>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">
                            Upload invoices, compliance documents, or client agreements
                        </p>
                        <button 
                            wire:click="uploadDocuments"
                            class="px-4 py-2 bg-lime-500 text-white rounded-lg hover:bg-lime-600 transition-colors"
                        >
                            Upload Files
                        </button>
                    </div>

                    <!-- Download Templates -->
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center dark:border-slate-600">
                        <flux:icon name="arrow-down-tray" class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Download Templates</h4>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">
                            Get standard contracts, agreements, and guidelines
                        </p>
                        <div class="flex gap-2 justify-center">
                            <button 
                                wire:click="downloadTemplate('contracts')"
                                class="px-3 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors text-sm"
                            >
                                Contracts
                            </button>
                            <button 
                                wire:click="downloadTemplate('agreements')"
                                class="px-3 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors text-sm"
                            >
                                Agreements
                            </button>
                            <button 
                                wire:click="downloadTemplate('guidelines')"
                                class="px-3 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-colors text-sm"
                            >
                                Guidelines
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>