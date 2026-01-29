@props(['label'])

<button {{ $attributes }} type="button" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-xs font-medium text-zinc-600 dark:text-zinc-300 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition-all group">
    {{ $label }}
    <flux:icon name="x-mark" variant="mini" class="size-3 opacity-50 group-hover:opacity-100" />
</button>