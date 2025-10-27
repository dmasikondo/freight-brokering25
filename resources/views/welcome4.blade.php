<x-layouts.app>
    <div class="">
        <main class="w-full">
            <!-- === VEHICLE ANIMATION HEADER === -->
            @include('partials.vehicle_animation')

            <!-- === MODERN HERO SECTION === -->
            <div id="home"
                class="relative py-20 md:py-28 bg-gradient-to-br from-zinc-50 via-white to-emerald-50 dark:from-zinc-900 dark:via-zinc-800 dark:to-emerald-900/20 overflow-hidden">
                <!-- Background Elements -->
                <div class="absolute inset-0 bg-grid-zinc-900/[0.02] dark:bg-grid-white/[0.02] bg-[size:60px_60px]">
                </div>
                <div class="absolute top-0 left-0 w-72 h-72 bg-emerald-500/5 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute bottom-0 right-0 w-96 h-96 bg-green-600/5 rounded-full blur-3xl"></div>

                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
                    <div class="grid lg:grid-cols-12 gap-12 items-center">
                        <!-- Text Content -->
                        <div class="lg:col-span-6 space-y-8">
                            <div class="space-y-2">
                                <flux:badge variant="outline"
                                    class="text-sm font-semibold text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-700 bg-emerald-50 dark:bg-emerald-900/20 px-3 py-1 rounded-full">
                                    🚛 Your Preferred Logistics Partner
                                </flux:badge>
                                <h1 class="text-4xl md:text-6xl font-bold tracking-tight text-zinc-900 dark:text-white">
                                    <span
                                        class="block bg-gradient-to-r from-zinc-900 to-emerald-600 dark:from-white dark:to-emerald-400 bg-clip-text text-transparent">We
                                        pledge to</span>
                                    <span
                                        class="block bg-gradient-to-r from-emerald-600 to-green-600 dark:from-emerald-400 dark:to-green-300 bg-clip-text text-transparent">assist
                                        Transport</span>
                                    <span
                                        class="block bg-gradient-to-r from-green-600 to-emerald-700 dark:from-green-300 dark:to-emerald-400 bg-clip-text text-transparent">Companies</span>
                                </h1>
                            </div>
                            <p class="text-xl text-zinc-600 dark:text-zinc-300 leading-relaxed">
                                Empowering freight and passenger companies across Zimbabwe and the SADC region with
                                cutting-edge logistics solutions and expert consultancy services.
                            </p>
                            <div class="flex flex-col sm:flex-row gap-4 pt-4">
                                <flux:button variant="primary"
                                    class="group bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-700 hover:to-green-700 border-0 text-white px-8 py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105"
                                    href="/consultancy" wire:navigate>
                                    Get Consultancy Today
                                    <flux:icon.arrow-right
                                        class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" />
                                </flux:button>
                                <flux:button variant="ghost"
                                    class="border-2 border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300 hover:border-emerald-300 hover:text-emerald-600 dark:hover:border-emerald-500 dark:hover:text-emerald-400 px-8 py-3 rounded-xl font-semibold transition-all duration-300 hover:scale-105"
                                    href="/about-us" wire:navigate>
                                    Learn More
                                </flux:button>
                            </div>

                            <!-- Stats -->
                            <div class="grid grid-cols-3 gap-6 pt-8 border-t border-zinc-200 dark:border-zinc-700">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">500+</div>
                                    <div class="text-sm text-zinc-600 dark:text-zinc-400">Vehicles</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">24/7</div>
                                    <div class="text-sm text-zinc-600 dark:text-zinc-400">Support</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">SADC</div>
                                    <div class="text-sm text-zinc-600 dark:text-zinc-400">Coverage</div>
                                </div>
                            </div>
                        </div>

                        <!-- Image / Visual -->
                        <div class="lg:col-span-6 lg:flex lg:justify-end">
                            <div class="relative group">
                                <div
                                    class="absolute -inset-4 bg-gradient-to-r from-emerald-600 to-green-600 rounded-2xl blur opacity-25 group-hover:opacity-75 transition duration-1000 group-hover:duration-200">
                                </div>
                                <div
                                    class="relative overflow-hidden rounded-xl shadow-2xl transition-all duration-500 group-hover:scale-[1.02]">
                                    <img src="https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2069&q=80"
                                        alt="Modern transport logistics"
                                        class="object-cover w-full h-96 rounded-xl transform group-hover:scale-110 transition duration-700" />
                                    <div
                                        class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent rounded-xl">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- === MODERN SERVICES SECTION === -->
            <div class="py-20 md:py-28 bg-white dark:bg-zinc-800 relative overflow-hidden">
                <!-- Background Pattern -->
                <div class="absolute inset-0 bg-dots-zinc-900/[0.02] dark:bg-dots-white/[0.02] bg-[size:30px_30px]">
                </div>

                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
                    <div class="text-center mb-16">
                        <flux:badge variant="outline"
                            class="text-sm font-semibold text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-700 bg-emerald-50 dark:bg-emerald-900/20 px-3 py-1 rounded-full mb-4">
                            Our Services
                        </flux:badge>
                        <h2 class="text-4xl md:text-5xl font-bold text-zinc-900 dark:text-white mb-6">
                            Comprehensive <span
                                class="bg-gradient-to-r from-emerald-600 to-green-600 bg-clip-text text-transparent">Transport
                                Solutions</span>
                        </h2>
                        <p class="text-xl text-zinc-600 dark:text-zinc-300 max-w-3xl mx-auto leading-relaxed">
                            We are a Southern Africa based Freight Forwarding and Transport Consultancy Company that
                            offers extended logistics services with cutting-edge technology.
                        </p>
                    </div>

                    <!-- Features Grid -->
                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                        @php
                            $features = [
                                [
                                    'icon' => 'truck',
                                    'color' => 'emerald',
                                    'title' => 'Available Vehicles',
                                    'description' =>
                                        'We have carefully vetted Carriers with locations restricted to Southern African (SADC) States, ready to move your goods.',
                                    'link' => '/lanes',
                                ],
                                [
                                    'icon' => 'cube',
                                    'color' => 'indigo',
                                    'title' => 'Available Truck Loads',
                                    'description' =>
                                        'Do you wish to Transport Goods or are you a carrier looking for suitable return loads? Find freight that suits your vehicle.',
                                    'link' => '/freights',
                                ],
                                [
                                    'icon' => 'arrow-trending-up',
                                    'color' => 'red',
                                    'title' => 'Transport Consultancy Services',
                                    'description' =>
                                        'We assist Transport Operators to start, solve and manage transport business for both freight and passenger sectors.',
                                    'link' => '/consultancy',
                                ],
                                [
                                    'icon' => 'shield-check',
                                    'color' => 'yellow',
                                    'title' => 'Tracking and Insurance',
                                    'description' =>
                                        'Protect your assets. We arrange insurance and tracking systems for your goods and vehicles whilst in transit or storage.',
                                    'link' => '/tracking-insurance',
                                ],
                                [
                                    'icon' => 'shopping-bag',
                                    'color' => 'cyan',
                                    'title' => 'Extended Freight Forwarding Services',
                                    'description' =>
                                        'We handle the vital documentation requirements, including customs clearance, making necessary arrangements on your behalf.',
                                    'link' => '/consultancy/#extended-freight',
                                ],
                                [
                                    'icon' => 'bolt',
                                    'color' => 'teal',
                                    'title' => 'Cost effective Transport Solutions',
                                    'description' =>
                                        'Our experienced team selects the most appropriate and cost-effective methods of transport for your goods, optimizing logistics.',
                                    'link' => '/about-us#cost-effective',
                                ],
                            ];
                        @endphp

                        @foreach ($features as $feature)
                            <div class="group relative">
                                <div
                                    class="absolute -inset-0.5 bg-gradient-to-r from-{{ $feature['color'] }}-500 to-green-500 rounded-2xl blur opacity-0 group-hover:opacity-20 transition duration-1000 group-hover:duration-200">
                                </div>
                                <div
                                    class="relative p-8 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm hover:shadow-xl transition-all duration-300 group-hover:border-{{ $feature['color'] }}-300 dark:group-hover:border-{{ $feature['color'] }}-500 h-full flex flex-col">
                                    <a href="{{ $feature['link'] }}" wire:navigate class="space-y-6 block flex-1">
                                        <div
                                            class="w-14 h-14 bg-gradient-to-br from-{{ $feature['color'] }}-500 to-{{ $feature['color'] }}-600 rounded-2xl flex items-center justify-center shadow-lg">
                                            <flux:icon name="{{ $feature['icon'] }}" class="w-7 h-7 text-white" />
                                        </div>
                                        <h3
                                            class="text-2xl font-bold text-zinc-900 dark:text-white group-hover:text-{{ $feature['color'] }}-600 dark:group-hover:text-{{ $feature['color'] }}-400 transition-colors">
                                            {{ $feature['title'] }}
                                        </h3>
                                        <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed flex-1">
                                            {{ $feature['description'] }}
                                        </p>
                                        <div
                                            class="flex items-center text-{{ $feature['color'] }}-600 dark:text-{{ $feature['color'] }}-400 font-semibold pt-4">
                                            Learn more
                                            <flux:icon.arrow-right
                                                class="w-4 h-4 ml-2 transform group-hover:translate-x-1 transition-transform" />
                                        </div>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </main>
    </div>
</x-layouts.app>
