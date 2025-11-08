<x-layouts.app  :title="__('Service Unavailable - 503 | Transpartner Logistics')">
    <div
        class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50 dark:from-slate-900 dark:to-slate-800 flex items-center justify-center p-4">
        <div
            class="max-w-xl w-full text-center p-8 md:p-12 bg-white/80 backdrop-blur-sm border border-blue-200 rounded-3xl shadow-2xl transition-all duration-500 dark:bg-slate-800/80 dark:border-blue-500/30">

            <!-- Animated Branding -->
            <div class="mb-8">
                <h1 class="text-2xl font-black text-blue-600 tracking-wider dark:text-blue-400">
                    TRANSPARTNER<span class="text-gray-700 font-light ml-1 dark:text-gray-300">LOGISTICS</span>
                </h1>
            </div>

            <!-- 503 SVG Illustration -->
            <div class="mb-10 flex justify-center">
                <div class="relative">
                    <div
                        class="w-64 h-48 bg-gradient-to-r from-blue-100 to-blue-200 rounded-2xl flex items-center justify-center shadow-inner dark:from-slate-700 dark:to-slate-600">
                        <span class="text-8xl font-black text-blue-500/70 dark:text-blue-400/50">503</span>
                    </div>
                    <div
                        class="absolute -top-4 -right-4 w-20 h-20 bg-blue-500 rounded-full flex items-center justify-center shadow-lg animate-bounce">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-10 h-10 text-white">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z" />
                        </svg>
                    </div>
                    <div
                        class="absolute -bottom-4 -left-4 w-16 h-16 bg-blue-400 rounded-full flex items-center justify-center shadow-lg animate-pulse">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-6 h-6 text-white">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Main Heading -->
            <div class="space-y-4 mb-8">
                <h1 class="text-5xl font-black text-gray-900 tracking-tight dark:text-white">
                    Back Soon
                </h1>
                <div class="h-1 w-24 bg-gradient-to-r from-blue-400 to-blue-500 rounded-full mx-auto"></div>
            </div>

            <!-- Description -->
            <p class="text-lg text-gray-600 mb-10 max-w-md mx-auto leading-relaxed dark:text-gray-400">
                We're currently performing scheduled maintenance to improve your experience. Service will be restored
                shortly.
            </p>

            <!-- Maintenance Info -->
            <div
                class="mb-8 p-4 bg-blue-50 rounded-2xl border border-blue-200 dark:bg-blue-900/20 dark:border-blue-700/30">
                <div class="flex items-center justify-center gap-2 mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-5 h-5 text-blue-600 dark:text-blue-400">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm font-semibold text-blue-800 dark:text-blue-200">Estimated Recovery Time</span>
                </div>
                <div class="text-2xl font-bold text-blue-600 mb-2 dark:text-blue-400">~30 minutes</div>
                <div class="w-full bg-blue-200 rounded-full h-2 dark:bg-blue-700">
                    <div class="bg-blue-500 h-2 rounded-full animate-pulse" style="width: 65%"></div>
                </div>
                {{-- <p class="text-xs text-blue-600 mt-2 dark:text-blue-400">Started: 2:00 PM • Expected: 2:30 PM</p> --}}
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-8">
                <button onclick="window.location.reload()"
                    class="px-8 py-4 bg-gradient-to-r from-blue-500 to-blue-600 text-white font-bold rounded-full shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 hover:-translate-y-1 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-5 h-5 text-white">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    Refresh Page
                </button>
                <a href="/#status"
                    class="px-6 py-4 bg-white text-blue-600 font-semibold border border-blue-300 rounded-full shadow-md hover:shadow-lg transition-all duration-300 transform hover:scale-105 flex items-center gap-2 dark:bg-slate-700 dark:text-blue-400 dark:border-blue-500/30">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-5 h-5 text-blue-600 dark:text-blue-400">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                    </svg>
                    Status Page
                </a>
            </div>

            <!-- Quick Links -->
            <div class="border-t border-gray-200 pt-6 dark:border-gray-700">
                <p class="text-sm text-gray-500 mb-4 dark:text-gray-500">Stay Updated</p>
                <div class="flex flex-wrap justify-center gap-4">
                    <a href="/#updates"
                        class="text-blue-600 hover:text-blue-500 transition-colors duration-200 flex items-center gap-1 dark:text-blue-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                        </svg>
                        Updates
                    </a>
                    <a href="/#status"
                        class="text-blue-600 hover:text-blue-500 transition-colors duration-200 flex items-center gap-1 dark:text-blue-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        Maintenance Log
                    </a>
                    <a href="https://wa.me/263772930514"
                        class="text-blue-600 hover:text-blue-500 transition-colors duration-200 flex items-center gap-1 dark:text-blue-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                        </svg>
                        Contact
                    </a>
                </div>
            </div>
        </div>

        <!-- Background decorative elements -->
        <div
            class="fixed top-10 left-10 w-20 h-20 bg-blue-200 rounded-full blur-xl opacity-30 animate-pulse dark:bg-blue-900">
        </div>
        <div
            class="fixed bottom-10 right-10 w-32 h-32 bg-blue-300 rounded-full blur-xl opacity-20 animate-bounce dark:bg-blue-800">
        </div>
    </div>

</x-layouts.app>
