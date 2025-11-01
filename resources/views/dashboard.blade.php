<x-layouts.app :title="__('Dashboard')">
    {{-- <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="grid auto-rows-min gap-4 md:grid-cols-4">
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>            
        </div>
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
        </div>
    </div> --}}
    <div class="p-6 space-y-6">
    <!-- Header Section -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Carrier Onboarding</h1>
            <p class="text-gray-600 dark:text-gray-400">Complete your profile to start accepting loads</p>
        </div>
        <div class="flex gap-3">
            <button class="px-4 py-2 bg-lime-500 text-white rounded-lg hover:bg-lime-600 transition-colors flex items-center gap-2">
                <flux:icon name="truck" class="w-5 h-5" />
                Upload Vehicles
            </button>
            <button class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors flex items-center gap-2">
                <flux:icon name="document-arrow-up" class="w-5 h-5" />
                Upload Documents
            </button>
        </div>
    </div>

    <!-- Form Completion Status -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
<!-- Directors Information Card -->
{{-- <x-card.status-progress
    title="Directors"
    icon="users"
    iconColor="lime"
    completionPercentage="100"
    completionText="3/3"
    :statusItems="[
        ['label' => 'Basic Information', 'status' => 'completed'],
        ['label' => 'Identification', 'status' => 'completed'],
        ['label' => 'Background Check', 'status' => 'completed'],
    ]"
    showButton="false"
>
    <!-- Add the Livewire component for director creation -->
    <livewire:director.create />
</x-card.status-progress> --}}

<livewire:carrier.director.status-info />


        <!-- Fleet Information Status -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-gray-200 dark:border-slate-700">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center dark:bg-blue-900">
                        <flux:icon name="truck" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Fleet Information</h3>
                </div>
                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full dark:bg-blue-900 dark:text-blue-200">
                    75%
                </span>
            </div>
            
            <!-- Progress Bar -->
            <div class="mb-4">
                <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-1">
                    <span>Completion</span>
                    <span>3/4</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                    <div class="bg-blue-500 h-2 rounded-full" style="width: 75%"></div>
                </div>
            </div>

            <div class="space-y-2">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Vehicle Details</span>
                    <flux:icon name="check-circle" class="w-4 h-4 text-green-500" />
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Insurance</span>
                    <flux:icon name="check-circle" class="w-4 h-4 text-green-500" />
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Registration</span>
                    <flux:icon name="check-circle" class="w-4 h-4 text-green-500" />
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Safety Records</span>
                    <flux:icon name="clock" class="w-4 h-4 text-amber-500" />
                </div>
            </div>

            <button class="w-full mt-4 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                <flux:icon name="pencil-square" class="w-4 h-4 inline mr-2" />
                Complete Section
            </button>
        </div>

        <!-- Trade References Status -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-gray-200 dark:border-slate-700">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center dark:bg-amber-900">
                        <flux:icon name="building-storefront" class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Trade References</h3>
                </div>
                <span class="px-2 py-1 bg-amber-100 text-amber-800 text-xs rounded-full dark:bg-amber-900 dark:text-amber-200">
                    40%
                </span>
            </div>
            
            <!-- Progress Bar -->
            <div class="mb-4">
                <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-1">
                    <span>Completion</span>
                    <span>2/5</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                    <div class="bg-amber-500 h-2 rounded-full" style="width: 40%"></div>
                </div>
            </div>

            <div class="space-y-2">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Reference 1</span>
                    <flux:icon name="check-circle" class="w-4 h-4 text-green-500" />
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Reference 2</span>
                    <flux:icon name="check-circle" class="w-4 h-4 text-green-500" />
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Reference 3</span>
                    <flux:icon name="clock" class="w-4 h-4 text-amber-500" />
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Reference 4</span>
                    <div class="w-4 h-4">
                        <x-placeholder-pattern class="w-full h-full text-gray-400" />
                    </div>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Reference 5</span>
                    <div class="w-4 h-4">
                        <x-placeholder-pattern class="w-full h-full text-gray-400" />
                    </div>
                </div>
            </div>

            <button class="w-full mt-4 px-4 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition-colors">
                <flux:icon name="plus-circle" class="w-4 h-4 inline mr-2" />
                Add References
            </button>
        </div>
    </div>

    <!-- Upload Truck Loads Section -->
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700">
        <div class="p-6 border-b border-gray-200 dark:border-slate-700">
            <div class="flex items-center gap-3">
                <flux:icon name="cloud-arrow-up" class="w-6 h-6 text-lime-600 dark:text-lime-400" />
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Upload Truck Loads</h3>
            </div>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Upload your vehicle requirements to get matched with available loads</p>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- CSV Upload Area -->
                <div class="space-y-4">
                    <h4 class="font-medium text-gray-900 dark:text-white">Bulk Upload</h4>
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center dark:border-slate-600 hover:border-lime-400 transition-colors cursor-pointer">
                        <flux:icon name="document-arrow-up" class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Upload CSV File</h4>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">
                            Upload a CSV file with your vehicle specifications and load requirements
                        </p>
                        <div class="flex gap-3 justify-center">
                            <button class="px-4 py-2 bg-lime-500 text-white rounded-lg hover:bg-lime-600 transition-colors">
                                Choose File
                            </button>
                            <button class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 dark:border-slate-600 dark:text-gray-300 dark:hover:bg-slate-700">
                                Download Template
                            </button>
                        </div>
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        <flux:icon name="information-circle" class="w-4 h-4 inline mr-1" />
                        Supported formats: CSV, XLSX (Max 10MB)
                    </div>
                </div>

                <!-- Manual Entry Form -->
                <div class="space-y-4">
                    <h4 class="font-medium text-gray-900 dark:text-white">Manual Entry</h4>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Vehicle Type
                                </label>
                                <select class="w-full border border-gray-300 rounded-lg px-3 py-2 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                                    <option>Dry Van</option>
                                    <option>Refrigerated</option>
                                    <option>Flatbed</option>
                                    <option>Tanker</option>
                                    <option>Container</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Capacity (tons)
                                </label>
                                <input type="number" class="w-full border border-gray-300 rounded-lg px-3 py-2 dark:bg-slate-700 dark:border-slate-600 dark:text-white" placeholder="e.g., 25">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Available From
                                </label>
                                <input type="date" class="w-full border border-gray-300 rounded-lg px-3 py-2 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Service Area
                                </label>
                                <select class="w-full border border-gray-300 rounded-lg px-3 py-2 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                                    <option>Local</option>
                                    <option>Regional</option>
                                    <option>National</option>
                                    <option>International</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Special Requirements
                            </label>
                            <textarea rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 dark:bg-slate-700 dark:border-slate-600 dark:text-white" placeholder="Any special equipment or requirements..."></textarea>
                        </div>

                        <button class="w-full bg-lime-500 text-white py-3 rounded-lg hover:bg-lime-600 transition-colors font-medium flex items-center justify-center gap-2">
                            <flux:icon name="plus-circle" class="w-5 h-5" />
                            Add Load Requirement
                        </button>
                    </div>
                </div>
            </div>

            <!-- Recent Uploads -->
            <div class="mt-8 pt-6 border-t border-gray-200 dark:border-slate-700">
                <h4 class="font-medium text-gray-900 dark:text-white mb-4">Recent Uploads</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg dark:bg-slate-700">
                        <flux:icon name="document-text" class="w-8 h-8 text-blue-500" />
                        <div>
                            <div class="font-medium text-gray-900 dark:text-white">fleet_vehicles.csv</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Uploaded 2 hours ago</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg dark:bg-slate-700">
                        <flux:icon name="document-text" class="w-8 h-8 text-green-500" />
                        <div>
                            <div class="font-medium text-gray-900 dark:text-white">load_requirements.csv</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Uploaded 1 day ago</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg dark:bg-slate-700">
                        <div class="w-8 h-8">
                            <x-placeholder-pattern class="w-full h-full text-gray-400" />
                        </div>
                        <div>
                            <div class="font-medium text-gray-900 dark:text-white">No recent files</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Upload your first file</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    
</x-layouts.app>
