{{--
    @component worksheet._progress-bar
    @prop  int     $completed
    @prop  int     $total
    @prop  bool    $muted     false = emerald colour, true = grey (for completed worksheets)
--}}
@props(['completed', 'total', 'muted' => false])

@php
    $pct   = $total > 0 ? round(($completed / $total) * 100) : 0;
    $fill  = $muted ? 'bg-slate-300 dark:bg-slate-600' : 'bg-emerald-500 dark:bg-emerald-400';
    $label = $muted ? 'text-slate-400 dark:text-slate-500' : 'text-emerald-600 dark:text-emerald-400';
@endphp

<div {{ $attributes }}>
    <div class="flex justify-between items-center mb-1.5">
        <span class="text-[10px] text-slate-400 dark:text-slate-500 font-bold">
            {{ $completed }} / {{ $total }} partners
        </span>
        <span class="text-[10px] font-black {{ $label }}">{{ $pct }}%</span>
    </div>
    <div class="w-full bg-slate-100 dark:bg-slate-700 h-1.5 rounded-full overflow-hidden">
        <div class="h-full rounded-full transition-all duration-500 {{ $fill }}" style="width: {{ $pct }}%"></div>
    </div>
</div>