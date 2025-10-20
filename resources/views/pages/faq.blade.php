<x-layouts.app>
<section id="faq" class="py-16 sm:py-24 bg-gray-50 dark:bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex flex-col md:flex-row items-center justify-center p-8 mb-12 rounded-xl shadow-lg bg-indigo-600 dark:bg-indigo-800 text-white">
            <div class="md:w-1/4 flex-shrink-0 mb-4 md:mb-0 md:mr-6">
                {{-- Replace 'questions.jpg' with an icon or image placeholder --}}
                <div class="text-6xl text-indigo-200 dark:text-indigo-400">
                    ❓
                </div>
            </div>
            <div class="md:w-3/4 text-center md:text-left">
                <h1 class="text-4xl font-extrabold sm:text-5xl">
                    Frequently Asked Questions
                </h1>
                <h2 class="mt-2 text-xl font-light text-indigo-200 dark:text-indigo-300">
                    Find answers and general information quickly about Transpartner Logistics' operations and procedures.
                </h2>
            </div>
        </div>
        
        {{-- Use Alpine.js to handle the open/close state of the accordion --}}
        <div x-data="{ open: 1 }" class="space-y-6 max-w-4xl mx-auto">
            
            <div id="get_truck_load" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <button 
                    @click="open = (open === 1 ? null : 1)"
                    class="w-full flex justify-between items-center p-6 text-xl font-semibold text-left text-gray-900 dark:text-white hover:bg-indigo-50 dark:hover:bg-gray-700 transition duration-150 focus:outline-none"
                >
                    <span class="flex items-center">
                        <svg class="w-6 h-6 mr-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        How do I Get Available Truck Loads?
                    </span>
                    <span :class="{'transform rotate-90': open === 1, 'transform rotate-0': open !== 1}" class="transition-transform duration-300 text-indigo-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </span>
                </button>

                <div x-show="open === 1" x-collapse.duration.300ms class="p-6 border-t border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300">
                    <p class="mb-4">We allow our clients to find suitable freight to carry that suit your vehicle at competitive rates.</p>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li><span class="font-semibold">Register:</span> <a href="/register" class="text-indigo-600 dark:text-indigo-400 hover:underline">Register</a> for a transporter's account or <a href="/login" class="text-indigo-600 dark:text-indigo-400 hover:underline">login</a> if already registered.</li>
                        <li><span class="font-semibold">Navigate:</span> Go to the **Services** menu and select **Available Loads**.</li>
                    </ul>
                    <a href="/freights" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 transition duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Get Truck Loads Now!
                    </a>
                </div>
            </div>

            <div id="upload_consignment" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <button 
                    @click="open = (open === 2 ? null : 2)"
                    class="w-full flex justify-between items-center p-6 text-xl font-semibold text-left text-gray-900 dark:text-white hover:bg-indigo-50 dark:hover:bg-gray-700 transition duration-150 focus:outline-none"
                >
                    <span class="flex items-center">
                        <svg class="w-6 h-6 mr-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        How do I Place my Consignment for Carriage?
                    </span>
                    <span :class="{'transform rotate-90': open === 2, 'transform rotate-0': open !== 2}" class="transition-transform duration-300 text-indigo-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </span>
                </button>

                <div x-show="open === 2" x-collapse.duration.300ms class="p-6 border-t border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300">
                    <p class="mb-4">We connect shippers with vetted carriers in Zimbabwe and the SADC region at affordable, distance-covered rates.</p>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li><span class="font-semibold">Customer Account:</span> <a href="/register" class="text-indigo-600 dark:text-indigo-400 hover:underline">Register</a> as a shipping customer or <a href="/login" class="text-indigo-600 dark:text-indigo-400 hover:underline">login</a>.</li>
                        <li><span class="font-semibold">Upload:</span> Go to **Services** &rarr; **Freight Forwarding** &rarr; **Upload Consignment**.</li>
                        <li><span class="font-semibold">Agreement:</span> We will agree on payment details and assign your consignment to a hot-standby carrier.</li>
                    </ul>
                    <a href="/freights/create" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 transition duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Upload Consignment now
                    </a>
                </div>
            </div>

            <div id="insurance" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <button 
                    @click="open = (open === 3 ? null : 3)"
                    class="w-full flex justify-between items-center p-6 text-xl font-semibold text-left text-gray-900 dark:text-white hover:bg-indigo-50 dark:hover:bg-gray-700 transition duration-150 focus:outline-none"
                >
                    <span class="flex items-center">
                        <svg class="w-6 h-6 mr-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.047A12.003 12.003 0 003 12c0 2.755 1.11 5.234 2.938 7.062A11.91 11.91 0 0012 22c2.934 0 5.61-1.194 7.562-3.138A12.003 12.003 0 0021 12c0-3.097-1.047-5.88-2.382-8.016z"></path></svg>
                        What if I don't have insurance cover?
                    </span>
                    <span :class="{'transform rotate-90': open === 3, 'transform rotate-0': open !== 3}" class="transition-transform duration-300 text-indigo-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </span>
                </button>

                <div x-show="open === 3" x-collapse.duration.300ms class="p-6 border-t border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300">
                    <p class="mb-4">It is extremely important that your goods have **adequate insurance cover** whilst in storage or transit. We partner with reputable and experienced providers.</p>
                    <p>If you don't have cover, you can simply: <a href="/tracking-insurance" class="text-indigo-600 dark:text-indigo-400 hover:underline font-semibold">Contact us here</a> or navigate via **Services** &rarr; **Tracking and Insurance** &rarr; **Insurance Cover** and select "Ask more on Insurance?". We can arrange insurance for your goods.</p>
                </div>
            </div>

            <div id="tracking" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <button 
                    @click="open = (open === 4 ? null : 4)"
                    class="w-full flex justify-between items-center p-6 text-xl font-semibold text-left text-gray-900 dark:text-white hover:bg-indigo-50 dark:hover:bg-gray-700 transition duration-150 focus:outline-none"
                >
                    <span class="flex items-center">
                        <svg class="w-6 h-6 mr-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.55 4.55M19 19L5 5m14 0L5 19"></path></svg>
                        What tracking systems do you have?
                    </span>
                    <span :class="{'transform rotate-90': open === 4, 'transform rotate-0': open !== 4}" class="transition-transform duration-300 text-indigo-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </span>
                </button>

                <div x-show="open === 4" x-collapse.duration.300ms class="p-6 border-t border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300">
                    <p class="mb-4">We address almost all your tracking needs, with highly qualified engineers capable of designing systems to suit your unique requirements. Common requests include:</p>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li>Tracking Driving Behaviour</li>
                        <li>Tracking Fuel Consumption</li>
                        <li>Tracking Geographical Position (Real-time Location)</li>
                        <li>Load Security and Theft Prevention</li>
                        <li>Negligence Alerts (e.g., unauthorized stops, harsh braking)</li>
                    </ul>
                    <p class="mt-4">Ask more about <a href="/tracking-insurance" class="text-indigo-600 dark:text-indigo-400 hover:underline font-semibold">Tracking Systems?</a></p>
                </div>
            </div>
            
            <div id="why_registering" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <button 
                    @click="open = (open === 5 ? null : 5)"
                    class="w-full flex justify-between items-center p-6 text-xl font-semibold text-left text-gray-900 dark:text-white hover:bg-indigo-50 dark:hover:bg-gray-700 transition duration-150 focus:outline-none"
                >
                    <span class="flex items-center">
                        <svg class="w-6 h-6 mr-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        Why must I sign up to view additional resources?
                    </span>
                    <span :class="{'transform rotate-90': open === 5, 'transform rotate-0': open !== 5}" class="transition-transform duration-300 text-indigo-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </span>
                </button>

                <div x-show="open === 5" x-collapse.duration.300ms class="p-6 border-t border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300">
                    <p class="mb-4">We take secure browsing and safety very seriously. Signing up enables us to:</p>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li>Ensure we are dealing with **real, verified people** (security).</li>
                        <li>**Customise** our services and create a great user experience tailored to you.</li>
                        <li>Protect sensitive data (like available loads) from the public internet.</li>
                    </ul>
                    <p class="mt-4 text-sm">We use standard encryption technologies to ensure your details are safe and confidential. We **do not sell your data** to advertisers.</p>
                    
                    @if(!Auth::check()) 
                        <a href="/register" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 transition duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM12 17H5a2 2 0 00-2 2v2h16v-2a2 2 0 00-2-2z"></path></svg>
                            Register with Transpartner Logistics Now!
                        </a> 
                    @endif
                </div>
            </div>

        </div>
    </div>
</section>    
</x-layouts.app>