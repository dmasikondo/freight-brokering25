@props(['class' => 'size-6'])
<img src="{{ asset('storage/img/tpl_logo.jpg') }}" {{ $attributes->merge(['class' => $class]) }} />