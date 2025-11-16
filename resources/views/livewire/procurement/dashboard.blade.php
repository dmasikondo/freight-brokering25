<?php

use Livewire\Volt\Component;
use App\Models\User;

new class extends Component {
    public $registeredCarriers;
    public $incompleteRegistrations;
    public $payments;
    public $notifications;
    public $user;

    protected function loadRegisteredCarriers()
    {
        $user = $this->user;

        // Get carriers registered by the user or in user's territories
        $this->registeredCarriers = User::whereHas('roles', function ($query) {
            $query->where('name', 'carrier');
        })
            ->where(function ($query) use ($user) {
                // Carriers created by this user
                $query
                    ->whereHas('createdBy', function ($q) use ($user) {
                        $q->where('creator_user_id', $user->id);
                    })
                    // Or carriers in user's territories
                    ->orWhereHas('territories', function ($q) use ($user) {
                        $q->whereIn('territories.id', $user->territories->pluck('id'));
                    });
            })
            ->with(['fleets', 'buslocation', 'directors', 'traderefs'])
            ->get()
            ->map(function ($carrier) {
                return [
                    'id' => $carrier->id,
                    'name' => $carrier->name,
                    'email' => $carrier->email,
                    'status' => $carrier->status ?? 'active',
                    'registration_date' => $carrier->created_at->format('Y-m-d'),
                    'fleet_count' => $carrier->fleets->count(),
                    'bus_location_count' => $carrier->buslocation->count(),
                    'directors_count' => $carrier->directors->count(),
                    'traderefs_count' => $carrier->traderefs->count(),
                ];
            });
    }

    protected function loadIncompleteRegistrations()
    {
        $user = $this->user;

        // Get user's territory IDs
        $userTerritoryIds = $user->territories->pluck('id');

        // Get incomplete carriers in two parts:
        // 1. Carriers registered by user OR in user's territories with missing fleet, directors, or traderefs
        $incompleteCarriersPart1 = User::whereHas('roles', function ($query) {
            $query->where('name', 'carrier');
        })
            ->where(function ($query) use ($user, $userTerritoryIds) {
                // Carriers created by this user
                $query
                    ->whereHas('createdBy', function ($q) use ($user) {
                        $q->where('creator_user_id', $user->id);
                    })
                    // Or carriers in user's territories
                    ->orWhereHas('territories', function ($q) use ($userTerritoryIds) {
                        $q->whereIn('territories.id', $userTerritoryIds);
                    });
            })
            ->with(['fleets', 'buslocation', 'directors', 'traderefs'])
            ->get()
            ->filter(function ($carrier) {
                // Check if any required information is missing (fleet, directors, traderefs)
                return $carrier->fleets->count() === 0 || $carrier->directors->count() === 0 || $carrier->traderefs->count() === 0;
            });

        // 2. Carriers with missing buslocation (regardless of who registered them or territory)
        $incompleteCarriersPart2 = User::whereHas('roles', function ($query) {
            $query->where('name', 'carrier');
        })
            ->with(['fleets', 'buslocation', 'directors', 'traderefs'])
            ->get()
            ->filter(function ($carrier) {
                // Check if buslocation is missing
                return $carrier->buslocation->count() === 0;
            });

        // Combine both results and remove duplicates
        $allIncompleteCarriers = $incompleteCarriersPart1->merge($incompleteCarriersPart2)->unique('id');

        // Store the full count for the stats card
        $this->incompleteRegistrationsCount = $allIncompleteCarriers->count();

        // But only take 5 for the display list
        $this->incompleteRegistrations = $allIncompleteCarriers->take(5)->map(function ($carrier) {
            $missingInfo = [];
            if ($carrier->fleets->count() === 0) {
                $missingInfo[] = 'fleet';
            }
            if ($carrier->buslocation->count() === 0) {
                $missingInfo[] = 'buslocation';
            }
            if ($carrier->directors->count() === 0) {
                $missingInfo[] = 'directors';
            }
            if ($carrier->traderefs->count() === 0) {
                $missingInfo[] = 'traderefs';
            }
            return [
                'id' => $carrier->id,
                'name' => $carrier->name,
                'email' => $carrier->email,
                'missing_info' => $missingInfo,
                'created_at' => $carrier->created_at->format('Y-m-d'),
                'is_buslocation_only' => $carrier->buslocation->count() === 0 && $carrier->fleets->count() > 0 && $carrier->directors->count() > 0 && $carrier->traderefs->count() > 0,
            ];
        });
    }

    protected function loadPayments()
    {
        // Dummy payment data only
        $this->payments = [
            [
                'id' => 1,
                'carrier_name' => 'ABC Transport Ltd',
                'amount' => 2500.0,
                'due_date' => '2024-04-15',
                'status' => 'pending',
            ],
            [
                'id' => 2,
                'carrier_name' => 'XYZ Logistics',
                'amount' => 1800.5,
                'due_date' => '2024-04-10',
                'status' => 'paid',
            ],
            [
                'id' => 3,
                'carrier_name' => 'Quick Delivery Services',
                'amount' => 3200.75,
                'due_date' => '2024-04-20',
                'status' => 'overdue',
            ],
        ];
    }

    protected function loadNotifications()
    {
        // Notification headings only as requested
        $this->notifications = ['New carrier registration pending approval', 'Payment received from ABC Transport Ltd', 'Incomplete registration requires attention', 'Territory assignment updated', 'Document verification completed'];
    }

    public function getRegisteredCarriersCountProperty()
    {
        return $this->registeredCarriers->count();
    }

    public function getIncompleteRegistrationsCountProperty()
    {
        return $this->incompleteRegistrations->count();
    }

    public function getPendingPaymentsCountProperty()
    {
        return collect($this->payments)->where('status', 'pending')->count();
    }

    public function getOverduePaymentsCountProperty()
    {
        return collect($this->payments)->where('status', 'overdue')->count();
    }

    // Additional helper to show carriers that only need buslocation
    public function getBuslocationOnlyCountProperty()
    {
        return $this->incompleteRegistrations->where('is_buslocation_only', true)->count();
    }

    public function mount(User $user = null)
    {
        $this->user = $user?->load('roles');
        $this->loadRegisteredCarriers();
        $this->loadIncompleteRegistrations();
        $this->loadPayments();
        $this->loadNotifications();
    }
}; ?>

<div class="p-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Procurement Associate Dashboard</h1>
            <p class="text-gray-600 dark:text-gray-400">Manage carriers, vehicle availability, and logistics operations
            </p>
        </div>
        <div class="flex gap-3">
            <div class="flex gap-3">
                <flux:button type="submit" icon="user-plus" href="{{ route('users.create') }}" wire:navigation
                    variant='primary' color="emerald">
                    Register Carrier
                </flux:button>
                <flux:button type="submit" icon="document-plus" href="{{ route('lane.create') }}" wire:navigation
                    variant='primary' color="sky">
                    Post Truck
                </flux:button>
            </div>
        </div>
    </div>
    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Registered Carriers -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Registered Carriers</h3>
                    <p class="text-2xl font-semibold text-gray-900">{{ $this->registeredCarriersCount }}</p>
                </div>
            </div>
        </div>

        <!-- Incomplete Registrations -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-yellow-100 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Incomplete Registrations</h3>
                    <p class="text-2xl font-semibold text-gray-900">{{ $this->incompleteRegistrationsCount }}</p>
                </div>
            </div>
        </div>

        <!-- Pending Payments -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-orange-100 rounded-lg">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Pending Payments</h3>
                    <p class="text-2xl font-semibold text-gray-900">
                        {{-- {{ $this->pendingPaymentsCount }} --}} --

                    </p>
                </div>
            </div>
        </div>

        <!-- Overdue Payments -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-red-100 rounded-lg">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Overdue Payments</h3>
                    <p class="text-2xl font-semibold text-gray-900">
                        {{-- {{ $this->overduePaymentsCount }} --}} --

                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Registered Carriers List -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Registered Carriers</h3>
            </div>
            <div class="p-6">
                @if ($registeredCarriers->count() > 0)
                    <div class="space-y-4">
                        @foreach ($registeredCarriers as $carrier)
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                <div>
                                    <h4 class="font-medium text-gray-900">{{ $carrier['name'] }}</h4>
                                    <p class="text-sm text-gray-500">{{ $carrier['email'] }}</p>
                                    <div class="flex space-x-4 mt-2 text-xs text-gray-500">
                                        <span>Fleets: {{ $carrier['fleet_count'] }}</span>
                                        <span>Locations: {{ $carrier['bus_location_count'] }}</span>
                                        <span>Directors: {{ $carrier['directors_count'] }}</span>
                                        <span>Trade Refs: {{ $carrier['traderefs_count'] }}</span>
                                    </div>
                                </div>
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                    {{ $carrier['status'] }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-4">No registered carriers found.</p>
                @endif
            </div>
        </div>

        <!-- Incomplete Registrations -->
        {{-- <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Incomplete Registrations</h3>
            </div>
            <div class="p-6">
                @if ($incompleteRegistrations->count() > 0)
                    <div class="space-y-4">
                        @foreach ($incompleteRegistrations as $registration)
                            <div class="p-4 border border-yellow-200 rounded-lg bg-yellow-50">
                                <h4 class="font-medium text-gray-900">{{ $registration['name'] }}</h4>
                                <p class="text-sm text-gray-500">{{ $registration['email'] }}</p>
                                <div class="mt-2">
                                    <span class="text-xs font-medium text-yellow-800">Missing:</span>
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        @foreach ($registration['missing_info'] as $missing)
                                            <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">
                                                {{ $missing }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-4">All registrations are complete.</p>
                @endif
            </div>
        </div> --}}
        <!-- Incomplete Registrations -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">Recent Incomplete Registrations</h3>
                    @if ($this->buslocationOnlyCount > 0)
                        <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                            {{ $this->buslocationOnlyCount }} need buslocation only
                        </span>
                    @endif
                </div>
            </div>
            <div class="p-6">
                @if ($incompleteRegistrations->count() > 0)
                    <div class="space-y-4">
                        @foreach ($incompleteRegistrations as $registration)
                            <div
                                class="p-4 border rounded-lg 
                        @if ($registration['is_buslocation_only']) border-blue-200 bg-blue-50
                        @else border-yellow-200 bg-yellow-50 @endif">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-900">
                                            <flux:link href="#"> {{ $registration['name'] }}</flux:link>

                                        </h4>
                                        <p class="text-sm text-gray-500">{{ $registration['email'] }}</p>
                                        <div class="mt-2">
                                            <span
                                                class="text-xs font-medium 
                                        @if ($registration['is_buslocation_only']) text-blue-800
                                        @else text-yellow-800 @endif">
                                                Missing Information:
                                            </span>
                                            <div class="flex flex-wrap gap-1 mt-1">
                                                @foreach ($registration['missing_info'] as $missing)
                                                    <span
                                                        class="px-2 py-1 text-xs rounded-full
                                                @if ($missing === 'buslocation' && $registration['is_buslocation_only']) bg-blue-100 text-blue-800 border border-blue-200
                                                @else bg-yellow-100 text-yellow-800 @endif">
                                                        {{ $missing }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @if ($registration['is_buslocation_only'])
                                        <span
                                            class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                            Bus Location Only
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-4">All registrations are complete.</p>
                @endif
            </div>
        </div>

        <!-- Payments Section -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Recent Payments</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach ($payments as $payment)
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                            <div>
                                <h4 class="font-medium text-gray-900">
                                    {{-- {{ $payment['carrier_name'] }} --}}

                                </h4>
                                <p class="text-sm text-gray-500">
                                    {{-- ${{ number_format($payment['amount'], 2) }} --}}

                                </p>
                                <p class="text-xs text-gray-400">Due:
                                    {{-- {{ $payment['due_date'] }} --}}

                                </p>
                            </div>
                            <span
                                class="px-2 py-1 text-xs font-medium rounded-full 
                                @if ($payment['status'] === 'paid') bg-green-100 text-green-800
                                @elseif($payment['status'] === 'pending') bg-orange-100 text-orange-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ $payment['status'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Notifications Section -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Notifications</h3>
            </div>
            {{-- <div class="p-6">
                <div class="space-y-3">
                    @foreach ($notifications as $notification)
                        <div class="flex items-start p-3 border border-gray-200 rounded-lg">
                            <div class="flex-shrink-0 mt-1">
                                <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                            </div>
                            <p class="ml-3 text-sm text-gray-700">{{ $notification }}</p>
                        </div>
                    @endforeach
                </div>
            </div> --}}
        </div>
    </div>
</div>
