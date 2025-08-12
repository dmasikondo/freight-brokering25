
@props([
    'type' => 'text',
    'model' => null,
     'required' => false,
    'disabled' => false,
    'placeholder' => '',
    'value' => '',
    'hasError'=>false,
])

<div x-data="{ focused: false, hasValue: !!$el.querySelector('input').value }" class="relative w-full">
    <label 
        x-show="focused || hasValue" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 transform translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        class="absolute left-3 top-0 px-1 bg-gray-800 text-xs text-gray-300 transform -translate-y-1/2"
>
    {{ $placeholder }}
    </label>

<input
    type="{{ $type }}"
    x-model="{{ $model }}"
    @if($value) value="{{ $value }}" @endif
    placeholder="{{ $placeholder }}"
    @focus="focused = true" 
    @blur="focused = false"
    x-on:input="hasValue = !!$event.target.value"
    {{ $disabled ? 'disabled' : '' }}
    {{ $required ? 'required' : '' }}
    {!! $attributes->merge(['class' => "w-full p-3 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-white transition-all duration-200"
    ]) !!}>
 
</div>

