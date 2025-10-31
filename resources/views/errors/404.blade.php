<x-layouts.app  :title="__('Not Found - 404 | Transpartner Logistics')">
{{-- <div class="h-screen bg-cover bg-center" style="background-image: url('{{ asset('storage/img/svg/404.svg') }}');">
    mabossida<!-- Your content here -->
</div> --}}
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-lime-50 dark:from-slate-900 dark:to-slate-800 flex items-center justify-center p-4">
    <div class="max-w-xl w-full text-center p-8 md:p-12 bg-white/80 backdrop-blur-sm border border-lime-200 rounded-3xl shadow-2xl transition-all duration-500 dark:bg-slate-800/80 dark:border-lime-500/30">
        
        <!-- Animated Branding -->
        <div class="mb-8 animate-pulse">
            <h1 class="text-2xl font-black text-lime-600 tracking-wider dark:text-lime-400">
                TRANSPARTNER<span class="text-gray-700 font-light ml-1 dark:text-gray-300">LOGISTICS</span>
            </h1>
        </div>

        <!-- 404 Illustration with Animation -->
        <div class="mb-10 flex justify-center">
            <div class="relative">
                <div class="w-64 h-48 bg-gradient-to-r from-lime-100 to-lime-200 rounded-2xl flex items-center justify-center shadow-inner dark:from-slate-700 dark:to-slate-600">
                    <span class="text-8xl font-black text-lime-500/70 dark:text-lime-400/50">404</span>
                </div>
                <div class="absolute -top-4 -right-4 w-20 h-20 bg-lime-500 rounded-full flex items-center justify-center shadow-lg animate-bounce">
                    <flux:icon name="exclamation-triangle" class="text-white text-2xl"></flux:icon>
                </div>
                <div class="absolute -bottom-4 -left-4 w-16 h-16 bg-lime-400 rounded-full flex items-center justify-center shadow-lg animate-pulse">
                    <flux:icon name="magnifying-glass-circle" class="text-white text-lg"></flux:icon>
                </div>
            </div>
        </div>
        
        <!-- Main Heading with Staggered Animation -->
        <div class="space-y-4 mb-8">
            <h1 class="text-5xl font-black text-gray-900 tracking-tight dark:text-white animate-pulse">
                Lost in Transit
            </h1>
            <div class="h-1 w-24 bg-gradient-to-r from-lime-400 to-lime-500 rounded-full mx-auto"></div>
        </div>

        <!-- Description -->
        <p class="text-lg text-gray-600 mb-10 max-w-md mx-auto leading-relaxed dark:text-gray-400">
            The page you're looking for has been rerouted or is no longer in service. Our logistics team has been notified.
        </p>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-8">
            <a href="/" class="px-8 py-4 bg-gradient-to-r from-lime-500 to-lime-600 text-white font-bold rounded-full shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 hover:-translate-y-1 flex items-center gap-2" wire:navigate>
                <flux:icon name="home" class="text-white"></flux:icon>
                Return to Home
            </a>
            <a href="https://wa.me/263772930514" class="px-6 py-4 bg-white text-lime-600 font-semibold border border-lime-300 rounded-full shadow-md hover:shadow-lg transition-all duration-300 transform hover:scale-105 flex items-center gap-2 dark:bg-slate-700 dark:text-lime-400 dark:border-lime-500/30" wire:navigate>
                <flux:icon name="speaker-wave" class="text-lime-600 dark:text-lime-400"></flux:icon>
                Contact Support
            </a>
        </div>

        <!-- Quick Links -->
        <div class="border-t border-gray-200 pt-6 dark:border-gray-700">
            <p class="text-sm text-gray-500 mb-4 dark:text-gray-500">Quick Navigation</p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="/lanes" class="text-lime-600 hover:text-lime-500 transition-colors duration-200 flex items-center gap-1 dark:text-lime-400" wire:navigate>
                    <flux:icon name="truck" class="text-xs"></flux:icon>
                    Vehicles
                </a>
                <a href="/freights" class="text-lime-600 hover:text-lime-500 transition-colors duration-200 flex items-center gap-1 dark:text-lime-400" wire:navigate>
                    <flux:icon name="cube" class="text-xs"></flux:icon>
                    Loads
                </a>
                <a href="/about-us" class="text-lime-600 hover:text-lime-500 transition-colors duration-200 flex items-center gap-1 dark:text-lime-400" wire:navigate>
                    <flux:icon name="information-circle" class="text-xs"></flux:icon>
                    About Us
                </a>
            </div>
        </div>
    </div>

    <!-- Background decorative elements -->
    <div class="fixed top-10 left-10 w-20 h-20 bg-lime-200 rounded-full blur-xl opacity-30 animate-pulse dark:bg-lime-900"></div>
    <div class="fixed bottom-10 right-10 w-32 h-32 bg-lime-300 rounded-full blur-xl opacity-20 animate-bounce dark:bg-lime-800"></div>
</div>

        
      
</x-layouts.app>