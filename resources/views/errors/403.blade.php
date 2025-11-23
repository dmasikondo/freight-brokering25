<x-layouts.app :title="__('Access Denied  - 403 | Transpartner Logistics')">
    <div
        class="min-h-screen bg-gradient-to-br from-gray-50 to-rose-50 dark:from-slate-900 dark:to-slate-800 flex items-center justify-center p-4">
        <div
            class="max-w-xl w-full text-center p-8 md:p-12 bg-white/80 backdrop-blur-sm border border-rose-200 rounded-3xl shadow-2xl transition-all duration-500 dark:bg-slate-800/80 dark:border-rose-500/30">

            <!-- Animated Branding -->
            <div class="mb-8">
                <h1 class="text-2xl sm:text-3xl md:text-5xl font-black text-lime-600 tracking-wider dark:text-lime-400">
                    TRANSPARTNER
                    <span class="text-gray-700 font-light ml-1 dark:text-gray-300">
                        LOGISTICS
                    </span>
                </h1>
            </div>

            <!-- 403 SVG Illustration -->
            <div class="mb-10 flex justify-center">
                <div class="relative">
                    <div
                        class="w-64 h-48 bg-gradient-to-r from-rose-100 to-rose-200 rounded-2xl flex items-center justify-center shadow-inner dark:from-slate-700 dark:to-slate-600">
                        <span class="text-8xl font-black text-rose-500/70 dark:text-rose-400/50">403</span>
                    </div>
                    <div
                        class="absolute -top-4 -right-4 w-20 h-20 bg-rose-500 rounded-full flex items-center justify-center shadow-lg animate-bounce">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-10 h-10 text-white">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                    </div>
                    <div
                        class="absolute -bottom-4 -left-4 w-16 h-16 bg-rose-400 rounded-full flex items-center justify-center shadow-lg animate-pulse">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-6 h-6 text-white">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Main Heading -->
            <div class="space-y-4 mb-8">
                <h1 class="text-5xl font-black text-gray-900 tracking-tight dark:text-white">
                    Access Restricted
                </h1>
                <div class="h-1 w-24 bg-gradient-to-r from-rose-400 to-rose-500 rounded-full mx-auto"></div>
            </div>

            <!-- Description -->
            <p class="text-lg text-gray-600 mb-10 max-w-md mx-auto leading-relaxed dark:text-gray-400">
                You don't have permission to access this resource. Please contact your system administrator if you
                believe this is an error.
            </p>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-8">
                <a href="/"
                    class="px-8 py-4 bg-gradient-to-r from-rose-500 to-rose-600 text-white font-bold rounded-full shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 hover:-translate-y-1 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-5 h-5 text-white">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    Return to Home
                </a>
                <a href="tel:+263772930514"
                    class="px-6 py-4 bg-white text-rose-600 font-semibold border border-rose-300 rounded-full shadow-md hover:shadow-lg transition-all duration-300 transform hover:scale-105 flex items-center gap-2 dark:bg-slate-700 dark:text-rose-400 dark:border-rose-500/30">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-5 h-5 text-rose-600 dark:text-rose-400">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                    </svg>
                    Request Access
                </a>
            </div>

            <!-- Quick Links -->
            <div class="border-t border-gray-200 pt-6 dark:border-gray-700">
                <p class="text-sm text-gray-500 mb-4 dark:text-gray-500">Alternative Options</p>
                <div class="flex flex-wrap justify-center gap-4">
                    <a href="/faq"
                        class="text-rose-600 hover:text-rose-500 transition-colors duration-200 flex items-center gap-1 dark:text-rose-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
                        </svg>
                        Help Center
                    </a>
                    <a href="/#support"
                        class="text-rose-600 hover:text-rose-500 transition-colors duration-200 flex items-center gap-1 dark:text-rose-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
                        </svg>
                        IT Support
                    </a>
                </div>
            </div>
        </div>

        <!-- Background decorative elements -->
        <div
            class="fixed top-10 left-10 w-20 h-20 bg-rose-200 rounded-full blur-xl opacity-30 animate-pulse dark:bg-rose-900">
        </div>
        <div
            class="fixed bottom-10 right-10 w-32 h-32 bg-rose-300 rounded-full blur-xl opacity-20 animate-bounce dark:bg-rose-800">
        </div>
    </div>
</x-layouts.app>
