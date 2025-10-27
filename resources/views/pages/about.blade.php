<x-layouts.app :title="__('About Us')">
    <section id="about-us" class="py-16 sm:py-24 bg-gray-50 dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <header class="text-center mb-16 wow fadeInDown">
                <x-card.heading>
                <p class="text-5xl font-extrabold text-gray-900 dark:text-white sm:text-6xl">
                    About Transpartner Logistics 🌍
                </p>                    
                </x-card.heading>

                <p class="mt-4 max-w-2xl mx-auto text-xl text-gray-600 dark:text-gray-300">
                    We are a Southern Africa-based Logistics, Freight Forwarding, and Transport Consultancy Company
                    dedicated to connecting loads with reliable carriers.
                </p>
            </header>

            <div class="lg:grid lg:grid-cols-2 lg:gap-16 items-start">
                <div class="wow fadeInLeft bg-white dark:bg-gray-800 p-8 rounded-xl shadow-lg mb-10 lg:mb-0">
                    <h2 class="text-3xl font-bold text-indigo-600 dark:text-indigo-400 mb-6">
                        Our Legacy & Core Services
                    </h2>
                    <p class="text-lg text-gray-700 dark:text-gray-300 leading-relaxed">
                        Transpartner Logistics (Pvt) Ltd (TPL), incorporated in 2011, is a fully registered private
                        company operating as a <span class="font-semibold">connected web of vetted freight
                            carriers</span>. We make you leverage hundreds of partners and trucks at your fingertips for SADC regional coverage.
                    </p>
                    <p class="mt-4 text-gray-700 dark:text-gray-300 font-extrabold">
                        Our comprehensive service offerings include:
                    </p>
                    <ul class="mt-4 space-y-3 text-gray-600 dark:text-gray-400 list-disc list-inside ml-4">
                        <li>Core focus on <span class="font-semibold">Logistics and Transport Consultancy</span>.</li>
                        <li>Providing logistics services to supply chains, service institutions, NGOs, and government
                            departments.</li>
                        <li>Offering expert <span class="font-semibold">Freight Forwarding and Brokerage</span>
                            services.</li>
                        <li>Providing specialized services like <span class="font-semibold">vehicle tracking and
                                insurance</span>.</li>
                        <li>Serving as the essential link between <span class="font-semibold">shippers</span> with goods
                            and <span class="font-semibold">trucking companies</span> looking for return loads across
                            Southern Africa.</li>
                    </ul>
                </div>

                <div
                    class="wow fadeInRight p-8 bg-indigo-50 dark:bg-indigo-900 rounded-xl shadow-2xl shadow-indigo-500/20">
                    <h3 class="text-2xl font-bold text-indigo-700 dark:text-white mb-4">
                        The Transpartner Difference
                    </h3>
                    <p
                        class="text-lg text-indigo-800 dark:text-indigo-200 leading-relaxed border-l-4 border-indigo-500 pl-4">
                        "We are as good as our designated carriers. Therefore, all hauliers are meticulously
                        <span class="font-semibold"> referenced and scrutinised</span> before we offer them any
                        consignment, ensuring maximum
                        reliability and security for our clients."
                    </p>
                    <div class="mt-6">
                        <a href="#"
                            class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 transition duration-150">
                            Partner with Us &rarr;
                        </a>
                    </div>
                </div>
            </div>

            <hr class="my-16 border-gray-300 dark:border-gray-700">

            <h2 class="text-4xl font-extrabold text-center text-gray-900 dark:text-white mb-12">
                Our Core Values
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">

                {{-- Vision --}}
                <div
                    class="wow fadeInUp p-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl transition duration-300">
                    <div class="text-5xl text-indigo-500 mb-4">
                        🌟
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Our Vision</h3>
                    <h4 class="text-base text-indigo-600 dark:text-indigo-400 font-medium">Your Preferred Logistics
                        Partner</h4>
                    <p class="mt-3 text-gray-600 dark:text-gray-400 text-sm">
                        To be the leading freight forwarding and transport consultancy company in Southern Africa,
                        driven by <span class="font-semibold">Professionalism, Integrity, and Reliability</span>.
                    </p>
                </div>

                {{-- Mission --}}
                <div class="wow fadeInUp p-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl transition duration-300"
                    data-wow-delay="0.2s">
                    <div class="text-5xl text-green-500 mb-4">
                        🎯
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Our Mission</h3>
                    <h4 class="text-base text-green-600 dark:text-green-400 font-medium">Cost-Effective & Reliable
                        Transport</h4>
                    <p class="mt-3 text-gray-600 dark:text-gray-400 text-sm">
                        To provide cost-effective and reliable transportation in Zimbabwe and SADC, while offering
                        lasting consultancy solutions and training for transport practitioners.
                    </p>
                </div>

                {{-- Concern (The Promise) --}}
                <div class="wow fadeInUp p-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl transition duration-300"
                    data-wow-delay="0.4s">
                    <div class="text-5xl text-red-500 mb-4">
                        ✅
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Our Promise</h3>
                    <h4 class="text-base text-red-600 dark:text-red-400 font-medium">Service and Quality</h4>
                    <p class="mt-3 text-gray-600 dark:text-gray-400 text-sm">
                        Service and attention to quality is <span class="font-semibold">The Transpartner Logistics
                            difference</span>. You can count on
                        our <span class="font-semibold">Expertise</span> and <span class="font-semibold">Teamwork</span>
                        in an age where finding trustworthy service is paramount.
                    </p>
                </div>
            </div>

            <hr class="my-16 border-gray-300 dark:border-gray-700">

            <div class="lg:grid lg:grid-cols-1 lg:gap-16 items-start">
                <div class="wow fadeInUp">

                    <div class="text-center mb-12">
                        <div class="flex flex-wrap justify-center gap-3 mb-6">
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 text-sm font-medium">
                                <x-icon name="check-circle" class="w-4 h-4 mr-1" />
                                Reliable
                            </span>
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 text-sm font-medium">
                                <x-icon name="clock" class="w-4 h-4 mr-1" />
                                Efficient
                            </span>
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-400 text-sm font-medium">
                                <x-icon name="shield-check" class="w-4 h-4 mr-1" />
                                Secure
                            </span>
                        </div>
                        <div class="text-center mb-12">
                            <div
                                class="inline-flex items-center px-4 py-2 rounded-full bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400 text-sm font-medium mb-4">
                                <x-icon name="sparkles" class="w-4 h-4 mr-2" />
                                Your Preferred Logistics Partner
                            </div>

                            <h2 class="text-3xl font-bold text-gray-900 dark:text-white sm:text-4xl lg:text-5xl mb-4">
                                    <span class="block bg-gradient-to-r from-zinc-900 to-emerald-600 dark:from-white dark:to-emerald-400 bg-clip-text text-transparent">Why Smart Transport Clients & Professionals</span>
                                                                  
                                
                                <span class="block bg-gradient-to-r from-emerald-600 to-green-600 dark:from-emerald-400 dark:to-green-300 bg-clip-text text-transparent">
                                    Choose Us?
                                </span>
                            </h2>

                            <p class="text-lg text-gray-600 dark:text-gray-300 max-w-2xl mx-auto leading-relaxed">
                                Discover the values and expertise that make us the preferred choice for logistics and
                                transportation solutions in the SADC region.
                            </p>
                        </div>

                        {{-- Using a simplified FAQ/Accordion structure with Tailwind (assuming Alpine.js for interactivity) --}}
                        <div x-data="{ open: 1 }" class="max-w-4xl mx-auto space-y-6">
                            @php
                                $faqs = [
                                    [
                                        'id' => 1,
                                        'title' => 'Adherence to Moral Principles (Integrity)',
                                        'content' =>
                                            'We manage our business through principles of integrity and ethical practice. Our passion is delivering solutions tailored specifically to meet our dedicated clients\' transport needs, ensuring thorough satisfaction.',
                                        'icon' => 'shield-check',
                                    ],
                                    [
                                        'id' => 2,
                                        'title' => 'Cost Effectiveness',
                                        'content' =>
                                            'Our experts select the most appropriate and cost-effective transport methods. We help you drastically reduce shipping costs and simplify operations by providing access to the best carriage rates and shipment consolidation.',
                                        'icon' => 'currency-dollar',
                                    ],
                                    [
                                        'id' => 3,
                                        'title' => 'Reliability and Security (Vetted Carriers)',
                                        'content' =>
                                            'We firmly believe we are as good as our designated carriers. All hauliers are referenced and carefully verified before we offer them any work, putting a strong emphasis on maintaining our reputation.',
                                        'icon' => 'user-group',
                                    ],
                                    [
                                        'id' => 4,
                                        'title' => 'Insurance Cover',
                                        'content' =>
                                            'Recognizing that adequate insurance cover for goods is extremely important, we can arrange comprehensive transit and storage insurance for your goods, providing peace of mind.',
                                        'icon' => 'document-check',
                                    ],
                                    [
                                        'id' => 5,
                                        'title' => 'Convenience to Shippers and Carriers (Marketplace)',
                                        'content' =>
                                            'Our platform allows clients to find offers both ways: suitable freight for carriers at competitive rates, and available vehicle space for shippers. We facilitate rate bidding and affordable pricing based on actual distance covered.',
                                        'icon' => 'building-storefront',
                                    ],
                                ];
                            @endphp

                            @foreach ($faqs as $faq)
                                <div
                                    class="overflow-hidden transition-all duration-300 bg-white border border-gray-200 rounded-xl shadow-xs dark:bg-gray-800 dark:border-gray-700 hover:shadow-md dark:hover:shadow-gray-700/20">
                                    <button @click="open = (open === {{ $faq['id'] }} ? null : {{ $faq['id'] }})"
                                        class="flex items-center justify-between w-full p-6 text-left transition-colors duration-200 hover:bg-gray-50 dark:hover:bg-gray-700/50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-inset"
                                        :class="{ 'bg-gray-50 dark:bg-gray-700/50': open === {{ $faq['id'] }} }">
                                        <div class="flex items-center space-x-4">
                                            <div
                                                class="flex items-center justify-center w-10 h-10 rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-900/20 dark:text-primary-400">
                                                <x-icon name="{{ $faq['icon'] }}" class="w-5 h-5" />
                                            </div>
                                            <span class="text-lg font-semibold text-gray-900 dark:text-white">
                                                {{ $faq['title'] }}
                                            </span>
                                        </div>

                                        <div class="flex items-center space-x-2">
                                            <span x-show="open !== {{ $faq['id'] }}"
                                                class="text-sm font-medium text-primary-600 dark:text-primary-400">
                                                Read more
                                            </span>
                                            <span
                                                :class="{
                                                    'rotate-180 transform': open === {{ $faq['id'] }},
                                                    'transform rotate-0': open !== {{ $faq['id'] }}
                                                }"
                                                class="transition-transform duration-300 text-gray-500 dark:text-gray-400">
                                                <x-icon name="chevron-down" class="w-5 h-5" />
                                            </span>
                                        </div>
                                    </button>

                                    <div x-show="open === {{ $faq['id'] }}" x-collapse.duration.500ms
                                        class="border-t border-gray-200 dark:border-gray-700">
                                        <div class="p-6">
                                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                                                {{ $faq['content'] }}
                                            </p>
                                            @if (isset($faq['image']) && $faq['image'])
                                                <div class="mt-4">
                                                    <img src="{{ $faq['image'] }}" alt="{{ $faq['title'] }}"
                                                        class="rounded-lg shadow-xs">
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>
    </section>
</x-layouts.app>
