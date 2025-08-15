{{-- resources/views/components/radio-button.blade.php --}}

@props([
    'id' => uniqid(),
    'value',
    'label',
    'model',
    'icon' => null,
    'disabled' => false,
])

<div
    x-data="{
        selected: @entangle($model)
    }"
    class="relative w-full"
>
    <label
        for="{{ $id }}"
        class="flex items-center p-3 gap-2 rounded-lg cursor-pointer transition-all duration-200
            {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}
            "
        :class="{
            'bg-gray-700 border border-blue-500': selected =='{{ $value }}',
            'bg-gray-800 border border-gray-700 hover:bg-gray-700': selected !='{{ $value }}'
        }"
    >
        <input
            type="radio"
            id="{{ $id }}"
            value="{{ $value }}"
            {{-- wire:model.live="{{ $model }}" --}}
            x-model=selected
            class="hidden peer"
            {{ $disabled ? 'disabled' : '' }}
            x-on:change="select('{{ $value }}')"
        />

        {{-- Custom visual radio button --}}
        <div class="relative flex items-center justify-center size-5 rounded-full border-2 transition-all duration-200"
            :class="{
                'bg-blue-500 border-blue-500': isChecked,
                'bg-gray-800 border-gray-400': !isChecked
            }"
        >
            <div x-show="isChecked" class="size-2 bg-white rounded-full transition-all duration-200"
                x-cloak
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="opacity-0 scale-0"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-0"
            ></div>
        </div>

        <span class="flex items-center gap-2 text-gray-300 select-none">
            {{-- This is where you can use your icon component --}}
            @if($icon)
                <x-graphic :name="$icon" class="size-5 text-yellow-400"/>
            @else
                {{-- Placeholder SVG if no icon is provided --}}
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-radio size-5 text-gray-400"
                    :class="{ 'text-blue-400': isChecked }">
                    <circle cx="12" cy="12" r="2" />
                    <path d="M16.24 7.76a6 6 0 0 1 0 8.49" />
                    <path d="M7.76 7.76a6 6 0 0 0 0 8.49" />
                </svg>
            @endif
            <span>{{ $label }}</span>
        </span>
    </label>
</div>
