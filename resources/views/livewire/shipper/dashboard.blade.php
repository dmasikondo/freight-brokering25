<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<div>
<div class="p-6 space-y-6">
    <!-- Header Section -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Shipper Dashboard</h1>
            <p class="text-gray-600 dark:text-gray-400">Manage your shipments and track progress</p>
        </div>
        <div class="flex items-center gap-4">
            <div class="text-right">
                <div class="text-sm text-gray-600 dark:text-gray-400">Shipper ID</div>
                <div class="font-mono font-bold text-lime-600 dark:text-lime-400">ZWHR012406001S</div>
            </div>
            <button class="px-4 py-2 bg-lime-500 text-white rounded-lg hover:bg-lime-600 transition-colors flex items-center gap-2">
                <flux:icon name="plus-circle" class="w-5 h-5" />
                New Shipment
            </button>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Total Shipments -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-gray-200 dark:border-slate-700">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Total Shipments</h3>
                <flux:icon name="cube" class="w-6 h-6 text-blue-500" />
            </div>
            <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">48</div>
            <div class="space-y-1 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">In Transit</span>
                    <span class="font-semibold text-amber-600">12</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Delivered</span>
                    <span class="font-semibold text-green-600">36</span>
                </div>
            </div>
        </div>

        <!-- Bids Summary -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-gray-200 dark:border-slate-700">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Bids Made</h3>
                <flux:icon name="scale" class="w-6 h-6 text-purple-500" />
            </div>
            <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">24</div>
            <div class="space-y-1 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Active</span>
                    <span class="font-semibold text-blue-600">8</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Accepted</span>
                    <span class="font-semibold text-green-600">14</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Rejected</span>
                    <span class="font-semibold text-red-600">2</span>
                </div>
            </div>
        </div>

        <!-- Shipment Status -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-gray-200 dark:border-slate-700">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Shipment Status</h3>
                <flux:icon name="document-text" class="w-6 h-6 text-lime-500" />
            </div>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Draft</span>
                    <span class="px-3 py-1 bg-gray-100 text-gray-800 text-sm rounded-full font-medium dark:bg-gray-700 dark:text-gray-200">5</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Submitted</span>
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full font-medium dark:bg-blue-900 dark:text-blue-200">3</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Published</span>
                    <span class="px-3 py-1 bg-green-100 text-green-800 text-sm rounded-full font-medium dark:bg-green-900 dark:text-green-200">8</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Unpublished</span>
                    <span class="px-3 py-1 bg-amber-100 text-amber-800 text-sm rounded-full font-medium dark:bg-amber-900 dark:text-amber-200">2</span>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-gray-200 dark:border-slate-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h3>
            <div class="space-y-3">
                <button class="w-full px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors flex items-center gap-2 justify-center">
                    <flux:icon name="document-plus" class="w-4 h-4" />
                    New SRF
                </button>
                <button class="w-full px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors flex items-center gap-2 justify-center">
                    <flux:icon name="truck" class="w-4 h-4" />
                    Request Load
                </button>
                <button class="w-full px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-colors flex items-center gap-2 justify-center">
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
            <p class="text-gray-600 dark:text-gray-400 mt-1">Complete all forms to publish loads</p>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- SRF Card -->
                <div class="border border-gray-200 dark:border-slate-700 rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center dark:bg-blue-900">
                                <flux:icon name="user-circle" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white">SRF</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Shipper Registration</p>
                            </div>
                        </div>
                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full dark:bg-green-900 dark:text-green-200">
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

                    <button class="w-full px-4 py-2 border border-blue-500 text-blue-600 rounded-lg hover:bg-blue-50 transition-colors dark:border-blue-400 dark:text-blue-400 dark:hover:bg-blue-900/20">
                        <flux:icon name="eye" class="w-4 h-4 inline mr-2" />
                        View Form
                    </button>
                </div>

                <!-- LRF Card -->
                <div class="border border-gray-200 dark:border-slate-700 rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center dark:bg-green-900">
                                <flux:icon name="clipboard-document-list" class="w-6 h-6 text-green-600 dark:text-green-400" />
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white">LRF</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Load Request</p>
                            </div>
                        </div>
                        <span class="px-2 py-1 bg-amber-100 text-amber-800 text-xs rounded-full dark:bg-amber-900 dark:text-amber-200">
                            60%
                        </span>
                    </div>
                    
                    <div class="mb-4">
                        <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-1">
                            <span>Progress</span>
                            <span>3/5</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                            <div class="bg-amber-500 h-2 rounded-full" style="width: 60%"></div>
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

                    <button class="w-full px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                        <flux:icon name="pencil-square" class="w-4 h-4 inline mr-2" />
                        Complete Form
                    </button>
                </div>

                <!-- CAF Card -->
                <div class="border border-gray-200 dark:border-slate-700 rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center dark:bg-purple-900">
                                <flux:icon name="credit-card" class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white">CAF</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Credit Application</p>
                            </div>
                        </div>
                        <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full dark:bg-red-900 dark:text-red-200">
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

                    <button class="w-full px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-colors">
                        <flux:icon name="document-arrow-up" class="w-4 h-4 inline mr-2" />
                        Upload Documents
                    </button>
                </div>

                <!-- LC Card -->
                <div class="border border-gray-200 dark:border-slate-700 rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-lime-100 rounded-xl flex items-center justify-center dark:bg-lime-900">
                                <flux:icon name="check-badge" class="w-6 h-6 text-lime-600 dark:text-lime-400" />
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white">LC</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Load Confirm</p>
                            </div>
                        </div>
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full dark:bg-blue-900 dark:text-blue-200">
                            Ready
                        </span>
                    </div>
                    
                    <div class="mb-4">
                        <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-1">
                            <span>Progress</span>
                            <span>100%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                            <div class="bg-blue-500 h-2 rounded-full" style="width: 100%"></div>
                        </div>
                    </div>

                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Load confirmation available for published shipments
                    </div>

                    <button class="w-full px-4 py-2 bg-lime-500 text-white rounded-lg hover:bg-lime-600 transition-colors">
                        <flux:icon name="document-check" class="w-4 h-4 inline mr-2" />
                        Confirm Load
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700">
        <div class="p-6 border-b border-gray-200 dark:border-slate-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Activity</h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg dark:bg-slate-700">
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center dark:bg-green-900">
                        <flux:icon name="check-circle" class="w-5 h-5 text-green-600 dark:text-green-400" />
                    </div>
                    <div class="flex-1">
                        <div class="font-medium text-gray-900 dark:text-white">Shipment #TRK-7842 delivered</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Just now • From Nairobi to Mombasa</div>
                    </div>
                    <div class="text-right">
                        <div class="font-semibold text-green-600">Completed</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">15 tons</div>
                    </div>
                </div>

                <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg dark:bg-slate-700">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center dark:bg-blue-900">
                        <flux:icon name="clock" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div class="flex-1">
                        <div class="font-medium text-gray-900 dark:text-white">Bid submitted for Load #LD-4591</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">2 hours ago • Awaiting carrier response</div>
                    </div>
                    <div class="text-right">
                        <div class="font-semibold text-blue-600">Pending</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">$1,250</div>
                    </div>
                </div>

                <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg dark:bg-slate-700">
                    <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center dark:bg-amber-900">
                        <flux:icon name="exclamation-triangle" class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                    </div>
                    <div class="flex-1">
                        <div class="font-medium text-gray-900 dark:text-white">Credit Application pending review</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">1 day ago • Contact admin for approval</div>
                    </div>
                    <div class="text-right">
                        <div class="font-semibold text-amber-600">Action Required</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">CAF-2301</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
