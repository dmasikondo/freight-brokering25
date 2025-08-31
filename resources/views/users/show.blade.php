<x-layouts.app :title="__('User Profile')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div
            x-data="{ 
                hasOrganisation: '{{ $user?->organisation }}',
                hasWhatsapp: '{{ $user->whatsapp }}',
                hasCreator: '{{ $user->createdBy?->first()->contact_person }}',
                hasLocation: '{{ $user->buslocation->first()?->city }}',
                isShipper: '{{ $user->hasRole('shipper') }}',
                isCarrier: '{{ $user->hasRole('carrier') }}',
                isMarketing: '{{ $user->hasRole('marketing logistics associate') }}',          
                isProcurementLA: '{{ $user->hasRole('procurement logistics associate') }}',          
                isOperationsLA: '{{ $user->hasRole('operations logistics associate') }}',
                activeTab: 'activity' // Default active tab is 'activity'          
            }" 
            class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-4xl w-full p-8 transition-all duration-300 animate-fade-in"
        >
            <div class="flex flex-col md:flex-row gap-8">
                <!-- User Profile Section -->
                <div class="md:w-1/3 text-center mb-8 md:mb-0 space-y-4">
                    <div class="rounded-full w-48 h-48 mx-auto mb-4 border-4 border-indigo-800 dark:border-blue-900 transition-transform duration-300 hover:scale-105">
                        <x-graphic name="user" class=" size-44"/>
                    </div>
                    
                    <h1 class="text-2xl font-bold text-indigo-800 dark:text-white">{{$user->contact_person}}</h1>
                    <p class="text-gray-600 dark:text-gray-300 text-xs font-thin">{{$user?->identificationNumber}}</p>
                    
                    <!-- Role Badges Section -->
                    <div class="flex flex-col items-center justify-center space-y-2 text-gray-600 dark:text-gray-300">
                        @foreach ($user->roles as $role)
                            @php
                                $icon = match($role->name) {
                                    'marketing logistics associate' => 'megaphone',
                                    'procurement logistics associate' => 'clipboard-document-list',
                                    'operations logistics associate' => 'cursor-arrow-ripple',
                                    'admin' => 'cog-6-tooth',
                                    'superadmin' => 'lock-closed',
                                    'carrier' => 'truck',
                                    'shipper' => 'cube',
                                    default => 'user-circle' // Default icon
                                };
                            @endphp
                            <p class="font-medium flex items-center justify-center gap-2">
                                <flux:icon. :name="$icon" class="size-5 text-indigo-800 dark:text-blue-900" />
                                {{ Str::title($role->name) }} 
                            </p>
                            @if ($role->pivot?->classification)
                                @php
                                    $classificationIcon = match ($role->pivot->classification) {
                                        'real_owner' => 'shield-check',
                                        'broker_agent' => 'exchange',
                                        default => ''
                                    };
                                    $classificationText = $role->pivot->classification === 'real_owner' ? 'Real Owner' : 'Broker / Agent';
                                    $iconColor = $role->pivot->classification === 'real_owner' ? 'text-yellow-400' : 'text-blue-400';
                                @endphp
                                <p class="text-sm font-light text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                    <x-graphic :name="$classificationIcon" class="size-4 {{ $iconColor }}" />
                                    {{ $classificationText }}
                                </p>
                            @endif
                        @endforeach
                    </div>
                    
                    <button class="w-full mt-4 bg-indigo-800 text-white px-4 py-2 rounded-lg hover:bg-blue-900 transition-colors duration-300">Edit Profile</button>
                    <p x-show="hasCreator" x-text="'Created By: ' + hasCreator" class="text-gray-400 text-xs font-extralight"></p>
                </div>
                
                <!-- Information Tabs Section -->
                <div class="md:w-2/3 md:pl-8">
                    <!-- Tab Navigation -->
                    <div class="flex relative mb-6">
                        <button
                            @click="activeTab = 'activity'"
                            :class="{ 'z-10 bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-lg border-b-2 border-r-2 border-indigo-600 dark:border-indigo-400': activeTab === 'activity' }"
                            class="relative -mr-px py-2 px-6 font-semibold text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors duration-200"
                        >
                            Activity
                        </button>
                        <button
                            @click="activeTab = 'contact'"
                            :class="{ 'z-10 bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-lg border-b-2 border-l-2 border-indigo-600 dark:border-indigo-400': activeTab === 'contact' }"
                            class="relative -ml-px py-2 px-6 font-semibold text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors duration-200"
                        >
                            Contact Info
                        </button>
                        <button
                            @click="activeTab = 'territory'"
                            :class="{ 'z-10 bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-lg border-b-2 border-l-2 border-indigo-600 dark:border-indigo-400': activeTab === 'territory' }"
                            class="relative -ml-px py-2 px-6 font-semibold text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors duration-200"
                        >
                            Territory
                        </button>                        
                        <div class="absolute bottom-0 left-0 w-full h-px bg-gray-200 dark:bg-gray-700"></div>
                    </div>
                    
                    <!-- Tab Content -->
                    <div x-show="activeTab === 'activity'" class="space-y-6">
                        @php
                            // Dynamic color palette for badges
                            $badgePalette = [
                                ['bg-blue-100', 'text-blue-800'],
                                ['bg-orange-100', 'text-orange-800'],
                                ['bg-green-100', 'text-green-800'],
                                ['bg-purple-100', 'text-purple-800'],
                                ['bg-gray-100', 'text-gray-800'],
                            ];
                            $getRandomColor = fn() => $badgePalette[array_rand($badgePalette)];
                        @endphp
                        
                        <!-- Shipping Activity Section -->
                        <template x-if="isShipper || isMarketing || isOperationsLA">
                            <div>
                                <h2 class="text-xl font-semibold text-indigo-800 dark:text-white mb-4">Shipping Activity</h2>
                                <div class="flex flex-wrap gap-2">
                                    <span class="{{ implode(' ', $getRandomColor()) }} px-3 py-1 rounded-full text-sm">Sent</span>
                                    <span class="{{ implode(' ', $getRandomColor()) }} px-3 py-1 rounded-full text-sm">In Transit</span>
                                    <span class="{{ implode(' ', $getRandomColor()) }} px-3 py-1 rounded-full text-sm">Delivered</span>
                                    <span class="{{ implode(' ', $getRandomColor()) }} px-3 py-1 rounded-full text-sm">Awaiting Approval</span>
                                    <span class="{{ implode(' ', $getRandomColor()) }} px-3 py-1 rounded-full text-sm">Draft</span>
                                    <template x-if="isMarketing || isOperationsLA">
                                        <span class="{{ implode(' ', $getRandomColor()) }} px-3 py-1 rounded-full text-sm" title="Total No. of Shippers">
                                            Registered ( {{ $user->createdUsers->count() }} )
                                        </span>
                                        <span class="{{ implode(' ', $getRandomColor()) }} px-3 py-1 rounded-full text-sm" title="Total No. of Invoices">Invoices</span>
                                        <span class="{{ implode(' ', $getRandomColor()) }} px-3 py-1 rounded-full text-sm" title="Pending Invoices">Pending</span>
                                        <span class="{{ implode(' ', $getRandomColor()) }} px-3 py-1 rounded-full text-sm" title="Overdue Invoices">Overdue</span>
                                        <span class="{{ implode(' ', $getRandomColor()) }} px-3 py-1 rounded-full text-sm" title="Paid Invoices">Paid</span>
                                    </template>
                                </div>
                            </div>
                        </template>
                        
                        <!-- Carrier Activity Section -->
                        <template x-if="isCarrier || isProcurementLA || isOperationsLA">
                            <div>
                                <h2 class="text-xl font-semibold text-indigo-800 dark:text-white mb-4">Carrier Activity</h2>
                                <div class="flex flex-wrap gap-2">
                                    <template x-if="isCarrier">
                                        <span class="{{ implode(' ', $getRandomColor()) }} px-3 py-1 rounded-full text-sm">Directors Info</span>
                                        <span class="{{ implode(' ', $getRandomColor()) }} px-3 py-1 rounded-full text-sm">Trade References</span>
                                        <span class="{{ implode(' ', $getRandomColor()) }} px-3 py-1 rounded-full text-sm">Fleet Info</span>
                                        <span class="{{ implode(' ', $getRandomColor()) }} px-3 py-1 rounded-full text-sm">Bids</span>
                                    </template>
                                    <template x-if="isProcurementLA || isOperationsLA">
                                        <span class="{{ implode(' ', $getRandomColor()) }} px-3 py-1 rounded-full text-sm" title="Total No. of Carriers">Registered 
                                            ( {{ $user->createdUsers->count() }} )
                                        </span>
                                        <span class="{{ implode(' ', $getRandomColor()) }} px-3 py-1 rounded-full text-sm" title="Incomplete Registrations">Incomplete</span>
                                        <span class="{{ implode(' ', $getRandomColor()) }} px-3 py-1 rounded-full text-sm" title="No. of Published Vehicles">Vehicles</span>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                    
                    <!-- Contact Information Section -->
                    <div x-show="activeTab === 'contact'">
                        <template x-if="hasOrganisation">
                            <div class="flex items-center gap-2 mb-4">
                                <x-graphic name="building-office-2" class="size-5 text-yellow-400"/>
                                <span x-text="hasOrganisation" class="text-gray-400 text-xl dark:text-white"></span>
                            </div>
                        </template> 
                        
                        <div class="bg-gray-100 dark:bg-gray-700 p-4 rounded-lg shadow-inner">
                            <ul class="space-y-4 text-gray-700 dark:text-gray-300">
                                <li class="flex items-center gap-3">
                                    <x-graphic name="mail" class="size-5 text-red-400 dark:text-red-300"/>          
                                    <div class="flex flex-col">
                                        <span class="font-semibold text-sm text-gray-500 dark:text-gray-400">Email</span>
                                        <span class="font-medium">{{ $user->email }}</span>
                                    </div>
                                </li>
                                <li class="flex items-center gap-3">
                                    <x-graphic name="phone" class="size-5 text-indigo-800 dark:text-blue-900"/> 
                                    <div class="flex flex-col">
                                        <span class="font-semibold text-sm text-gray-500 dark:text-gray-400">Phone</span>
                                        <span class="font-medium">{{ $user->phone_type}}: {{ $user->contact_phone }}</span>
                                    </div>
                                </li>
                                <template x-if="hasWhatsapp">
                                    <li class="flex items-center gap-3">
                                        <x-graphic name="whatsapp" class="size-5 text-green-800 dark:text-green-900"/> 
                                        <div class="flex flex-col">
                                            <span class="font-semibold text-sm text-gray-500 dark:text-gray-400">Whatsapp</span>
                                            <span class="font-medium">{{ $user->whatsapp }}</span>
                                        </div>
                                    </li> 
                                </template>
                                <template x-if="hasLocation">
                                    <li class="flex items-center gap-3">
                                        <x-graphic name="location-marker" class="size-5 text-green-400 dark:text-green-800"/> 
                                        <div class="flex flex-col">
                                            <span class="font-semibold text-sm text-gray-500 dark:text-gray-400">Address</span>
                                            <span class="font-medium">
                                                {{ $user->buslocation->first()?->address }},
                                                {{ $user->buslocation->first()?->city }},
                                                {{ $user->buslocation->first()?->country }}
                                            </span>
                                        </div>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>
                    {{-- territory section --}}
                        <div x-show="activeTab ==='territory'" class="space-y-4">
                            @livewire('territory.user-territory', ['createdUser' => $user->slug])

                            <livewire:users.assign_territory :createdUser="$user->slug"/>
                        </div>                       
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
