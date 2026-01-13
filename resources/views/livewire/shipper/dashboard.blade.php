<?php

use Livewire\Volt\Component;
use App\Models\User;

new class extends Component {
    public $user;

    public function mount(User $user)
    {
        $this->user = $user->load('buslocation', 'roles');
    }

    public function isUnapproved()
    {
        return is_null($this->user->approved_at);
    }
}; ?>

<div class="p-6 space-y-6">
    <!-- Header Section -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Shipper Dashboard</h1>
            <p class="text-gray-600 dark:text-gray-400">Manage your shipments and track progress</p>
        </div>
        <div class="flex items-center gap-4">
            @if(!$this->isUnapproved())
            <div class="text-right">
                <div class="text-sm text-gray-600 dark:text-gray-400">Shipper ID</div>
                <div class="font-mono font-bold text-lime-600 dark:text-lime-400">{{ $user->identificationNumber }}</div>
            </div>
            @endif
            <flux:button type="submit" icon="plus-circle" variant="primary" color="emerald"
                href="{{ route('freights.create') }}">
                New Shipment
            </flux:button>
        </div>
    </div>
    @if ($this->isUnapproved())
        <div
            class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded-r-xl dark:bg-amber-900/20 dark:border-amber-600">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <flux:icon name="clock" class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                </div>
                <div class="ml-3">
                    <p class="text-sm text-amber-800 dark:text-amber-200">
                        <span class="font-bold">{{ __('Account Pending Approval:') }}</span>
                        {{ __('Your account is currently under review. Approval usually takes up to 2 working days. You will be notified once you can start publishing loads.') }}
                    </p>
                </div>
            </div>
        </div>
    @endif
    <livewire:users.contact-info :user="$user" />

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Total Shipments -->
        <livewire:shipper.shipment-status :user="$user" />



        <livewire:shipper.freight-status :user="$user" />

        <!-- Quick Actions -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-gray-200 dark:border-slate-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h3>
            <div class="space-y-3">
                <button @if ($this->isUnapproved()) disabled @endif @class([
                    'w-full px-4 py-2 text-white rounded-lg transition-colors flex items-center gap-2 justify-center',
                    'bg-blue-500 hover:bg-blue-600' => !$this->isUnapproved(),
                    'bg-gray-400 cursor-not-allowed opacity-60' => $this->isUnapproved(),
                ])>
                    <flux:icon name="document-plus" class="w-4 h-4" />
                    New SRF
                </button>
                <button @if ($this->isUnapproved()) disabled @endif @class([
                    'w-full px-4 py-2 text-white rounded-lg transition-colors flex items-center gap-2 justify-center',
                    'bg-blue-500 hover:bg-blue-600' => !$this->isUnapproved(),
                    'bg-gray-400 cursor-not-allowed opacity-60' => $this->isUnapproved(),
                ])>
                    <flux:icon name="truck" class="w-4 h-4" />
                    Request Load
                </button>
                <button @if ($this->isUnapproved()) disabled @endif @class([
                    'w-full px-4 py-2 text-white rounded-lg transition-colors flex items-center gap-2 justify-center',
                    'bg-blue-500 hover:bg-blue-600' => !$this->isUnapproved(),
                    'bg-gray-400 cursor-not-allowed opacity-60' => $this->isUnapproved(),
                ])>
                    <flux:icon name="credit-card" class="w-4 h-4" />
                    Credit App
                </button>
            </div>
        </div>
    </div>

    <!-- Documents Completion Status -->
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700">
        <div class="p-6 border-b border-gray-200 dark:border-slate-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Required Documents</h3>
            <p class="text-gray-600 dark:text-gray-400 mt-1">You need to complete these forms when you get a load</p>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- SRF Card -->
                <div class="border border-gray-200 dark:border-slate-700 rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center dark:bg-blue-900">
                                <flux:icon name="user-circle" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white">SRF</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Shipper Registration</p>
                            </div>
                        </div>
                        <span
                            class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full dark:bg-green-900 dark:text-green-200">
                            Complete
                        </span>
                    </div>

                    <div class="mb-4">
                        <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-1">
                            <span>Progress</span>
                            <span>100%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                            <div class="bg-green-500 h-2 rounded-full" style="width: 100%"></div>
                        </div>
                    </div>

                    <button
                        class="w-full px-4 py-2 border border-blue-500 text-blue-600 rounded-lg hover:bg-blue-50 transition-colors dark:border-blue-400 dark:text-blue-400 dark:hover:bg-blue-900/20">
                        <flux:icon name="eye" class="w-4 h-4 inline mr-2" />
                        View Form
                    </button>
                </div>

                <!-- LRF Card -->
                <div class="border border-gray-200 dark:border-slate-700 rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center dark:bg-green-900">
                                <flux:icon name="clipboard-document-list"
                                    class="w-6 h-6 text-green-600 dark:text-green-400" />
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white">LRF</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Load Request</p>
                            </div>
                        </div>
                        <span
                            class="px-2 py-1 bg-amber-100 text-amber-800 text-xs rounded-full dark:bg-amber-900 dark:text-amber-200">
                            0%
                        </span>
                    </div>

                    <div class="mb-4">
                        <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-1">
                            <span>Progress</span>
                            <span>0/5</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                            <div class="bg-amber-500 h-2 rounded-full" style="width: 0%"></div>
                        </div>
                    </div>

                    <div class="space-y-2 mb-4">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">Load Details</span>
                            <flux:icon name="check-circle" class="w-4 h-4 text-green-500" />
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">Requirements</span>
                            <flux:icon name="check-circle" class="w-4 h-4 text-green-500" />
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">Timeline</span>
                            <div class="w-4 h-4">
                                <x-placeholder-pattern class="w-full h-full text-gray-400" />
                            </div>
                        </div>
                    </div>

                    <button
                        class="w-full px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                        <flux:icon name="pencil-square" class="w-4 h-4 inline mr-2" />
                        Complete Form
                    </button>
                </div>

                <!-- CAF Card -->
                <div class="border border-gray-200 dark:border-slate-700 rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center dark:bg-purple-900">
                                <flux:icon name="credit-card" class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white">CAF</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Credit Application</p>
                            </div>
                        </div>
                        <span
                            class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full dark:bg-red-900 dark:text-red-200">
                            Required
                        </span>
                    </div>

                    <div class="mb-4">
                        <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-1">
                            <span>Progress</span>
                            <span>0%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                            <div class="bg-red-500 h-2 rounded-full" style="width: 0%"></div>
                        </div>
                    </div>

                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Credit form required from admin to publish loads
                    </div>

                    <button
                        class="w-full px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-colors">
                        <flux:icon name="document-arrow-up" class="w-4 h-4 inline mr-2" />
                        Upload Documents
                    </button>
                </div>

                <!-- LC Card -->
                <div class="border border-gray-200 dark:border-slate-700 rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-12 h-12 bg-lime-100 rounded-xl flex items-center justify-center dark:bg-lime-900">
                                <flux:icon name="check-badge" class="w-6 h-6 text-lime-600 dark:text-lime-400" />
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white">LC</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Load Confirm</p>
                            </div>
                        </div>
                        <span
                            class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full dark:bg-blue-900 dark:text-blue-200">
                            Ready
                        </span>
                    </div>

                    <div class="mb-4">
                        <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-1">
                            <span>Progress</span>
                            <span>0%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                            <div class="bg-blue-500 h-2 rounded-full" style="width: 0%"></div>
                        </div>
                    </div>

                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Load confirmation available for published shipments
                    </div>

                    <button
                        class="w-full px-4 py-2 bg-lime-500 text-white rounded-lg hover:bg-lime-600 transition-colors">
                        <flux:icon name="document-check" class="w-4 h-4 inline mr-2" />
                        Confirm Load
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
