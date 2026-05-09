{{--
    @component worksheet._toast
    Listens for Livewire dispatch('notify', message: '…') events.
    Place once inside your component's root div.
--}}
<div
    x-data="{ show: false, message: '', type: 'success' }"
    x-on:notify.window="
        show    = true;
        message = $event.detail.message;
        type    = $event.detail.type ?? 'success';
        setTimeout(() => show = false, 3500)
    "
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-500"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed bottom-6 right-6 z-50 flex items-center gap-3 px-5 py-3 rounded-2xl shadow-2xl border
           bg-slate-900 dark:bg-slate-800 text-white border-slate-700 dark:border-slate-600"
    style="display: none;">

    <span x-show="type === 'success'">
        <flux:icon.check-circle class="text-emerald-400 h-5 w-5 flex-shrink-0" />
    </span>
    <span x-show="type === 'error'">
        <flux:icon.exclamation-triangle class="text-red-400 h-5 w-5 flex-shrink-0" />
    </span>

    <span class="text-xs font-bold" x-text="message"></span>
</div>