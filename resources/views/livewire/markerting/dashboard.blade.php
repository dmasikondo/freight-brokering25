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
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Marketing Associate Dashboard</h1>
            <p class="text-gray-600 dark:text-gray-400">Manage your registered shippers and logistics operations</p>
        </div>
        <div class="flex gap-3">
            <button class="px-4 py-2 bg-lime-500 text-white rounded-lg hover:bg-lime-600 transition-colors flex items-center gap-2">
                <flux:icon name="user-plus" class="w-5 h-5" />
                Register Shipper
            </button>
            <button class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors flex items-center gap-2">
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
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Registered Shippers</h3>
                <flux:icon name="users" class="w-6 h-6 text-blue-500" />
            </div>
            <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">24</div>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                <span class="text-green-600 font-semibold">+3</span> this month
            </div>
        </div>

        <!-- Pending Invoices -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-gray-200 dark:border-slate-700">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Pending Invoices</h3>
                <flux:icon name="document-text" class="w-6 h-6 text-amber-500" />
            </div>
            <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">8</div>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                <span class="font-semibold">$45,670</span> total pending
            </div>
        </div>

        <!-- Overdue Invoices -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-gray-200 dark:border-slate-700">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Overdue</h3>
                <flux:icon name="exclamation-triangle" class="w-6 h-6 text-red-500" />
            </div>
            <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">3</div>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                <span class="text-red-600 font-semibold">$12,450</span> overdue
            </div>
        </div>

        <!-- Recent Feedback -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-gray-200 dark:border-slate-700">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Avg. Rating</h3>
                <flux:icon name="star" class="w-6 h-6 text-yellow-500" />
            </div>
            <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">4.2</div>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                from <span class="font-semibold">18</span> reviews
            </div>
        </div>
    </div>

    <!-- Quick Actions Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <button class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 hover:border-lime-400 transition-colors group">
            <div class="w-12 h-12 bg-lime-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-lime-200 transition-colors dark:bg-lime-900">
                <flux:icon name="user-plus" class="w-6 h-6 text-lime-600 dark:text-lime-400" />
            </div>
            <div class="text-left">
                <div class="font-semibold text-gray-900 dark:text-white">Register Shipper</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Add new client</div>
            </div>
        </button>

        <button class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 hover:border-blue-400 transition-colors group">
            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-blue-200 transition-colors dark:bg-blue-900">
                <flux:icon name="truck" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
            </div>
            <div class="text-left">
                <div class="font-semibold text-gray-900 dark:text-white">Create Shipment</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">New logistics order</div>
            </div>
        </button>

        <button class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 hover:border-green-400 transition-colors group">
            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-green-200 transition-colors dark:bg-green-900">
                <flux:icon name="currency-dollar" class="w-6 h-6 text-green-600 dark:text-green-400" />
            </div>
            <div class="text-left">
                <div class="font-semibold text-gray-900 dark:text-white">Create Invoice</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Generate bill</div>
            </div>
        </button>

        <button class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 hover:border-purple-400 transition-colors group">
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
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Shippers</h3>
                    <button class="text-blue-600 hover:text-blue-700 text-sm font-medium dark:text-blue-400">
                        View All
                    </button>
                </div>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg dark:bg-slate-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center dark:bg-blue-900">
                            <flux:icon name="building-office" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white">Global Traders Ltd</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Registered: 2 days ago</p>
                        </div>
                    </div>
                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full dark:bg-green-900 dark:text-green-200">
                        Active
                    </span>
                </div>

                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg dark:bg-slate-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center dark:bg-blue-900">
                            <flux:icon name="building-storefront" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white">QuickShip Express</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Registered: 1 week ago</p>
                        </div>
                    </div>
                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full dark:bg-green-900 dark:text-green-200">
                        Active
                    </span>
                </div>

                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg dark:bg-slate-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center dark:bg-blue-900">
                            <flux:icon name="building-library" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white">Metro Freight Co</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Registered: 2 weeks ago</p>
                        </div>
                    </div>
                    <span class="px-2 py-1 bg-amber-100 text-amber-800 text-xs rounded-full dark:bg-amber-900 dark:text-amber-200">
                        Pending
                    </span>
                </div>
            </div>
        </div>

        <!-- Pending Invoices -->
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700">
            <div class="p-6 border-b border-gray-200 dark:border-slate-700">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Pending Invoices</h3>
                    <button class="text-blue-600 hover:text-blue-700 text-sm font-medium dark:text-blue-400">
                        View All
                    </button>
                </div>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex items-center justify-between p-4 bg-amber-50 rounded-lg border border-amber-200 dark:bg-amber-900/20 dark:border-amber-700/30">
                    <div>
                        <h4 class="font-medium text-gray-900 dark:text-white">INV-7842</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Global Traders Ltd</p>
                    </div>
                    <div class="text-right">
                        <div class="font-semibold text-amber-600">$8,450</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Due in 3 days</div>
                    </div>
                </div>

                <div class="flex items-center justify-between p-4 bg-red-50 rounded-lg border border-red-200 dark:bg-red-900/20 dark:border-red-700/30">
                    <div>
                        <h4 class="font-medium text-gray-900 dark:text-white">INV-7791</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">QuickShip Express</p>
                    </div>
                    <div class="text-right">
                        <div class="font-semibold text-red-600">$12,450</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Overdue: 5 days</div>
                    </div>
                </div>

                <div class="flex items-center justify-between p-4 bg-amber-50 rounded-lg border border-amber-200 dark:bg-amber-900/20 dark:border-amber-700/30">
                    <div>
                        <h4 class="font-medium text-gray-900 dark:text-white">INV-7823</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Metro Freight Co</p>
                    </div>
                    <div class="text-right">
                        <div class="font-semibold text-amber-600">$4,780</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Due in 7 days</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                <div class="flex items-start gap-4 p-4 bg-blue-50 rounded-lg dark:bg-blue-900/20">
                    <flux:icon name="chat-bubble-left-right" class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5" />
                    <div class="flex-1">
                        <div class="font-medium text-gray-900 dark:text-white">New message from Global Traders</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Inquiry about shipment tracking for TRK-8923</div>
                        <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">10 minutes ago</div>
                    </div>
                </div>

                <div class="flex items-start gap-4 p-4 bg-green-50 rounded-lg dark:bg-green-900/20">
                    <flux:icon name="check-circle" class="w-5 h-5 text-green-600 dark:text-green-400 mt-0.5" />
                    <div class="flex-1">
                        <div class="font-medium text-gray-900 dark:text-white">Shipment delivered</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">TRK-7845 delivered to QuickShip Express warehouse</div>
                        <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">2 hours ago</div>
                    </div>
                </div>

                <div class="flex items-start gap-4 p-4 bg-red-50 rounded-lg dark:bg-red-900/20">
                    <flux:icon name="exclamation-triangle" class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5" />
                    <div class="flex-1">
                        <div class="font-medium text-gray-900 dark:text-white">Invoice overdue</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">INV-7791 from QuickShip Express is 5 days overdue</div>
                        <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">1 day ago</div>
                    </div>
                </div>
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
                    <button class="px-4 py-2 bg-lime-500 text-white rounded-lg hover:bg-lime-600 transition-colors">
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
                        <button class="px-3 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors text-sm">
                            Contracts
                        </button>
                        <button class="px-3 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors text-sm">
                            Agreements
                        </button>
                        <button class="px-3 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-colors text-sm">
                            Guidelines
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
