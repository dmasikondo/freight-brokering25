<x-layouts.app :title="__('About Us')">
<section id="about-us" class="py-16 sm:py-24 bg-gray-50 dark:bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <header class="text-center mb-16 wow fadeInDown">
            <h1 class="text-5xl font-extrabold text-gray-900 dark:text-white sm:text-6xl">
                About Transpartner Logistics 🌍
            </h1>
            <p class="mt-4 max-w-2xl mx-auto text-xl text-gray-600 dark:text-gray-300">
                We are a Southern Africa-based Logistics, Freight Forwarding, and Transport Consultancy Company dedicated to connecting loads with reliable carriers.
            </p>
        </header>

        <div class="lg:grid lg:grid-cols-2 lg:gap-16 items-start">
            <div class="wow fadeInLeft bg-white dark:bg-gray-800 p-8 rounded-xl shadow-lg mb-10 lg:mb-0">
                <h2 class="text-3xl font-bold text-indigo-600 dark:text-indigo-400 mb-6">
                    Our Legacy & Core Services
                </h2>
                <p class="text-lg text-gray-700 dark:text-gray-300 leading-relaxed">
                    Transpartner Logistics (Pvt) Ltd (TPL), incorporated in 2011, is a fully registered private company operating as a **connected web of vetted freight carriers**. We own and operate hundreds of trucks within and across Zimbabwe and the SADC region.
                </p>
                <p class="mt-4 text-gray-700 dark:text-gray-300 font-semibold">
                    Our comprehensive service offerings include:
                </p>
                <ul class="mt-4 space-y-3 text-gray-600 dark:text-gray-400 list-disc list-inside ml-4">
                    <li>Core focus on **Logistics and Transport Consultancy**.</li>
                    <li>Providing logistics services to supply chains, service institutions, NGOs, and government departments.</li>
                    <li>Offering expert **Freight Forwarding and Brokerage** services.</li>
                    <li>Providing specialized services like **vehicle tracking and insurance**.</li>
                    <li>Serving as the essential link between **shippers** with goods and **trucking companies** looking for return loads across Southern Africa.</li>
                </ul>
            </div>
            
            <div class="wow fadeInRight p-8 bg-indigo-50 dark:bg-indigo-900 rounded-xl shadow-2xl shadow-indigo-500/20">
                <h3 class="text-2xl font-bold text-indigo-700 dark:text-white mb-4">
                    The Transpartner Difference
                </h3>
                <p class="text-lg text-indigo-800 dark:text-indigo-200 leading-relaxed border-l-4 border-indigo-500 pl-4">
                    "We are as good as our designated carriers. Therefore, all hauliers are meticulously **referenced and scrutinised** before we offer them any consignment, ensuring maximum reliability and security for our clients."
                </p>
                <div class="mt-6">
                    <a href="#" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 transition duration-150">
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
            <div class="wow fadeInUp p-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl transition duration-300">
                <div class="text-5xl text-indigo-500 mb-4">
                    🌟
                </div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Our Vision</h3>
                <h4 class="text-base text-indigo-600 dark:text-indigo-400 font-medium">Your Preferred Logistics Partner</h4>
                <p class="mt-3 text-gray-600 dark:text-gray-400 text-sm">
                    To be the leading freight forwarding and transport consultancy company in Southern Africa, driven by **Professionalism, Integrity, and Reliability**.
                </p>
            </div>

            {{-- Mission --}}
            <div class="wow fadeInUp p-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl transition duration-300" data-wow-delay="0.2s">
                <div class="text-5xl text-green-500 mb-4">
                    🎯
                </div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Our Mission</h3>
                <h4 class="text-base text-green-600 dark:text-green-400 font-medium">Cost-Effective & Reliable Transport</h4>
                <p class="mt-3 text-gray-600 dark:text-gray-400 text-sm">
                    To provide cost-effective and reliable transportation in Zimbabwe and SADC, while offering lasting consultancy solutions and training for transport practitioners.
                </p>
            </div>

            {{-- Concern (The Promise) --}}
            <div class="wow fadeInUp p-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl transition duration-300" data-wow-delay="0.4s">
                <div class="text-5xl text-red-500 mb-4">
                    ✅
                </div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Our Promise</h3>
                <h4 class="text-base text-red-600 dark:text-red-400 font-medium">Service and Quality</h4>
                <p class="mt-3 text-gray-600 dark:text-gray-400 text-sm">
                    Service and attention to quality is **The Transpartner Logistics difference**. You can count on our **Expertise** and **Teamwork** in an age where finding trustworthy service is paramount.
                </p>
            </div>
        </div>
        
        <hr class="my-16 border-gray-300 dark:border-gray-700">

        <div class="lg:grid lg:grid-cols-1 lg:gap-16 items-start">
            <div class="wow fadeInUp">
                <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-10 text-center">
                    Why Transport Clients Choose Us
                </h2>
                
                {{-- Using a simplified FAQ/Accordion structure with Tailwind (assuming Alpine.js for interactivity) --}}
                <div x-data="{ open: 1 }" class="space-y-4 max-w-4xl mx-auto">
                    
                    @php 
                        // Simplified array for cleaner iteration in Blade
                        $faqs = [
                            ['id' => 1, 'title' => 'Adherence to Moral Principles (Integrity)', 'content' => 'We manage our business through principles of integrity and ethical practice. Our passion is delivering solutions tailored specifically to meet our dedicated clients\' transport needs, ensuring thorough satisfaction.', 'image' => '/img/adherence_to_moral_principles.jpg'],
                            ['id' => 2, 'title' => 'Cost Effectiveness', 'content' => 'Our experts select the most appropriate and cost-effective transport methods. We help you drastically reduce shipping costs and simplify operations by providing access to the best carriage rates and shipment consolidation.', 'image' => null],
                            ['id' => 3, 'title' => 'Reliability and Security (Vetted Carriers)', 'content' => 'We firmly believe we are as good as our designated carriers. All hauliers are referenced and carefully verified before we offer them any work, putting a strong emphasis on maintaining our reputation.', 'image' => null],
                            ['id' => 4, 'title' => 'Insurance Cover', 'content' => 'Recognizing that adequate insurance cover for goods is extremely important, we can arrange comprehensive transit and storage insurance for your goods, providing peace of mind.', 'image' => null],
                            ['id' => 5, 'title' => 'Convenience to Shippers and Carriers (Marketplace)', 'content' => 'Our platform allows clients to find offers both ways: suitable freight for carriers at competitive rates, and available vehicle space for shippers. We facilitate rate bidding and affordable pricing based on actual distance covered.', 'image' => null],
                        ];
                    @endphp

                    @foreach($faqs as $faq)
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden border border-gray-200 dark:border-gray-700">
                            <button 
                                @click="open = (open === {{ $faq['id'] }} ? null : {{ $faq['id'] }})"
                                class="w-full flex justify-between items-center p-5 text-lg font-medium text-left text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none"
                            >
                                <span>{{ $faq['title'] }}</span>
                                <span :class="{'transform rotate-90': open === {{ $faq['id'] }}, 'transform rotate-0': open !== {{ $faq['id'] }}}" class="transition-transform duration-300">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </span>
                            </button>

                            <div x-show="open === {{ $faq['id'] }}" x-collapse.duration.300ms class="p-5 border-t border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300">
                                {{ $faq['content'] }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        
    </div>
</section>
</x-layouts.app>
		