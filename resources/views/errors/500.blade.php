<x-layouts.app  :title="__('Server Error - 500 | Transpartner Logistics')" >
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-amber-50 dark:from-slate-900 dark:to-slate-800 flex items-center justify-center p-4">
    <div class="max-w-xl w-full text-center p-8 md:p-12 bg-white/80 backdrop-blur-sm border border-amber-200 rounded-3xl shadow-2xl transition-all duration-500 dark:bg-slate-800/80 dark:border-amber-500/30">
        
        <!-- Animated Branding -->
        <div class="mb-8">
            <h1 class="text-2xl font-black text-amber-600 tracking-wider dark:text-amber-400">
                TRANSPARTNER<span class="text-gray-700 font-light ml-1 dark:text-gray-300">LOGISTICS</span>
            </h1>
        </div>

        <!-- 500 SVG Illustration -->
        <div class="mb-10 flex justify-center">
            <div class="relative">
                <div class="w-64 h-48 bg-gradient-to-r from-amber-100 to-amber-200 rounded-2xl flex items-center justify-center shadow-inner dark:from-slate-700 dark:to-slate-600">
                    <span class="text-8xl font-black text-amber-500/70 dark:text-amber-400/50">500</span>
                </div>
                <div class="absolute -top-4 -right-4 w-20 h-20 bg-amber-500 rounded-full flex items-center justify-center shadow-lg animate-bounce">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 text-white">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <div class="absolute -bottom-4 -left-4 w-16 h-16 bg-amber-400 rounded-full flex items-center justify-center shadow-lg animate-pulse">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-white">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 7.5A2.25 2.25 0 017.5 5.25h9a2.25 2.25 0 012.25 2.25v9a2.25 2.25 0 01-2.25 2.25h-9a2.25 2.25 0 01-2.25-2.25v-9z" />
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Main Heading -->
        <div class="space-y-4 mb-8">
            <h1 class="text-5xl font-black text-gray-900 tracking-tight dark:text-white">
                Server Maintenance
            </h1>
            <div class="h-1 w-24 bg-gradient-to-r from-amber-400 to-amber-500 rounded-full mx-auto"></div>
        </div>

        <!-- Description -->
        <p class="text-lg text-gray-600 mb-10 max-w-md mx-auto leading-relaxed dark:text-gray-400">
            Our servers are experiencing technical difficulties. Our team has been alerted and is working to resolve the issue promptly.
        </p>

        <!-- Status & Progress -->
        <div class="mb-8 p-4 bg-amber-50 rounded-2xl border border-amber-200 dark:bg-amber-900/20 dark:border-amber-700/30">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-amber-800 dark:text-amber-200">System Status</span>
                <span class="text-sm font-semibold text-amber-600 dark:text-amber-400">Investigating</span>
            </div>
            <div class="w-full bg-amber-200 rounded-full h-2 dark:bg-amber-700">
                <div class="bg-amber-500 h-2 rounded-full animate-pulse" style="width: 45%"></div>
            </div>
            <p class="text-xs text-amber-600 mt-2 dark:text-amber-400">Last updated: Just now</p>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-8">
            <a href="/" class="px-8 py-4 bg-gradient-to-r from-amber-500 to-amber-600 text-white font-bold rounded-full shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 hover:-translate-y-1 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-white">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
                Return to Home
            </a>
            <button onclick="window.location.reload()" class="px-6 py-4 bg-white text-amber-600 font-semibold border border-amber-300 rounded-full shadow-md hover:shadow-lg transition-all duration-300 transform hover:scale-105 flex items-center gap-2 dark:bg-slate-700 dark:text-amber-400 dark:border-amber-500/30">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-amber-600 dark:text-amber-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
                Try Again
            </button>
        </div>

        <!-- Quick Links -->
        <div class="border-t border-gray-200 pt-6 dark:border-gray-700">
            <p class="text-sm text-gray-500 mb-4 dark:text-gray-500">While We Fix This</p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="/#status" class="text-amber-600 hover:text-amber-500 transition-colors duration-200 flex items-center gap-1 dark:text-amber-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                    </svg>
                    System Status
                </a>
                <a href="https://wa.me/263772930514" class="text-amber-600 hover:text-amber-500 transition-colors duration-200 flex items-center gap-1 dark:text-amber-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                    </svg>
                    Report Issue
                </a>
                <a href="/faq" class="text-amber-600 hover:text-amber-500 transition-colors duration-200 flex items-center gap-1 dark:text-amber-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
                    </svg>
                    Help Center
                </a>
            </div>
        </div>
    </div>

    <!-- Background decorative elements -->
    <div class="fixed top-10 left-10 w-20 h-20 bg-amber-200 rounded-full blur-xl opacity-30 animate-pulse dark:bg-amber-900"></div>
    <div class="fixed bottom-10 right-10 w-32 h-32 bg-amber-300 rounded-full blur-xl opacity-20 animate-bounce dark:bg-amber-800"></div>
</div>    
</x-layouts.app>