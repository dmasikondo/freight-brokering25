<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<div>
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
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Procurement Associate Dashboard</h1>
            <p class="text-gray-600 dark:text-gray-400">Manage carriers, vehicle availability, and logistics operations</p>
        </div>
        <div class="flex gap-3">
            <button class="px-4 py-2 bg-lime-500 text-white rounded-lg hover:bg-lime-600 transition-colors flex items-center gap-2">
                <flux:icon name="truck" class="w-5 h-5" />
                Register Carrier
            </button>
            <button class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors flex items-center gap-2">
                <flux:icon name="cloud-arrow-up" class="w-5 h-5" />
                Upload Documents
            </button>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Total Registered Carriers -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-gray-200 dark:border-slate-700">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Registered Carriers</h3>
                <flux:icon name="building-library" class="w-6 h-6 text-blue-500" />
            </div>
            <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">18</div>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                <span class="text-green-600 font-semibold">+2</span> this week
            </div>
        </div>

        <!-- Incomplete Registrations -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-gray-200 dark:border-slate-700">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Incomplete Registration</h3>
                <flux:icon name="exclamation-circle" class="w-6 h-6 text-amber-500" />
            </div>
            <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">5</div>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                Need document upload
            </div>
        </div>

        <!-- Available Trucks -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-gray-200 dark:border-slate-700">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Available Trucks</h3>
                <flux:icon name="truck" class="w-6 h-6 text-green-500" />
            </div>
            <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">42</div>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                Across <span class="font-semibold">12</span> carriers
            </div>
        </div>

        <!-- Pending Payments -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-gray-200 dark:border-slate-700">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Pending Payments</h3>
                <flux:icon name="currency-dollar" class="w-6 h-6 text-purple-500" />
            </div>
            <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">7</div>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                <span class="font-semibold">$23,850</span> total
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
                <div class="font-semibold text-gray-900 dark:text-white">Register Carrier</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Add new carrier</div>
            </div>
        </button>

        <button class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 hover:border-blue-400 transition-colors group">
            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-blue-200 transition-colors dark:bg-blue-900">
                <flux:icon name="clipboard-document-list" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
            </div>
            <div class="text-left">
                <div class="font-semibold text-gray-900 dark:text-white">Post Availability</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Truck details</div>
            </div>
        </button>

        <button class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 hover:border-green-400 transition-colors group">
            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-green-200 transition-colors dark:bg-green-900">
                <flux:icon name="credit-card" class="w-6 h-6 text-green-600 dark:text-green-400" />
            </div>
            <div class="text-left">
                <div class="font-semibold text-gray-900 dark:text-white">Process Payments</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Mark invoices paid</div>
            </div>
        </button>

        <button class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 hover:border-purple-400 transition-colors group">
            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-purple-200 transition-colors dark:bg-purple-900">
                <flux:icon name="map-pin" class="w-6 h-6 text-purple-600 dark:text-purple-400" />
            </div>
            <div class="text-left">
                <div class="font-semibold text-gray-900 dark:text-white">Update Tracking</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Shipment status</div>
            </div>
        </button>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Truck Availability Summary -->
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700">
            <div class="p-6 border-b border-gray-200 dark:border-slate-700">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Truck Availability</h3>
                    <button class="text-blue-600 hover:text-blue-700 text-sm font-medium dark:text-blue-400">
                        View All
                    </button>
                </div>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg border border-green-200 dark:bg-green-900/20 dark:border-green-700/30">
                    <div class="flex items-center gap-3">
                        <flux:icon name="truck" class="w-8 h-8 text-green-600 dark:text-green-400" />
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white">Dry Van Trailers</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Available: 15 trucks</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="font-semibold text-green-600">Available</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">24-48 ton capacity</div>
                    </div>
                </div>

                <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg border border-blue-200 dark:bg-blue-900/20 dark:border-blue-700/30">
                    <div class="flex items-center gap-3">
                        <flux:icon name="truck" class="w-8 h-8 text-blue-600 dark:text-blue-400" />
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white">Refrigerated</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Available: 8 trucks</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="font-semibold text-blue-600">Available</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Temp controlled</div>
                    </div>
                </div>

                <div class="flex items-center justify-between p-4 bg-amber-50 rounded-lg border border-amber-200 dark:bg-amber-900/20 dark:border-amber-700/30">
                    <div class="flex items-center gap-3">
                        <flux:icon name="truck" class="w-8 h-8 text-amber-600 dark:text-amber-400" />
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white">Flatbed Trailers</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Available: 12 trucks</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="font-semibold text-amber-600">Limited</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Heavy loads</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Carriers -->
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700">
            <div class="p-6 border-b border-gray-200 dark:border-slate-700">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Carriers</h3>
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
                            <h4 class="font-medium text-gray-900 dark:text-white">Swift Logistics</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Registered: 2 days ago</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full dark:bg-green-900 dark:text-green-200">
                            Complete
                        </span>
                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">8 trucks</div>
                    </div>
                </div>

                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg dark:bg-slate-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center dark:bg-blue-900">
                            <flux:icon name="building-storefront" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white">Metro Haulers</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Registered: 1 week ago</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="px-2 py-1 bg-amber-100 text-amber-800 text-xs rounded-full dark:bg-amber-900 dark:text-amber-200">
                            Incomplete
                        </span>
                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">5 trucks</div>
                    </div>
                </div>

                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg dark:bg-slate-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center dark:bg-blue-900">
                            <flux:icon name="building-library" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white">Global Transport</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Registered: 2 weeks ago</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full dark:bg-green-900 dark:text-green-200">
                            Complete
                        </span>
                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">12 trucks</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Vehicle Management Section -->
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700">
        <div class="p-6 border-b border-gray-200 dark:border-slate-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Vehicle Management</h3>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Publish or unpublish vehicles for public viewing</p>
        </div>
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-slate-700">
                            <th class="text-left py-3 text-sm font-medium text-gray-600 dark:text-gray-400">Vehicle</th>
                            <th class="text-left py-3 text-sm font-medium text-gray-600 dark:text-gray-400">Carrier</th>
                            <th class="text-left py-3 text-sm font-medium text-gray-600 dark:text-gray-400">Type</th>
                            <th class="text-left py-3 text-sm font-medium text-gray-600 dark:text-gray-400">Capacity</th>
                            <th class="text-left py-3 text-sm font-medium text-gray-600 dark:text-gray-400">Status</th>
                            <th class="text-left py-3 text-sm font-medium text-gray-600 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
                        <tr>
                            <td class="py-4">
                                <div class="font-medium text-gray-900 dark:text-white">TRK-7842</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Dry Van</div>
                            </td>
                            <td class="py-4 text-gray-900 dark:text-white">Swift Logistics</td>
                            <td class="py-4 text-gray-600 dark:text-gray-400">Trailer</td>
                            <td class="py-4 text-gray-600 dark:text-gray-400">24 tons</td>
                            <td class="py-4">
                                <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full dark:bg-green-900 dark:text-green-200">
                                    Published
                                </span>
                            </td>
                            <td class="py-4">
                                <button class="text-red-600 hover:text-red-700 text-sm font-medium dark:text-red-400">
                                    Unpublish
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-4">
                                <div class="font-medium text-gray-900 dark:text-white">TRK-7891</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Refrigerated</div>
                            </td>
                            <td class="py-4 text-gray-900 dark:text-white">Metro Haulers</td>
                            <td class="py-4 text-gray-600 dark:text-gray-400">Truck</td>
                            <td class="py-4 text-gray-600 dark:text-gray-400">18 tons</td>
                            <td class="py-4">
                                <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded-full dark:bg-gray-700 dark:text-gray-200">
                                    Unpublished
                                </span>
                            </td>
                            <td class="py-4">
                                <button class="text-green-600 hover:text-green-700 text-sm font-medium dark:text-green-400">
                                    Publish
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-4">
                                <div class="font-medium text-gray-900 dark:text-white">TRK-7923</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Flatbed</div>
                            </td>
                            <td class="py-4 text-gray-900 dark:text-white">Global Transport</td>
                            <td class="py-4 text-gray-600 dark:text-gray-400">Trailer</td>
                            <td class="py-4 text-gray-600 dark:text-gray-400">32 tons</td>
                            <td class="py-4">
                                <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full dark:bg-green-900 dark:text-green-200">
                                    Published
                                </span>
                            </td>
                            <td class="py-4">
                                <button class="text-red-600 hover:text-red-700 text-sm font-medium dark:text-red-400">
                                    Unpublish
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
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
                    <flux:icon name="document-text" class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5" />
                    <div class="flex-1">
                        <div class="font-medium text-gray-900 dark:text-white">New invoice received</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">From Swift Logistics for shipment TRK-7842</div>
                        <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">15 minutes ago</div>
                    </div>
                </div>

                <div class="flex items-start gap-4 p-4 bg-green-50 rounded-lg dark:bg-green-900/20">
                    <flux:icon name="truck" class="w-5 h-5 text-green-600 dark:text-green-400 mt-0.5" />
                    <div class="flex-1">
                        <div class="font-medium text-gray-900 dark:text-white">Truck availability update</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Metro Haulers added 3 new refrigerated trucks</div>
                        <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">2 hours ago</div>
                    </div>
                </div>

                <div class="flex items-start gap-4 p-4 bg-red-50 rounded-lg dark:bg-red-900/20">
                    <flux:icon name="exclamation-triangle" class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5" />
                    <div class="flex-1">
                        <div class="font-medium text-gray-900 dark:text-white">Incident report</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Minor delay reported for TRK-7891 - Weather conditions</div>
                        <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">4 hours ago</div>
                    </div>
                </div>

                <div class="flex items-start gap-4 p-4 bg-purple-50 rounded-lg dark:bg-purple-900/20">
                    <flux:icon name="chat-bubble-left-right" class="w-5 h-5 text-purple-600 dark:text-purple-400 mt-0.5" />
                    <div class="flex-1">
                        <div class="font-medium text-gray-900 dark:text-white">Message from carrier</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Global Transport - Inquiry about load confirmation</div>
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
                        Upload contracts, carrier profiles, invoices, and compliance documents
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
                        Get standard forms and agreements for carrier management
                    </p>
                    <div class="flex gap-2 justify-center flex-wrap">
                        <button class="px-3 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors text-sm">
                            Carrier Contracts
                        </button>
                        <button class="px-3 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors text-sm">
                            Load Confirm
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

</div>
