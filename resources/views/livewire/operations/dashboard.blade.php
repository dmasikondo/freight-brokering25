<div class="p-6 space-y-6">
    <!-- Header Section -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Operations Logistics Associate</h1>
            <p class="text-gray-600 dark:text-gray-400">Manage carriers and shippers in your sales territory</p>
            <div class="flex items-center gap-2 mt-1">
                <flux:icon name="map-pin" class="w-4 h-4 text-lime-600 dark:text-lime-400" />
                <span class="text-sm font-medium text-lime-600 dark:text-lime-400">Territory: East Africa Region</span>
            </div>
        </div>
        <div class="flex gap-3">
            <button class="px-4 py-2 bg-lime-500 text-white rounded-lg hover:bg-lime-600 transition-colors flex items-center gap-2">
                <flux:icon name="user-plus" class="w-5 h-5" />
                Register Shipper
            </button>
            <button class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors flex items-center gap-2">
                <flux:icon name="truck" class="w-5 h-5" />
                Register Carrier
            </button>
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
            <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">32</div>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                <span class="text-green-600 font-semibold">+5</span> this month
            </div>
        </div>

        <!-- Total Carriers -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-gray-200 dark:border-slate-700">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Carriers</h3>
                <flux:icon name="building-library" class="w-6 h-6 text-green-500" />
            </div>
            <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">24</div>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                <span class="text-green-600 font-semibold">+3</span> this month
            </div>
        </div>

        <!-- Active Shipments -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-gray-200 dark:border-slate-700">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Active Shipments</h3>
                <flux:icon name="truck" class="w-6 h-6 text-amber-500" />
            </div>
            <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">18</div>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                In your territory
            </div>
        </div>

        <!-- Territory Performance -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-gray-200 dark:border-slate-700">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Performance</h3>
                <flux:icon name="chart-bar" class="w-6 h-6 text-purple-500" />
            </div>
            <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">94%</div>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                On-time delivery rate
            </div>
        </div>
    </div>

    <!-- Quick Actions Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <!-- Shipper Management -->
        <button class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 hover:border-blue-400 transition-colors group">
            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-blue-200 transition-colors dark:bg-blue-900">
                <flux:icon name="user-plus" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
            </div>
            <div class="text-left">
                <div class="font-semibold text-gray-900 dark:text-white">Register Shipper</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Add new client</div>
            </div>
        </button>

        <!-- Carrier Management -->
        <button class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 hover:border-green-400 transition-colors group">
            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-green-200 transition-colors dark:bg-green-900">
                <flux:icon name="truck" class="w-6 h-6 text-green-600 dark:text-green-400" />
            </div>
            <div class="text-left">
                <div class="font-semibold text-gray-900 dark:text-white">Register Carrier</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Add new carrier</div>
            </div>
        </button>

        <!-- Create Shipment -->
        <button class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 hover:border-lime-400 transition-colors group">
            <div class="w-12 h-12 bg-lime-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-lime-200 transition-colors dark:bg-lime-900">
                <flux:icon name="document-plus" class="w-6 h-6 text-lime-600 dark:text-lime-400" />
            </div>
            <div class="text-left">
                <div class="font-semibold text-gray-900 dark:text-white">Create Shipment</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">New logistics order</div>
            </div>
        </button>

        <!-- Post Availability -->
        <button class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 hover:border-purple-400 transition-colors group">
            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-purple-200 transition-colors dark:bg-purple-900">
                <flux:icon name="clipboard-document-list" class="w-6 h-6 text-purple-600 dark:text-purple-400" />
            </div>
            <div class="text-left">
                <div class="font-semibold text-gray-900 dark:text-white">Post Availability</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Truck details</div>
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
                            <h4 class="font-medium text-gray-900 dark:text-white">Kenya Exporters Ltd</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Nairobi, Kenya • Registered: 2 days ago</p>
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
                            <h4 class="font-medium text-gray-900 dark:text-white">Tanzania Traders Co</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Dar es Salaam • Registered: 1 week ago</p>
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
                            <h4 class="font-medium text-gray-900 dark:text-white">Uganda Manufacturers</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Kampala, Uganda • Registered: 2 weeks ago</p>
                        </div>
                    </div>
                    <span class="px-2 py-1 bg-amber-100 text-amber-800 text-xs rounded-full dark:bg-amber-900 dark:text-amber-200">
                        Pending
                    </span>
                </div>
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
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg dark:bg-slate-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center dark:bg-green-900">
                            <flux:icon name="truck" class="w-5 h-5 text-green-600 dark:text-green-400" />
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white">East Africa Logistics</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Mombasa, Kenya • Registered: 3 days ago</p>
                        </div>
                    </div>
                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full dark:bg-green-900 dark:text-green-200">
                        Complete
                    </span>
                </div>

                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg dark:bg-slate-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center dark:bg-green-900">
                            <flux:icon name="truck" class="w-5 h-5 text-green-600 dark:text-green-400" />
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white">Tanzania Haulers</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Arusha, Tanzania • Registered: 1 week ago</p>
                        </div>
                    </div>
                    <span class="px-2 py-1 bg-amber-100 text-amber-800 text-xs rounded-full dark:bg-amber-900 dark:text-amber-200">
                        Incomplete
                    </span>
                </div>

                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg dark:bg-slate-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center dark:bg-green-900">
                            <flux:icon name="truck" class="w-5 h-5 text-green-600 dark:text-green-400" />
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white">Rwanda Transport</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Kigali, Rwanda • Registered: 2 weeks ago</p>
                        </div>
                    </div>
                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full dark:bg-green-900 dark:text-green-200">
                        Complete
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Territory Shipments -->
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700">
        <div class="p-6 border-b border-gray-200 dark:border-slate-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Active Shipments in Territory</h3>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Currently active shipments within East Africa Region</p>
        </div>
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-slate-700">
                            <th class="text-left py-3 text-sm font-medium text-gray-600 dark:text-gray-400">Shipment ID</th>
                            <th class="text-left py-3 text-sm font-medium text-gray-600 dark:text-gray-400">Route</th>
                            <th class="text-left py-3 text-sm font-medium text-gray-600 dark:text-gray-400">Shipper</th>
                            <th class="text-left py-3 text-sm font-medium text-gray-600 dark:text-gray-400">Carrier</th>
                            <th class="text-left py-3 text-sm font-medium text-gray-600 dark:text-gray-400">Status</th>
                            <th class="text-left py-3 text-sm font-medium text-gray-600 dark:text-gray-400">ETA</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
                        <tr>
                            <td class="py-4 font-medium text-gray-900 dark:text-white">TRK-8923</td>
                            <td class="py-4">
                                <div class="text-gray-900 dark:text-white">Nairobi → Kampala</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Dry Van • 24 tons</div>
                            </td>
                            <td class="py-4 text-gray-600 dark:text-gray-400">Kenya Exporters Ltd</td>
                            <td class="py-4 text-gray-600 dark:text-gray-400">East Africa Logistics</td>
                            <td class="py-4">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full dark:bg-blue-900 dark:text-blue-200">
                                    In Transit
                                </span>
                            </td>
                            <td class="py-4 text-gray-600 dark:text-gray-400">Tomorrow</td>
                        </tr>
                        <tr>
                            <td class="py-4 font-medium text-gray-900 dark:text-white">TRK-8917</td>
                            <td class="py-4">
                                <div class="text-gray-900 dark:text-white">Dar es Salaam → Mombasa</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Refrigerated • 18 tons</div>
                            </td>
                            <td class="py-4 text-gray-600 dark:text-gray-400">Tanzania Traders Co</td>
                            <td class="py-4 text-gray-600 dark:text-gray-400">Tanzania Haulers</td>
                            <td class="py-4">
                                <span class="px-2 py-1 bg-amber-100 text-amber-800 text-xs rounded-full dark:bg-amber-900 dark:text-amber-200">
                                    Loading
                                </span>
                            </td>
                            <td class="py-4 text-gray-600 dark:text-gray-400">Dec 28</td>
                        </tr>
                        <tr>
                            <td class="py-4 font-medium text-gray-900 dark:text-white">TRK-8904</td>
                            <td class="py-4">
                                <div class="text-gray-900 dark:text-white">Kigali → Nairobi</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Flatbed • 32 tons</div>
                            </td>
                            <td class="py-4 text-gray-600 dark:text-gray-400">Uganda Manufacturers</td>
                            <td class="py-4 text-gray-600 dark:text-gray-400">Rwanda Transport</td>
                            <td class="py-4">
                                <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full dark:bg-green-900 dark:text-green-200">
                                    Delivered
                                </span>
                            </td>
                            <td class="py-4 text-gray-600 dark:text-gray-400">Today</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Territory Management -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Truck Availability -->
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700">
            <div class="p-6 border-b border-gray-200 dark:border-slate-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Truck Availability in Territory</h3>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg border border-green-200 dark:bg-green-900/20 dark:border-green-700/30">
                    <div class="flex items-center gap-3">
                        <flux:icon name="truck" class="w-8 h-8 text-green-600 dark:text-green-400" />
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white">Dry Van Trailers</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Available: 22 trucks</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="font-semibold text-green-600">Available</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Across territory</div>
                    </div>
                </div>

                <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg border border-blue-200 dark:bg-blue-900/20 dark:border-blue-700/30">
                    <div class="flex items-center gap-3">
                        <flux:icon name="truck" class="w-8 h-8 text-blue-600 dark:text-blue-400" />
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white">Refrigerated</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Available: 14 trucks</p>
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
                            <p class="text-sm text-gray-600 dark:text-gray-400">Available: 8 trucks</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="font-semibold text-amber-600">Limited</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">High demand</div>
                    </div>
                </div>
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
                <div class="flex items-start gap-4 p-4 bg-blue-50 rounded-lg dark:bg-blue-900/20">
                    <flux:icon name="map-pin" class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5" />
                    <div class="flex-1">
                        <div class="font-medium text-gray-900 dark:text-white">New shipper registered</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Ethiopia Coffee Exporters in Addis Ababa</div>
                        <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">2 hours ago</div>
                    </div>
                </div>

                <div class="flex items-start gap-4 p-4 bg-green-50 rounded-lg dark:bg-green-900/20">
                    <flux:icon name="truck" class="w-5 h-5 text-green-600 dark:text-green-400 mt-0.5" />
                    <div class="flex-1">
                        <div class="font-medium text-gray-900 dark:text-white">Carrier capacity increased</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">East Africa Logistics added 5 new trucks</div>
                        <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">1 day ago</div>
                    </div>
                </div>

                <div class="flex items-start gap-4 p-4 bg-amber-50 rounded-lg dark:bg-amber-900/20">
                    <flux:icon name="exclamation-triangle" class="w-5 h-5 text-amber-600 dark:text-amber-400 mt-0.5" />
                    <div class="flex-1">
                        <div class="font-medium text-gray-900 dark:text-white">Border delay alert</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Increased clearance time at Kenya-Tanzania border</div>
                        <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">2 days ago</div>
                    </div>
                </div>
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