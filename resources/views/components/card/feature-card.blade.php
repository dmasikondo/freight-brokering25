{{-- resources/views/components/Card.blade.php --}}
@props(['feature'])

<div class="group relative">
    <div
        class="absolute -inset-0.5 bg-gradient-to-r from-{{ $feature['color'] }}-500 to-green-500 rounded-2xl blur opacity-0 group-hover:opacity-20 transition duration-1000 group-hover:duration-200">
    </div>
    <div
        class="relative p-8 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm hover:shadow-xl transition-all duration-300 group-hover:border-{{ $feature['color'] }}-300 dark:group-hover:border-{{ $feature['color'] }}-500 h-full flex flex-col">
        @if (isset($feature['link']))
            <a href="{{ $feature['link'] }}" wire:navigate class="space-y-6 block flex-1">
            @else
                <span class="space-y-6 block flex-1"></span>
        @endif
        <div
            class="w-14 h-14 bg-gradient-to-br from-{{ $feature['color'] }}-500 to-{{ $feature['color'] }}-600 rounded-2xl flex items-center justify-center shadow-lg">
            <flux:icon name="{{ $feature['icon'] }}" class="w-7 h-7 text-white" />
        </div>
        <h3
            class="text-2xl font-bold text-zinc-900 dark:text-white group-hover:text-{{ $feature['color'] }}-600 dark:group-hover:text-{{ $feature['color'] }}-400 transition-colors">
            {{ $feature['title'] }}
        </h3>
        <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed flex-1">
            {{ $feature['description'] }}
        </p>

        </a>
        {{ $slot }}
    </div>
</div>
