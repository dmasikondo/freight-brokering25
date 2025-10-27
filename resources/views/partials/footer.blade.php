<footer class="pt-16 pb-8 border-t border-emerald-700/50">
    <div class="container mx-auto px-6">
        <!-- Main Footer Content Grid -->
        <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-5 lg:gap-12">
            
            <!-- 1. Branding / Contact Section -->
            <div class="col-span-1 md:col-span-2 lg:col-span-2">
                <div class="flex items-center mb-4">
                    <div class="text-3xl font-extrabold text-emerald-500">
                        TPL<span class="text-white font-light"> Logistics</span>
                    </div>
                    <span class="ml-2 px-2 py-1 text-xs font-semibold bg-emerald-500/20 text-emerald-400 rounded-full border border-emerald-500/30">Your Preffered Logistics Partner</span>
                </div>
                <p class="text-gray-400 text-sm mb-6 max-w-md">
                    Reliable, fast, and secure transportation solutions across borders. Your partner in moving what matters.
                </p>
                
                <!-- Contact Information -->
                <div class="space-y-3">
                    <div class="flex items-start">
                        <flux:icon.device-phone-mobile class="w-5 h-5 text-emerald-400 mt-0.5 mr-3 flex-shrink-0" />                        
                        <span class="text-gray-300 text-sm font-medium">+263 772 930 514</span>
                    </div>
                    <div class="flex items-start">
                        <flux:icon.envelope class="text-emerald-400 mt-0.5 mr-3 flex-shrink-0" />
                        </svg>
                        <span class="text-gray-300 text-sm font-medium">info@transpartnerlogistics.co.zw</span>
                    </div>
                </div>
            </div>

            <!-- 2. Quick Links Section -->
            <div>
                <h5 class="font-semibold text-lg mb-6 uppercase tracking-wider text-emerald-400 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                    Quick Links
                </h5>
                <ul class="space-y-3">
                    <li>
                        <a href="{{ route('home') }}" class="text-gray-300 hover:text-emerald-400 transition duration-300 flex items-center group">
                            <span class="w-2 h-2 bg-emerald-500 rounded-full mr-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></span>
                            Home
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('about-us') }}" class="text-gray-300 hover:text-emerald-400 transition duration-300 flex items-center group">
                            <span class="w-2 h-2 bg-emerald-500 rounded-full mr-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></span>
                            About Us
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('faq') }}" class="text-gray-300 hover:text-emerald-400 transition duration-300 flex items-center group">
                            <span class="w-2 h-2 bg-emerald-500 rounded-full mr-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></span>
                            F.A.Q
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('terms') }}" class="text-gray-300 hover:text-emerald-400 transition duration-300 flex items-center group">
                            <span class="w-2 h-2 bg-emerald-500 rounded-full mr-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></span>
                            Terms & Policy
                        </a>
                    </li>
                </ul>
            </div>

            <!-- 3. Account/Resources Section -->
            <div>
                <h5 class="font-semibold text-lg mb-6 uppercase tracking-wider text-emerald-400 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Resources
                </h5>
                <ul class="space-y-3">
                    <li>
                        <a href="#" class="text-gray-300 hover:text-emerald-400 transition duration-300 flex items-center group">
                            <span class="w-2 h-2 bg-emerald-500 rounded-full mr-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></span>
                            Tracking
                        </a>
                    </li>
                    <li>
                        <a href="http://webmail.transpartnerlogistics.co.zw/" target="_blank" class="text-gray-300 hover:text-emerald-400 transition duration-300 flex items-center group">
                            <span class="w-2 h-2 bg-emerald-500 rounded-full mr-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></span>
                            Webmail Access
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-gray-300 hover:text-emerald-400 transition duration-300 flex items-center group">
                            <span class="w-2 h-2 bg-emerald-500 rounded-full mr-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></span>
                            Client Portal
                        </a>
                    </li>
                </ul>
            </div>

            <!-- 4. Follow Us Section -->
            <div>
                <h5 class="font-semibold text-lg mb-6 uppercase tracking-wider text-emerald-400 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                    </svg>
                    Connect
                </h5>
                <p class="text-gray-400 text-sm mb-4">Follow us on social media for updates</p>
                <div class="flex space-x-3">
                    <!-- Social Icon Wrapper -->
                    <a href="https://www.facebook.com/transpartnerlogisticsZim" target="_blank" aria-label="Facebook"
                        class=" text-gray-400 hover:text-white hover:bg-emerald-500   transition-all duration-300 p-3 rounded-full bg-gray-800/50 transform hover:-translate-y-1">
                        <x-graphic name="facebook" class="w-4 h-4 mr-2 text-blue-900" />
                    </a>
                    
                    <a href="https://x.com/TranspartnerLog" target="_blank" aria-label="X (Twitter)"
                        class="text-gray-400 hover:text-white hover:bg-emerald-500 transition-all duration-300 p-3 rounded-full bg-gray-800/50 transform hover:-translate-y-1">
                        <x-graphic name="twitter" class="w-5 h-5"/>
                       
                    </a>

                    <a href="https://www.linkedin.com/company/transpartner-logistics-company/?trk=biz-companies-cym" target="_blank" aria-label="LinkedIn"
                        class="text-gray-400 hover:text-white hover:bg-emerald-500 transition-all duration-300 p-3 rounded-full bg-gray-800/50 transform hover:-translate-y-1">
                        <x-graphic name="linkedin" class="w-5 h-5 text-indigo-900"/>
                    </a>
                    
                    <a href="https://wa.me/263772930514" target="_blank" aria-label="WhatsApp"
                        class="text-gray-400 hover:text-white hover:bg-emerald-500 transition-all duration-300 p-3 rounded-full bg-gray-800/50 transform hover:-translate-y-1">
                        <x-graphic name="whatsapp" class="w-5 h-5"/>
                       
                    </a>
                </div>
                
                <!-- Newsletter Signup -->
                <div class="mt-6">
                    <p class="text-gray-400 text-sm mb-2">Subscribe to our newsletter</p>
                    <div class="flex">
                        <input type="email" placeholder="Your email" class="px-3 py-2 bg-gray-800/50 text-gray-300 text-sm rounded-l-md focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent w-full">
                        <button class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-r-md transition duration-300 text-sm font-medium">
                            Subscribe
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Divider Line -->
        <div class="border-t border-emerald-700/50 mt-12 mb-6"></div>

        <!-- Copyright Section -->
        <div class="flex flex-col md:flex-row justify-between items-center">
            <p class="text-gray-500 font-medium text-sm mb-4 md:mb-0">
                &copy; {{ date('Y') }} TransPartner Logistics. All rights reserved.
            </p>
            <div class="flex items-center space-x-4 text-sm">
                <a href="{{ route('terms') }}" class="text-gray-500 hover:text-emerald-400 transition duration-300">Privacy Policy</a>
                <span class="text-gray-600">|</span>
                <a href="{{ route('terms') }}" class="text-gray-500 hover:text-emerald-400 transition duration-300">Terms of Service</a>
                <span class="text-gray-600">|</span>
                <span class="text-gray-500">
                    Web Woven with 💙 by <a href="https://www.facebook.com/drunkenSpider" target="blank" class="hover:text-emerald-300 transition duration-300">DSMultimedia</a>
                </span>
            </div>
        </div>
    </div>
</footer>