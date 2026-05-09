{{--
    @component worksheet._nav-link
    A lime/emerald anchor styled as a button for page navigation.
    @prop  string  $href
    @prop  string  $icon   (optional Heroicon name)
    @prop  string  $size   'sm' | 'md' (default)
--}}
@props(['href', 'icon' => null, 'size' => 'md'])

@php
    $pad  = $size === 'sm' ? 'px-3 py-1.5 text-[10px]' : 'px-4 py-2 text-xs';
@endphp

<a href="{{ $href }}" wire:navigate
   class="cursor-pointer inline-flex items-center gap-1.5 {{ $pad }} font-black uppercase tracking-widest
          rounded-xl bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-500 dark:hover:bg-emerald-600
          text-white transition-colors shadow-sm"
   {{ $attributes->except(['href', 'icon', 'size']) }}>
    @if ($icon)
        <flux:icon.{{ $icon }} variant="micro" />
    @endif
    {{ $slot }}
</a>