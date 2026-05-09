{{--
    @component worksheet._type-badge
    @prop  WorksheetType  $type
    @prop  string         $size   'sm' (default) | 'lg'
--}}
@props(['type', 'size' => 'sm'])

@php
    $textSize = $size === 'lg' ? 'text-[10px]' : 'text-[9px]';
    $padding  = $size === 'lg' ? 'px-2.5 py-1'  : 'px-2 py-0.5';
@endphp

<span {{ $attributes->merge([
    'class' => "{$padding} {$textSize} font-black uppercase rounded border {$type->badgeClasses()} select-none"
]) }}>
    {{ $type->label() }}
</span>