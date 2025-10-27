<x-layouts.app>
    <div class="">
        <main class="w-full">
            <!-- === HERO SECTION === -->
            <div id="home" class="py-16 md:py-24 bg-zinc-100 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid lg:grid-cols-12 gap-12 items-center">
                        <!-- Text Content -->
                        <div class="lg:col-span-6 space-y-6">
                            <flux:badge variant="outline" class="text-lg text-green-600 dark:text-green-400 border-green-300 dark:border-green-600">
                                Your Preferred Logistics Partner
                            </flux:badge>
                            <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-zinc-900 dark:text-white">
                                <span class="block">We pledge to assist </span>
                                <span class="block text-green-600 dark:text-green-400">Transport Companies in Southern Africa.</span>
                            </h1>
                            <p class="text-xl text-zinc-600 dark:text-zinc-400">
                                To start and manage freight and passenger companies, ensuring your goods move efficiently across Zimbabwe and the SADC region.
                            </p>
                            <div class="flex flex-col sm:flex-row gap-4">
                                {{-- FIX: Changed size="lg" to size="xl" to resolve UnhandledMatchError --}}
                                <flux:button variant="primary" size="xl" href="/consultancy" wire:navigate>
                                    Get Consultancy Today
                                    <flux:icon.arrow-right class="w-4 h-4 ml-2" />
                                </flux:button>
                                {{-- FIX: Changed size="lg" to size="xl" to resolve UnhandledMatchError --}}
                                <flux:button variant="ghost" size="xl" href="/about-us" wire:navigate>
                                    Learn More
                                </flux:button>
                            </div>
                        </div>

                        <!-- Image / Visual -->
                        <div class="lg:col-span-6 lg:flex lg:justify-end">
                            <div class="p-0 overflow-hidden w-full max-w-lg shadow-2xl transition-all hover:scale-[1.01] duration-300 rounded-xl">
                                <!-- Placeholder for the transport image -->
                                <img
                                    src="https://placehold.co/800x500/087a32/ffffff?text=Transport+Business"
                                    alt="Starting a transport business"
                                    class="object-cover w-full h-auto rounded-xl"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- --- -->

            <!-- === SERVICES / FEATURES SECTION === -->
            <div class="py-16 md:py-24">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center mb-12">
                        <h2 class="text-3xl font-bold text-zinc-900 dark:text-white mb-2">
                            Comprehensive Transport Solutions
                        </h2>
                        <p class="text-xl text-zinc-600 dark:text-zinc-400 max-w-3xl mx-auto">
                            We are a Southern Africa based Freight Forwarding and Transport Consultancy Company that offers extended logistics services.
                        </p>
                    </div>

                    <!-- Features Grid -->
                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">

                        <!-- Feature 1: Available Vehicles -->
                        <div class="p-6 h-full transition-all hover:shadow-lg hover:border-green-500/50 dark:hover:border-green-500/50">
                            <a href="/lanes" wire:navigate class="space-y-4 block">
                                <flux:icon.truck class="w-8 h-8 text-green-600 dark:text-green-400" />
                                <h3 class="text-xl font-semibold text-zinc-900 dark:text-white">Available Vehicles</h3>
                                <p class="text-zinc-600 dark:text-zinc-400">We have carefully vetted Carriers with locations restricted to Southern African (SADC) States, ready to move your goods.</p>
                            </a>
                        </div>

                        <!-- Feature 2: Available Truck Loads -->
                        <div class="p-6 h-full transition-all hover:shadow-lg hover:border-green-500/50 dark:hover:border-green-500/50">
                            <a href="/freights" wire:navigate class="space-y-4 block">
                                <flux:icon.cube class="w-8 h-8 text-indigo-600 dark:text-indigo-400" />
                                <h3 class="text-xl font-semibold text-zinc-900 dark:text-white">Available Truck Loads</h3>
                                <p class="text-zinc-600 dark:text-zinc-400">Do you wish to Transport Goods or are you a carrier looking for suitable return loads? Find freight that suits your vehicle.</p>
                            </a>
                        </div>

                        <!-- Feature 3: Transport Consultancy Services -->
                        <div class="p-6 h-full transition-all hover:shadow-lg hover:border-green-500/50 dark:hover:border-green-500/50">
                            <a href="/consultancy" wire:navigate class="space-y-4 block">
                                <flux:icon.arrow-trending-up class="w-8 h-8 text-red-600 dark:text-red-400" />
                                <h3 class="text-xl font-semibold text-zinc-900 dark:text-white">Transport Consultancy Services</h3>
                                <p class="text-zinc-600 dark:text-zinc-400">We assist Transport Operators to start, solve and manage transport business for both freight and passenger sectors.</p>
                            </a>
                        </div>

                        <!-- Feature 4: Tracking and Insurance -->
                        <div class="p-6 h-full transition-all hover:shadow-lg hover:border-green-500/50 dark:hover:border-green-500/50">
                            <a href="/tracking-insurance" wire:navigate class="space-y-4 block">
                                {{-- FIX: Replaced custom <x-graphic> with native flux:icon component --}}
                                <x-graphic name="umbrella" class="w-8 h-8 text-yellow-600 dark:text-yellow-400" />
                                <h3 class="text-xl font-semibold text-zinc-900 dark:text-white">Tracking and Insurance</h3>
                                <p class="text-zinc-600 dark:text-zinc-400">Protect your assets. We arrange insurance and tracking systems for your goods and vehicles whilst in transit or storage.</p>
                            </a>
                        </div>

                        <!-- Feature 5: Extended Freight Forwarding Services -->
                        <div class="p-6 h-full transition-all hover:shadow-lg hover:border-green-500/50 dark:hover:border-green-500/50">
                            <a href="/consultancy/#extended-freight" wire:navigate class="space-y-4 block">
                                <flux:icon.shopping-bag class="w-8 h-8 text-cyan-600 dark:text-cyan-400" />
                                <h3 class="text-xl font-semibold text-zinc-900 dark:text-white">Extended Freight Forwarding Services</h3>
                                <p class="text-zinc-600 dark:text-zinc-400">We handle the vital documentation requirements, including customs clearance, making necessary arrangements on your behalf.</p>
                            </a>
                        </div>

                        <!-- Feature 6: Cost effective Transport Solutions -->
                        <div class="p-6 h-full transition-all hover:shadow-lg hover:border-green-500/50 dark:hover:border-green-500/50">
                            <a href="/about-us#cost-effective" wire:navigate class="space-y-4 block">
                                {{-- FIX: Using battery-full as it matches the original intent (fa-battery-full) --}}
                                <flux:icon.battery-100 class="w-8 h-8 text-teal-600 dark:text-teal-400" />
                                <h3 class="text-xl font-semibold text-zinc-900 dark:text-white">Cost effective Transport Solutions</h3>
                                <p class="text-zinc-600 dark:text-zinc-400">Our experienced team selects the most appropriate and cost-effective methods of transport for your goods, optimizing logistics.</p>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main> 
    </div>
</x-layouts.app>
