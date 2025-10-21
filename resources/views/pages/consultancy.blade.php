<x-layouts.app>
    <div class="">

@php
    // Define the consultancy services data with relevant Heroicons
    $services = [
        [
            'title' => 'Start & Manage a Transport Company',
            'icon' => 'truck', // Heroicon-o-truck
            'list' => ['Running a Passenger Company', 'Running a Freight Company', 'Company Setup'],
            'image_alt' => 'How to start and manage a transport company',
            'service_name' => 'Transport Company'
        ],
        [
            'title' => 'Extended Freight Forwarding Services',
            'icon' => 'globe-alt', // Heroicon-o-globe-alt
            'list' => ['Customs Clearance', 'Vital Documentation', 'Documentation Identification', 'Arrangements on Your Behalf'],
            'image_alt' => 'Extended freight forwarding services',
            'service_name' => 'Extended Freight Forwarding Services'
        ],
        [
            'title' => 'Driver Management & Training',
            'icon' => 'user-group', // Heroicon-o-user-group
            'list' => ['Ability Training', 'Aptitude Training', 'Attitude Training'],
            'image_alt' => 'Driver management and training',
            'service_name' => 'Driver Management'
        ],
        [
            'title' => 'Licences and Permits Assistance',
            'icon' => 'document-check', // Heroicon-o-document-check
            'list' => ['Operator\'s Licence & Permits', 'Route Authority', 'Vehicle Licence Acquisition'],
            'image_alt' => 'Assistance to acquire licences and permits',
            'service_name' => 'Licences and Permits'
        ],
        [
            'title' => 'Market Research',
            'icon' => 'magnifying-glass-chart', // Heroicon-o-magnifying-glass-chart
            'list' => ['Opportunity Awareness', 'Environment Threats Analysis', 'Strategy Formulation', 'Strategy Implementation & Control'],
            'image_alt' => 'Logistics market research',
            'service_name' => 'Market Research'
        ],
        [
            'title' => 'Training Services',
            'icon' => 'academic-cap', // Heroicon-o-academic-cap
            'list' => ['Transport Managers', 'Drivers', 'Supervisors', 'Office Bearers', 'Transport Practitioners'],
            'image_alt' => 'Training services for transport professionals',
            'service_name' => 'Training'
        ],
    ];
@endphp

        <section id="consultancy" class="py-16 bg-gray-50 dark:bg-gray-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <div class="text-center mb-12 p-8 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-indigo-100 dark:border-indigo-900">
                    <h2 class="text-4xl font-extrabold text-gray-900 dark:text-white sm:text-5xl flex items-center justify-center">
                        <flux:icon.chart-bar-square class="w-10 h-10 mr-3 text-red-600" />
                        Transport Consultancy
                    </h2>
                    <p class="mt-4 text-lg text-gray-600 dark:text-gray-400 max-w-3xl mx-auto">
                        Transpartner Logistics' highly motivated and experienced team is on hot standby to impart a competitive edge by offering a wide range of transport consultancy services.
                        Be it starting and managing freight or passenger companies, driver management, extended freight forwarding, market research, or training services; **we are ready for you.**
                    </p>
                    <h3 class="mt-6 text-xl font-semibold text-indigo-600 dark:text-indigo-400">
                        Talk to us today! Simply click on a service to initiate a quick-form, and we will contact you.
                    </h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    
                    @foreach ($services as $service)
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl transition duration-300 ease-in-out transform hover:scale-[1.02] hover:shadow-2xl overflow-hidden group">
                            
                            {{-- Card Header / Image Placeholder (Using Heroicon for visual appeal, as real images were local) --}}
                            <div class="p-6 bg-indigo-600 dark:bg-indigo-700 text-white flex items-center justify-center h-40">
                                {{-- <x-dynamic-component 
                                    :component="$service['icon']" 
                                    class="w-20 h-20 text-indigo-200 dark:text-indigo-300 group-hover:animate-pulse" 
                                /> --}}
                            </div>

                            {{-- Card Body --}}
                            <div class="p-6">
                                <h4 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">
                                    {{ $service['title'] }}
                                </h4>
                                
                                <p class="text-sm font-semibold text-indigo-600 dark:text-indigo-400 mb-4 uppercase tracking-wider">
                                    Services Include:
                                </p>
                                
                                <ul class="space-y-2 text-gray-700 dark:text-gray-300 list-disc list-inside ml-2">
                                    @foreach ($service['list'] as $item)
                                        <li class="text-sm">{{ $item }}</li>
                                    @endforeach
                                </ul>
                            </div>

                            {{-- Card Footer / Action Button --}}
                            <div class="p-6 pt-0 border-t border-gray-100 dark:border-gray-700">
                                @auth
                                    {{-- Authenticated User: Use a button to trigger the modal (assuming you handle the modal/JS separately, as done in the original) --}}
                                    <button 
                                        class="w-full inline-flex justify-center items-center px-4 py-3 border border-transparent text-base font-medium rounded-lg shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-150"
                                        data-toggle="modal"
                                        data-target="#consulModal"
                                        data-service="{{ $service['service_name'] }}"
                                    >
                                        <flux:icon.chat-bubble-bottom-center-text class="w-5 h-5 mr-2" />
                                        Click to Enquire about {{ $service['service_name'] }}
                                    </button>
                                @else
                                    {{-- Guest User: Redirect to login/enquiry page --}}
                                    <a href="/login-for-consultancy" 
                                        class="w-full inline-flex justify-center items-center px-4 py-3 border border-transparent text-base font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150"
                                    >
                                        <flux:icon.lock-closed class="w-5 h-5 mr-2" />
                                        Login to Enquire
                                    </a>
                                @endauth
                            </div>
                        </div>
                    @endforeach

                </div>
            </div>
        </section>        
    </div>
</x-layouts.app>