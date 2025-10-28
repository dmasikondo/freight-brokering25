{{-- resources/views/components/Tabs.blade.php --}}
@props(['tabs', 'class' => 'md:w-2/3 md:pl-8'])

<div class="{{ $class }}" x-data="{ activeTab: '{{ $tabs[0]['name'] }}' }">
    <!-- Tab Navigation -->
    <div class="flex relative mb-6">
        @foreach($tabs as $tab)
            <button
                @click="activeTab = '{{ $tab['name'] }}'"
                :class="{ 'z-10 bg-white dark:bg-gray-800 text-emerald-600 dark:text-emerald-400 shadow-lg border-b-2 border-r-2 border-emerald-600 dark:border-emerald-400': activeTab === '{{ $tab['name'] }}' }"
                class="relative -mr-px py-2 px-6 font-semibold text-gray-500 dark:text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors duration-200"
            >
                {{ $tab['label'] }}
            </button>
        @endforeach
        <div class="absolute bottom-0 left-0 w-full h-px bg-gray-200 dark:bg-gray-700"></div>
    </div>
    
    <!-- Tab Content -->
    @foreach($tabs as $tab)
        <div x-show="activeTab === '{{ $tab['name'] }}'">
            {{ $tab['content'] }}
        </div>
    @endforeach
</div>