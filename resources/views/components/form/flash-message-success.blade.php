@if (session()->has('message'))
    {{-- 1. Initialize an Alpine component to manage the state and timer --}}
    <div 
        x-data="{ show: true }" 
        x-init="setTimeout(() => { show = false }, 5000)"
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate-x-4"
        x-transition:enter-end="opacity-100 transform translate-x-0"
        x-transition:leave="transition ease-in duration-500"
        x-transition:leave-start="opacity-100 transform translate-x-0"
        x-transition:leave-end="opacity-0 transform translate-x-4"
        class="z-50"
    >
        <flux:callout icon="check-circle" color="green" class="shadow-lg">
            <flux:callout.heading class="text-green-900 dark:text-green-100">Success!</flux:callout.heading>
            <flux:callout.text class="text-green-800 dark:text-green-200">
                {{ session('message') }}
            </flux:callout.text>
        </flux:callout>
    </div>
@endif