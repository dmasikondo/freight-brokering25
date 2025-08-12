@props([
    'name' => '',
    'model' => null,
    'required' => false,
    'disabled' => false,
    'placeholder' => '',
    'options' => [],
    'selected' => '',
])

<div x-data="{ focused: false, hasValue: !!$el.querySelector('select').value }" class="relative w-full">
    <label 
        x-show="focused || hasValue" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 transform translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        class="absolute left-3 top-0 px-1 bg-gray-800 text-xs text-gray-300 transform -translate-y-1/2"
    >
        {{ $placeholder }}
    </label>
    
    <select
        name="{{ $name }}"
        x-model="{{ $model ? $model : '' }}"
        @if($selected) value="{{ $selected }}" @endif
        @focus="focused = true" 
        @blur="focused = false"
        x-on:change="hasValue = !!$event.target.value"
        {{ $disabled ? 'disabled' : '' }}
        {{ $required ? 'required' : '' }}
        {!! $attributes->merge(['class' => 'w-full p-3 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-white transition-all duration-200 appearance-none']) !!}
    >
        <option value="" disabled selected>{{ $placeholder }}</option>
        @foreach($options as $value => $label)
            <option value="{{ $value }}" @if($selected == $value) selected @endif>{{ $label }}</option>
        @endforeach
    </select>
    
    <!-- Dropdown arrow icon -->
    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </div>
</div>