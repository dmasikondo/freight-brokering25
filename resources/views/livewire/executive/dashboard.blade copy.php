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
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Logistics Operations Executive</h1>
            <p class="text-gray-600 dark:text-gray-400">Manage loads, carriers, and oversee logistics operations</p>
        </div>
        <div class="flex gap-3">
            <button class="px-4 py-2 bg-lime-500 text-white rounded-lg hover:bg-lime-600 transition-colors flex items-center gap-2">
                <flux:icon name="document-plus" class="w-5 h-5" />
                Generate Load Number
            </button>
            <button class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors flex items-center gap-2">
                <flux:icon name="truck" class="w-5 h-5" />
                Register Carrier
            </button>
        </div>
    </div>

    <!-- Executive Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Available Loads -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-gray-200 dark:border-slate-700">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Available Loads</h3>
                <flux:icon name="clipboard-document-list" class="w-6 h-6 text-blue-500" />
            </div>
            <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">47</div>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                From Marketing & Operations Associates
            </div>
        </div>

        <!-- Available Trucks -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-gray-200 dark:border-slate-700">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Available Trucks</h3>
                <flux:icon name="truck" class="w-6 h-6 text-green-500" />
            </div>
            <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">156</div>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                From Procurement & Operations Associates
            </div>
        </div>

        <!-- Pending Invoices -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-gray-200 dark:border-slate-700">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Pending Invoices</h3>
                <flux:icon name="banknotes" class="w-6 h-6 text-amber-500" />
            </div>
            <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">23</div>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                <span class="font-semibold">$187,450</span> total pending
            </div>
        </div>

        <!-- Active Contracts -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-gray-200 dark:border-slate-700">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Active Contracts</h3>
                <flux:icon name="document-check" class="w-6 h-6 text-purple-500" />
            </div>
            <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">89</div>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                Loads in progress
            </div>
        </div>
    </div>

    <!-- Quick Actions Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <button class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 hover:border-lime-400 transition-colors group">
            <div class="w-12 h-12 bg-lime-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-lime-200 transition-colors dark:bg-lime-900">
                <flux:icon name="hashtag" class="w-6 h-6 text-lime-600 dark:text-lime-400" />
            </div>
            <div class="text-left">
                <div class="font-semibold text-gray-900 dark:text-white">Generate Load</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Create load number</div>
            </div>
        </button>

        <button class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 hover:border-blue-400 transition-colors group">
            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-blue-200 transition-colors dark:bg-blue-900">
                <flux:icon name="truck" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
            </div>
            <div class="text-left">
                <div class="font-semibold text-gray-900 dark:text-white">Register Carrier</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Add new carrier</div>
            </div>
        </button>

        <button class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 hover:border-green-400 transition-colors group">
            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-green-200 transition-colors dark:bg-green-900">
                <flux:icon name="credit-card" class="w-6 h-6 text-green-600 dark:text-green-400" />
            </div>
            <div class="text-left">
                <div class="font-semibold text-gray-900 dark:text-white">Process Payment</div>
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

    <!-- Load Generation Section -->
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700">
        <div class="p-6 border-b border-gray-200 dark:border-slate-700">
            <div class="flex items-center gap-2">
                <flux:icon name="hashtag" class="w-5 h-5 text-lime-600 dark:text-lime-400" />
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Load Number Generation</h3>
            </div>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Generate unique load numbers for tracking, payments, and references</p>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Generate New Load -->
                <div class="space-y-4">
                    <h4 class="font-medium text-gray-900 dark:text-white">Generate New Load Number</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Shipper ID</label>
                            <select class="w-full border border-gray-300 rounded-lg px-3 py-2 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                                <option>ZWHR012406001S</option>
                                <option>ZWHR012406002S</option>
                                <option>ZWHR012406003S</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Carrier ID</label>
                            <select class="w-full border border-gray-300 rounded-lg px-3 py-2 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                                <option>ZWCR012406001C</option>
                                <option>ZWCR012406002C</option>
                                <option>ZWCR012406003C</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Load Type</label>
                            <select class="w-full border border-gray-300 rounded-lg px-3 py-2 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                                <option>Dry Van</option>
                                <option>Refrigerated</option>
                                <option>Flatbed</option>
                                <option>Tanker</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Weight (tons)</label>
                            <input type="number" class="w-full border border-gray-300 rounded-lg px-3 py-2 dark:bg-slate-700 dark:border-slate-600 dark:text-white" placeholder="e.g., 25">
                        </div>
                    </div>
                    <button class="w-full bg-lime-500 text-white py-3 rounded-lg hover:bg-lime-600 transition-colors font-medium flex items-center justify-center gap-2">
                        <flux:icon name="hashtag" class="w-5 h-5" />
                        Generate Load Number
                    </button>
                </div>

                <!-- Recent Load Numbers -->
                <div class="space-y-4">
                    <h4 class="font-medium text-gray-900 dark:text-white">Recent Load Numbers</h4>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg dark:bg-slate-700">
                            <div>
                                <div class="font-mono font-bold text-lime-600 dark:text-lime-400">LD-ZWHR012406001S-ZWCR012406001C-001</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Generated: Today 14:23</div>
                            </div>
                            <flux:icon name="document-duplicate" class="w-5 h-5 text-gray-400 hover:text-lime-600 cursor-pointer" />
                        </div>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg dark:bg-slate-700">
                            <div>
                                <div class="font-mono font-bold text-lime-600 dark:text-lime-400">LD-ZWHR012406002S-ZWCR012406002C-001</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Generated: Today 11:45</div>
                            </div>
                            <flux:icon name="document-duplicate" class="w-5 h-5 text-gray-400 hover:text-lime-600 cursor-pointer" />
                        </div>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg dark:bg-slate-700">
                            <div>
                                <div class="font-mono font-bold text-lime-600 dark:text-lime-400">LD-ZWHR012406003S-ZWCR012406003C-001</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Generated: Yesterday</div>
                            </div>
                            <flux:icon name="document-duplicate" class="w-5 h-5 text-gray-400 hover:text-lime-600 cursor-pointer" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Available Loads Summary -->
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700">
            <div class="p-6 border-b border-gray-200 dark:border-slate-700">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Available Loads</h3>
                    <button class="text-blue-600 hover:text-blue-700 text-sm font-medium dark:text-blue-400">
                        View All (47)
                    </button>
                </div>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg border border-blue-200 dark:bg-blue-900/20 dark:border-blue-700/30">
                    <div class="flex items-center gap-3">
                        <flux:icon name="cube" class="w-8 h-8 text-blue-600 dark:text-blue-400" />
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white">Nairobi → Mombasa</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Dry Van • 24 tons • Urgent</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="font-semibold text-blue-600">Marketing</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">ZWHR012406001S</div>
                    </div>
                </div>

                <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg border border-green-200 dark:bg-green-900/20 dark:border-green-700/30">
                    <div class="flex items-center gap-3">
                        <flux:icon name="cube" class="w-8 h-8 text-green-600 dark:text-green-400" />
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white">Dar es Salaam → Kampala</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Refrigerated • 18 tons</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="font-semibold text-green-600">Operations</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">ZWHR012406002S</div>
                    </div>
                </div>

                <div class="flex items-center justify-between p-4 bg-amber-50 rounded-lg border border-amber-200 dark:bg-amber-900/20 dark:border-amber-700/30">
                    <div class="flex items-center gap-3">
                        <flux:icon name="cube" class="w-8 h-8 text-amber-600 dark:text-amber-400" />
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white">Kigali → Nairobi</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Flatbed • 32 tons</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="font-semibold text-amber-600">Marketing</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">ZWHR012406003S</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Available Trucks Summary -->
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700">
            <div class="p-6 border-b border-gray-200 dark:border-slate-700">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Available Trucks</h3>
                    <button class="text-green-600 hover:text-green-700 text-sm font-medium dark:text-green-400">
                        View All (156)
                    </button>
                </div>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg dark:bg-slate-700">
                    <div class="flex items-center gap-3">
                        <flux:icon name="truck" class="w-8 h-8 text-green-600 dark:text-green-400" />
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white">Dry Van Trailers</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">67 trucks available</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="font-semibold text-green-600">Procurement</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">24-48 tons</div>
                    </div>
                </div>

                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg dark:bg-slate-700">
                    <div class="flex items-center gap-3">
                        <flux:icon name="truck" class="w-8 h-8 text-blue-600 dark:text-blue-400" />
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white">Refrigerated</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">42 trucks available</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="font-semibold text-blue-600">Operations</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Temp controlled</div>
                    </div>
                </div>

                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg dark:bg-slate-700">
                    <div class="flex items-center gap-3">
                        <flux:icon name="truck" class="w-8 h-8 text-amber-600 dark:text-amber-400" />
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white">Flatbed Trailers</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">28 trucks available</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="font-semibold text-amber-600">Procurement</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Heavy loads</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Carrier Management -->
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700">
        <div class="p-6 border-b border-gray-200 dark:border-slate-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Carrier Management</h3>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Manage carriers registered by you or assigned to you</p>
        </div>
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-slate-700">
                            <th class="text-left py-3 text-sm font-medium text-gray-600 dark:text-gray-400">Carrier ID</th>
                            <th class="text-left py-3 text-sm font-medium text-gray-600 dark:text-gray-400">Trade Name</th>
                            <th class="text-left py-3 text-sm font-medium text-gray-600 dark:text-gray-400">Contact</th>
                            <th class="text-left py-3 text-sm font-medium text-gray-600 dark:text-gray-400">Trucks</th>
                            <th class="text-left py-3 text-sm font-medium text-gray-600 dark:text-gray-400">Status</th>
                            <th class="text-left py-3 text-sm font-medium text-gray-600 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
                        <tr>
                            <td class="py-4 font-mono text-lime-600 dark:text-lime-400">ZWCR012406001C</td>
                            <td class="py-4">
                                <div class="font-medium text-gray-900 dark:text-white">Swift Logistics</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Registered by You</div>
                            </td>
                            <td class="py-4">
                                <div class="text-gray-900 dark:text-white">john@swiftlogistics.com</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">+254 712 345 678</div>
                            </td>
                            <td class="py-4 text-gray-600 dark:text-gray-400">12</td>
                            <td class="py-4">
                                <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full dark:bg-green-900 dark:text-green-200">
                                    Active
                                </span>
                            </td>
                            <td class="py-4">
                                <button class="text-blue-600 hover:text-blue-700 text-sm font-medium dark:text-blue-400 mr-3">
                                    Edit
                                </button>
                                <button class="text-green-600 hover:text-green-700 text-sm font-medium dark:text-green-400">
                                    View
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-4 font-mono text-gray-400 dark:text-gray-500">ZWCR012406002C</td>
                            <td class="py-4">
                                <div class="font-medium text-gray-900 dark:text-white">Metro Haulers</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Assigned to You</div>
                            </td>
                            <td class="py-4">
                                <div class="text-gray-900 dark:text-white">-</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">-</div>
                            </td>
                            <td class="py-4 text-gray-600 dark:text-gray-400">8</td>
                            <td class="py-4">
                                <span class="px-2 py-1 bg-amber-100 text-amber-800 text-xs rounded-full dark:bg-amber-900 dark:text-amber-200">
                                    Pending
                                </span>
                            </td>
                            <td class="py-4">
                                <button class="text-blue-600 hover:text-blue-700 text-sm font-medium dark:text-blue-400 mr-3">
                                    Edit
                                </button>
                                <button class="text-green-600 hover:text-green-700 text-sm font-medium dark:text-green-400">
                                    View
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-4 font-mono text-gray-400 dark:text-gray-500">ZWCR012406003C</td>
                            <td class="py-4">
                                <div class="font-medium text-gray-900 dark:text-white">Global Transport</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Other Associate</div>
                            </td>
                            <td class="py-4">
                                <div class="text-gray-400 dark:text-gray-500">-</div>
                                <div class="text-sm text-gray-400 dark:text-gray-500">-</div>
                            </td>
                            <td class="py-4 text-gray-400 dark:text-gray-500">-</td>
                            <td class="py-4">
                                <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded-full dark:bg-gray-700 dark:text-gray-200">
                                    Restricted
                                </span>
                            </td>
                            <td class="py-4">
                                <span class="text-gray-400 text-sm">View ID Only</span>
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
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Executive Notifications</h3>
            </div>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                <div class="flex items-start gap-4 p-4 bg-blue-50 rounded-lg dark:bg-blue-900/20">
                    <flux:icon name="document-text" class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5" />
                    <div class="flex-1">
                        <div class="font-medium text-gray-900 dark:text-white">New load request received</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">From Marketing Associate - Urgent shipment Nairobi→Mombasa</div>
                        <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">30 minutes ago</div>
                    </div>
                </div>

                <div class="flex items-start gap-4 p-4 bg-green-50 rounded-lg dark:bg-green-900/20">
                    <flux:icon name="truck" class="w-5 h-5 text-green-600 dark:text-green-400 mt-0.5" />
                    <div class="flex-1">
                        <div class="font-medium text-gray-900 dark:text-white">Truck availability update</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Procurement Associate added 15 new refrigerated trucks</div>
                        <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">2 hours ago</div>
                    </div>
                </div>

                <div class="flex items-start gap-4 p-4 bg-amber-50 rounded-lg dark:bg-amber-900/20">
                    <flux:icon name="currency-dollar" class="w-5 h-5 text-amber-600 dark:text-amber-400 mt-0.5" />
                    <div class="flex-1">
                        <div class="font-medium text-gray-900 dark:text-white">Payment processing required</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">5 invoices awaiting executive approval for payment</div>
                        <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">4 hours ago</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Notice -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 dark:bg-blue-900/20 dark:border-blue-700/30">
        <div class="flex items-center gap-3">
            <flux:icon name="information-circle" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
            <div>
                <h4 class="font-semibold text-blue-800 dark:text-blue-200">System Management Notice</h4>
                <p class="text-blue-700 dark:text-blue-300 mt-1">
                    Load numbers generated are permanent records and cannot be deleted once created. 
                    All served loads are maintained for tracking, payment processing, and future references.
                </p>
            </div>
        </div>
    </div>
</div>
</div>
