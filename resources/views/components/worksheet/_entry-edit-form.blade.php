{{--
    @component worksheet._entry-edit-form
    @prop  int     $entryId           — the WorksheetEntry id
    @prop  string  $partnerName       — display name for the header
    @prop  string  $windowClosesAt    — human diff string, e.g. "in 6 hours"
    Expects the parent Livewire component to have:
        edit_activity, edit_feedback, edit_way_forward,
        edit_private_notes, edit_reminder_at  (wire:model targets)
    And methods: saveEdit($entryId), cancelEdit()
--}}
@props(['entryId', 'partnerName', 'windowClosesAt' => null])

<div class="p-5 bg-white dark:bg-slate-800 rounded-2xl border border-emerald-300 dark:border-emerald-700
            shadow-md space-y-4 animate-in fade-in duration-200">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="h-7 w-7 rounded-lg bg-emerald-600 text-white flex items-center justify-center">
                <flux:icon.pencil-square variant="micro" />
            </div>
            <div>
                <p class="font-black text-slate-900 dark:text-white text-sm leading-none">{{ $partnerName }}</p>
                @if ($windowClosesAt)
                    <p class="text-[10px] text-amber-600 dark:text-amber-400 mt-0.5">
                        Edit window closes {{ $windowClosesAt }}
                    </p>
                @endif
            </div>
        </div>
        <button wire:click="cancelEdit" type="button"
            class="cursor-pointer p-2 rounded-xl text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700
                   hover:text-slate-700 dark:hover:text-white transition-colors">
            <flux:icon.x-mark variant="micro" />
        </button>
    </div>

    {{-- Fields --}}
    <flux:textarea wire:model="edit_activity"      label="Action Taken"           rows="auto" />
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <flux:textarea wire:model="edit_feedback"      label="Partner Feedback"       rows="auto" />
        <flux:textarea wire:model="edit_way_forward"   label="Way Forward"            rows="auto" />
    </div>
    <flux:textarea wire:model="edit_private_notes" label="Private / Internal Notes" rows="auto" />
    <flux:input    type="datetime-local" wire:model="edit_reminder_at" label="Follow-up Reminder" />

    {{-- Actions --}}
    <div class="flex items-center gap-3 pt-1">
        <button wire:click="saveEdit({{ $entryId }})"
            wire:loading.attr="disabled"
            wire:target="saveEdit"
            type="button"
            class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700
                   dark:bg-emerald-500 dark:hover:bg-emerald-600 text-white text-sm font-bold rounded-xl
                   transition-colors disabled:opacity-60 disabled:cursor-not-allowed shadow-lg shadow-emerald-100">
            <span wire:loading.remove wire:target="saveEdit">
                <flux:icon.check variant="micro" />
            </span>
            <span wire:loading wire:target="saveEdit">
                <flux:icon.arrow-path variant="micro" class="animate-spin" />
            </span>
            <span wire:loading.remove wire:target="saveEdit">Save Changes</span>
            <span wire:loading wire:target="saveEdit">Saving…</span>
        </button>

        <button wire:click="cancelEdit" type="button"
            class="cursor-pointer px-4 py-2 text-sm font-bold text-slate-600 dark:text-slate-300
                   hover:text-slate-900 dark:hover:text-white rounded-xl border border-slate-200
                   dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
            Cancel
        </button>
    </div>
</div>