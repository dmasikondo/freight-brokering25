<x-layouts.app :title="__('Dashboard')">
    <div class="p-6 space-y-6">
        <!-- Header Section -->
        <livewire:carrier.profile-completion-check :user="$user" />

        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Carrier Onboarding</h1>
                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <div class="text-sm text-gray-600 dark:text-gray-400">{{ $user->organisation }}: CARRIER ID</div>
                        <div class="font-mono font-bold text-lime-600 dark:text-lime-400">ZWHR012406001S</div>
                    </div>
                </div>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('lane.create') }}"
                    class="px-4 py-2 bg-lime-500 text-white rounded-lg hover:bg-lime-600 transition-colors flex items-center gap-2">
                    <flux:icon name="truck" class="w-5 h-5" />
                    Upload Vehicles
                </a>
            </div>
        </div>

        <!-- Form Completion Status -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <livewire:carrier.director.status-info :user="$user" />

            <livewire:carrier.fleet.status-info :user="$user" />

            <livewire:carrier.traderef.status-info :user="$user" />
        </div>

        <livewire:carrier.document-upload :user="$user" />

        <livewire:carrier.recent-file-uploads :user="$user" />
    </div>

</x-layouts.app>
