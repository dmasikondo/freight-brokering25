<?php

use App\Models\WorksheetHeader;
use App\Models\WorksheetEntry;
use App\Models\User;
use App\Enums\WorksheetType;
use App\Enums\PartnerType;
use App\Enums\PlannedAction;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component {

    public WorksheetHeader $worksheet;

    // ── Edit-in-place ─────────────────────────────────────────────
    public ?int   $editing_entry_id     = null;
    public string $edit_activity        = '';
    public string $edit_feedback        = '';
    public string $edit_way_forward     = '';
    public string $edit_private_notes   = '';
    public string $edit_reminder_at     = '';

    // ── Add partner ───────────────────────────────────────────────
    public bool   $show_add_partner     = false;
    public        $p_id                 = null;
    public string $p_name               = '';
    public string $p_contact            = '';
    public string $p_type               = 'general';
    public string $p_planned_action     = '';
    public string $p_planned_custom     = '';

    // ── Share modal ───────────────────────────────────────────────
    public array  $selectedStaff        = [];
    public string $staff_search         = '';

    // ────────────────────────────────────────────────────────────
    // with()
    // ────────────────────────────────────────────────────────────
    public function with(): array
    {
        $this->worksheet->loadMissing(['user', 'sharedWith', 'entries.lastEditor']);

        $entries        = $this->worksheet->entries;
        $totalCount     = $entries->count();
        $completedCount = $entries->whereNotNull('completed_at')->count();
        $progress       = $totalCount > 0 ? round(($completedCount / $totalCount) * 100) : 0;

        return [
            ...compact('entries', 'totalCount', 'completedCount', 'progress'),
            'partnerTypes'     => PartnerType::cases(),
            'plannedActions'   => PlannedAction::cases(),
            'available_partners' => $this->p_name
                ? User::where('contact_person', 'like', "%{$this->p_name}%")->limit(5)->get()
                : collect(),
            'available_staff'  => User::where('id', '!=', Auth::id())
                ->whereHas('roles', fn($q) => $q->whereIn('name', [
                    'superadmin', 'admin',
                    'marketing logistics associate',
                    'procurement logistics associate',
                    'operations logistics associate',
                    'logistics operations executive',
                ]))
                ->when($this->staff_search, fn($q) =>
                    $q->whereAny(['users.contact_person', 'users.email'], 'like', "%{$this->staff_search}%"))
                ->get(),
        ];
    }

    // ────────────────────────────────────────────────────────────
    // Edit helpers
    // ────────────────────────────────────────────────────────────
    public function openEdit(int $id): void
    {
        $entry = WorksheetEntry::findOrFail($id);

        if (! auth()->user()->can('updateEntry', [$entry->header, $entry])) {
            $this->addError('edit_lock', $entry->editLockReason() ?? 'You do not have permission to edit this entry.');
            return;
        }

        $this->editing_entry_id   = $id;
        $this->edit_activity      = $entry->activity      ?? '';
        $this->edit_feedback      = $entry->feedback      ?? '';
        $this->edit_way_forward   = $entry->way_forward   ?? '';
        $this->edit_private_notes = $entry->private_notes ?? '';
        $this->edit_reminder_at   = $entry->reminder_at?->format('Y-m-d\TH:i') ?? '';
    }

    public function cancelEdit(): void
    {
        $this->editing_entry_id = null;
        $this->reset(['edit_activity', 'edit_feedback', 'edit_way_forward',
                      'edit_private_notes', 'edit_reminder_at']);
    }

    public function saveEdit(int $id): void
    {
        $entry = WorksheetEntry::findOrFail($id);

        if (! auth()->user()->can('updateEntry', [$entry->header, $entry])) {
            $this->addError('edit_lock', $entry->editLockReason() ?? 'You do not have permission to edit this entry.');
            return;
        }

        $this->validate([
            'edit_activity'      => 'required|string',
            'edit_feedback'      => 'required|string',
            'edit_way_forward'   => 'required|string',
            'edit_reminder_at'   => 'nullable|date',
            'edit_private_notes' => 'nullable|string',
        ]);

        $entry->update([
            'activity'          => $this->edit_activity,
            'feedback'          => $this->edit_feedback,
            'way_forward'       => $this->edit_way_forward,
            'private_notes'     => $this->edit_private_notes,
            'reminder_at'       => $this->edit_reminder_at ?: null,
            'last_edited_by_id' => Auth::id(),
        ]);

        $this->worksheet->load('entries.lastEditor');
        $this->cancelEdit();
        $this->dispatch('notify', message: 'Entry updated successfully.');
    }

    // ────────────────────────────────────────────────────────────
    // Add partner helpers
    // ────────────────────────────────────────────────────────────
    public function toggleAddPartner(): void
    {
        $this->show_add_partner = ! $this->show_add_partner;
        if (! $this->show_add_partner) {
            $this->resetAddPartner();
        }
    }

    public function selectPartner(int $id, string $name, ?string $phone, ?string $whatsapp): void
    {
        $this->p_id   = $id;
        $this->p_name = $name;

        $details = [];
        if ($phone)    $details[] = "Phone: {$phone}";
        if ($whatsapp) $details[] = "WA: {$whatsapp}";
        $this->p_contact = implode(' | ', $details);
    }

    public function addPartner(): void
    {
        // Gate: sequence must still be open
        if ($this->worksheet->sequenceLocked()) {
            $this->addError('add_partner', 'The 8-hour sequence window has closed. No new partners can be added.');
            return;
        }

        $this->authorize('update', $this->worksheet);

        $this->validate([
            'p_name'           => 'required|string|max:255',
            'p_contact'        => 'required|string|max:255',
            'p_type'           => 'required|string',
            'p_planned_action' => 'nullable|string',
            'p_planned_custom' => 'nullable|string|required_if:p_planned_action,custom|max:255',
        ]);

        $nextOrder = $this->worksheet->entries()->max('sort_order') + 1;

        WorksheetEntry::create([
            'header_id'             => $this->worksheet->id,
            'partner_name'          => $this->p_name,
            'contact_details'       => $this->p_contact,
            'partner_type'          => $this->p_type,
            'planned_action'        => $this->p_planned_action ?: null,
            'planned_action_custom' => $this->p_planned_action === 'custom' ? $this->p_planned_custom : null,
            'sort_order'            => $nextOrder,
        ]);

        $this->worksheet->load('entries.lastEditor');
        $this->resetAddPartner();
        $this->dispatch('notify', message: 'Partner added to the sequence.');
    }

    private function resetAddPartner(): void
    {
        $this->reset(['p_id', 'p_name', 'p_contact', 'p_type', 'p_planned_action', 'p_planned_custom']);
    }

    // ────────────────────────────────────────────────────────────
    // Share helpers
    // ────────────────────────────────────────────────────────────
    public function openShareModal(): void
    {
        // Only the owner may share
        abort_unless($this->worksheet->user_id === Auth::id(), 403);
        $this->worksheet->loadMissing('sharedWith');
        $this->selectedStaff = $this->worksheet->sharedWith->pluck('id')->toArray();
        $this->modal('share-modal')->show();
    }

    public function toggleShare(int $userId): void
    {
        abort_unless($this->worksheet->user_id === Auth::id(), 403);

        $targetUser = User::findOrFail($userId);

        if (in_array($userId, $this->selectedStaff)) {
            $this->worksheet->sharedWith()->detach($userId);
            $this->selectedStaff = array_values(array_diff($this->selectedStaff, [$userId]));
            $targetUser->notify(new \App\Notifications\WorksheetSharedNotification($this->worksheet, 'withdrawn'));
            $this->dispatch('notify', message: 'Access withdrawn from ' . $targetUser->contact_person . '.');
        } else {
            $this->worksheet->sharedWith()->attach($userId);
            $this->selectedStaff[] = $userId;
            $targetUser->notify(new \App\Notifications\WorksheetSharedNotification($this->worksheet, 'granted'));
            $this->dispatch('notify', message: 'Access granted to ' . $targetUser->contact_person . '.');
        }

        // Reload sharedWith so the collaborator list in the hero updates
        $this->worksheet->load('sharedWith');
    }

    // ────────────────────────────────────────────────────────────
    // mount
    // ────────────────────────────────────────────────────────────
    public function mount(WorksheetHeader $worksheet): void
    {
        $user = auth()->user();

        $canView = $worksheet->user_id === $user->id
            || $worksheet->sharedWith->contains($user->id)
            || $user->hasAnyRole(['admin', 'superadmin']);

        abort_unless($canView, 403);

        $this->worksheet = $worksheet;
    }
}; ?>

<div class="min-h-screen bg-slate-50 dark:bg-slate-950">

    <x-worksheet._toast />

    {{-- ════════════════════════════════════════════════════════════
         STICKY TOP NAV
    ════════════════════════════════════════════════════════════ --}}
    <div class="sticky top-0 z-20 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800">
        <div class="max-w-7xl mx-auto px-4 md:px-8 h-14 flex items-center justify-between gap-3">

            {{-- Breadcrumb --}}
            <div class="flex items-center gap-2 min-w-0">
                <a href="{{ route('worksheets.index') }}" wire:navigate
                   class="cursor-pointer flex items-center gap-1 text-sm font-bold
                          text-slate-500 dark:text-slate-400 hover:text-emerald-600
                          dark:hover:text-emerald-400 transition-colors flex-shrink-0">
                    <flux:icon.chevron-left variant="micro" />
                    Worksheets
                </a>
                <span class="text-slate-300 dark:text-slate-600">/</span>
                <span class="text-sm font-black text-slate-800 dark:text-white truncate">
                    {{ $worksheet->name }}
                </span>
            </div>

            {{-- Right actions --}}
            <div class="flex items-center gap-2 flex-shrink-0">
                <x-worksheet._type-badge :type="$worksheet->worksheet_type" class="hidden sm:inline-flex" />

                <span class="hidden sm:inline-flex px-2 py-0.5 text-[9px] font-black uppercase rounded
                    {{ $worksheet->is_completed
                        ? 'bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-600'
                        : 'bg-emerald-50 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800' }}">
                    {{ $worksheet->is_completed ? 'Completed' : 'Active' }}
                </span>

                {{-- Share button — owner only --}}
                @if ($worksheet->user_id === auth()->id())
                    <button type="button"
                        wire:click="openShareModal"
                        wire:loading.attr="disabled"
                        wire:target="openShareModal"
                        class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 text-[10px]
                               font-black uppercase tracking-widest rounded-xl border
                               border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300
                               hover:border-emerald-500 hover:text-emerald-700 dark:hover:border-emerald-500
                               dark:hover:text-emerald-400 bg-white dark:bg-slate-800 transition-all
                               disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="openShareModal">
                            <flux:icon.share variant="micro" />
                        </span>
                        <span wire:loading wire:target="openShareModal">
                            <flux:icon.arrow-path variant="micro" class="animate-spin" />
                        </span>
                        Share
                    </button>
                @endif

                {{-- Continue button — for active worksheets --}}
                @if (!$worksheet->is_completed)
                    @can('update', $worksheet)
                        <a href="{{ route('worksheets.create') }}" wire:navigate
                           class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 text-[10px]
                                  font-black uppercase tracking-widest rounded-xl bg-emerald-600
                                  hover:bg-emerald-700 dark:bg-emerald-500 dark:hover:bg-emerald-600
                                  text-white transition-colors shadow-sm">
                            <flux:icon.play variant="micro" />
                            Continue
                        </a>
                    @endcan
                @endif
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 md:px-8 py-8 space-y-8">

        {{-- ════════════════════════════════════════════════════════
             HERO CARD
        ════════════════════════════════════════════════════════ --}}
        <div class="bg-slate-900 dark:bg-slate-800 rounded-3xl overflow-hidden shadow-2xl">

            {{-- Type accent strip --}}
            <div class="h-1 w-full {{ match($worksheet->worksheet_type) {
                WorksheetType::Scouting => 'bg-emerald-500',
                WorksheetType::Daily    => 'bg-sky-500',
                WorksheetType::Weekly   => 'bg-violet-500',
                WorksheetType::Monthly  => 'bg-amber-500',
            } }}"></div>

            <div class="p-6 md:p-10">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

                    {{-- Left: title & meta --}}
                    <div class="lg:col-span-2 space-y-5">
                        <div>
                            <div class="flex items-center gap-3 mb-3 flex-wrap">
                                <x-worksheet._type-badge :type="$worksheet->worksheet_type" size="lg" />

                                @if ($worksheet->is_completed)
                                    <span class="flex items-center gap-1 px-2.5 py-1 text-[9px] font-black uppercase
                                                 rounded-lg bg-slate-700 text-slate-300 border border-slate-600">
                                        <flux:icon.check-circle variant="micro" class="text-emerald-400" />
                                        Completed
                                    </span>
                                @else
                                    <span class="flex items-center gap-1 px-2.5 py-1 text-[9px] font-black uppercase
                                                 rounded-lg bg-emerald-900/50 text-emerald-400 border border-emerald-800">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                        Active
                                    </span>
                                @endif
                            </div>

                            <h1 class="text-3xl md:text-4xl font-black text-white tracking-tight">
                                {{ $worksheet->name }}
                            </h1>
                        </div>

                        {{-- Meta cards --}}
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            <div class="bg-slate-800/70 rounded-2xl p-4 border border-slate-700/50">
                                <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1">Initiated By</p>
                                <a href="{{ route('users.show', $worksheet->user->slug) }}" wire:navigate
                                   class="cursor-pointer text-sm font-bold text-emerald-400 hover:text-emerald-300 transition-colors">
                                    {{ $worksheet->user->contact_person }}
                                </a>
                                <p class="text-[10px] text-slate-500 mt-0.5 truncate">{{ $worksheet->user->email }}</p>
                            </div>

                            <div class="bg-slate-800/70 rounded-2xl p-4 border border-slate-700/50">
                                <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1">Opened</p>
                                <p class="text-sm font-bold text-white">{{ $worksheet->created_at->format('d M Y') }}</p>
                                <p class="text-[10px] text-slate-500 mt-0.5">
                                    {{ $worksheet->created_at->format('H:i') }} · {{ $worksheet->created_at->diffForHumans() }}
                                </p>
                            </div>

                            <div class="bg-slate-800/70 rounded-2xl p-4 border border-slate-700/50">
                                @if ($worksheet->is_completed)
                                    <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1">Finalised</p>
                                    <p class="text-sm font-bold text-white">{{ $worksheet->updated_at->format('d M Y') }}</p>
                                    <p class="text-[10px] text-slate-500 mt-0.5">{{ $worksheet->updated_at->diffForHumans() }}</p>
                                @else
                                    <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1">Sequence Window</p>
                                    @if ($worksheet->sequenceLocked())
                                        <p class="text-sm font-bold text-red-400">Locked</p>
                                        <p class="text-[10px] text-slate-500 mt-0.5">No new partners can be added</p>
                                    @else
                                        @php $seqMins = $worksheet->sequenceMinutesRemaining(); @endphp
                                        <p class="text-sm font-bold text-amber-400">
                                            {{ intdiv($seqMins, 60) }}h {{ $seqMins % 60 }}m left
                                        </p>
                                        <p class="text-[10px] text-slate-500 mt-0.5">To add partners</p>
                                    @endif
                                @endif
                            </div>
                        </div>

                        {{-- Collaborators list --}}
                        @if ($worksheet->sharedWith->isNotEmpty())
                            <div>
                                <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-2">
                                    Collaborators
                                </p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($worksheet->sharedWith as $collab)
                                        <a href="{{ route('users.show', $collab->slug) }}" wire:navigate
                                           class="cursor-pointer flex items-center gap-2 px-3 py-1.5 rounded-xl
                                                  bg-slate-800 border border-slate-700
                                                  hover:border-emerald-600 transition-all group">
                                            <div class="h-5 w-5 rounded-full bg-emerald-900 flex items-center justify-center
                                                        text-[8px] font-black text-emerald-300">
                                                {{ strtoupper(substr($collab->contact_person, 0, 1)) }}
                                            </div>
                                            <span class="text-xs font-bold text-slate-300 group-hover:text-emerald-400 transition-colors">
                                                {{ $collab->contact_person }}
                                            </span>
                                        </a>
                                    @endforeach

                                    {{-- Quick-add via share button --}}
                                    @if ($worksheet->user_id === auth()->id())
                                        <button type="button" wire:click="openShareModal"
                                            class="cursor-pointer flex items-center gap-1.5 px-3 py-1.5 rounded-xl
                                                   bg-slate-800 border border-slate-700 border-dashed
                                                   hover:border-emerald-600 hover:text-emerald-400 text-slate-500
                                                   text-xs font-bold transition-all">
                                            <flux:icon.plus variant="micro" />
                                            Manage
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @elseif ($worksheet->user_id === auth()->id())
                            {{-- No collaborators yet — show invite nudge --}}
                            <button type="button" wire:click="openShareModal"
                                class="cursor-pointer self-start flex items-center gap-2 px-3 py-2 rounded-xl
                                       bg-slate-800/60 border border-slate-700 border-dashed
                                       hover:border-emerald-600 hover:text-emerald-400 text-slate-500
                                       text-xs font-bold transition-all">
                                <flux:icon.user-plus variant="micro" />
                                Share with colleagues
                            </button>
                        @endif
                    </div>

                    {{-- Right: circular progress ring + stat pills --}}
                    <div class="flex flex-col items-center gap-5">
                        <div class="relative h-36 w-36">
                            <svg class="h-full w-full -rotate-90" viewBox="0 0 120 120">
                                <circle cx="60" cy="60" r="50" fill="none" stroke="#1e293b" stroke-width="10"/>
                                <circle cx="60" cy="60" r="50" fill="none"
                                    stroke="{{ $worksheet->is_completed ? '#6b7280' : '#10b981' }}"
                                    stroke-width="10"
                                    stroke-linecap="round"
                                    stroke-dasharray="{{ round(2 * M_PI * 50, 2) }}"
                                    stroke-dashoffset="{{ round(2 * M_PI * 50 * (1 - $progress / 100), 2) }}"
                                    class="transition-all duration-1000"/>
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-3xl font-black {{ $worksheet->is_completed ? 'text-slate-400' : 'text-emerald-400' }}">
                                    {{ $progress }}%
                                </span>
                                <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Done</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 w-full">
                            <div class="bg-slate-800/70 rounded-2xl p-3 text-center border border-slate-700/50">
                                <p class="text-2xl font-black text-white">{{ $completedCount }}</p>
                                <p class="text-[9px] text-slate-500 font-black uppercase tracking-widest mt-0.5">Done</p>
                            </div>
                            <div class="bg-slate-800/70 rounded-2xl p-3 text-center border border-slate-700/50">
                                <p class="text-2xl font-black text-white">{{ $totalCount - $completedCount }}</p>
                                <p class="text-[9px] text-slate-500 font-black uppercase tracking-widest mt-0.5">Pending</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Edit lock / add partner errors --}}
        @foreach (['edit_lock', 'add_partner'] as $errKey)
            @if ($errors->has($errKey))
                <div class="p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800
                            rounded-2xl text-red-700 dark:text-red-400 text-sm flex items-center gap-3">
                    <flux:icon.exclamation-triangle variant="micro" class="flex-shrink-0" />
                    {{ $errors->first($errKey) }}
                </div>
            @endif
        @endforeach

        {{-- ════════════════════════════════════════════════════════
             PARTNER INTERACTION LOG
        ════════════════════════════════════════════════════════ --}}
        <div>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">
                    Partner Interaction Log
                </h2>
                <span class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                    {{ $totalCount }} Partners
                </span>
            </div>

            {{-- Desktop column headers --}}
            <div class="hidden md:grid grid-cols-12 gap-4 px-5 pb-2 border-b
                        border-slate-200 dark:border-slate-700
                        text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                <div class="col-span-1">#</div>
                <div class="col-span-2">Partner</div>
                <div class="col-span-2">Planned / Done</div>
                <div class="col-span-2">Feedback</div>
                <div class="col-span-2">Way Forward</div>
                <div class="col-span-2">Follow-up</div>
                <div class="col-span-1">Action</div>
            </div>

            <div class="mt-2 space-y-2">
                @foreach ($entries as $ent)
                    @php
                        $isDone    = ! is_null($ent->completed_at);
                        $canEdit   = $isDone && auth()->user()->can('updateEntry', [$ent->header, $ent]);
                        $isEditing = $editing_entry_id === $ent->id;
                        $isOwner   = $worksheet->user_id === auth()->id();
                    @endphp

                    <div class="bg-white dark:bg-slate-900 border rounded-2xl transition-all
                        {{ $isEditing
                            ? 'border-emerald-400 dark:border-emerald-600 ring-2 ring-emerald-100 dark:ring-emerald-900 shadow-lg'
                            : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 hover:shadow-sm' }}
                        {{ !$isDone ? 'opacity-60 dark:opacity-40' : '' }}">

                        @if (! $isEditing)
                            {{-- Collapsed row --}}
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-x-4 gap-y-2 p-4 md:p-5 items-start">

                                {{-- # --}}
                                <div class="md:col-span-1 flex items-center">
                                    <div class="h-8 w-8 rounded-xl flex items-center justify-center font-black text-sm flex-shrink-0
                                        {{ $isDone
                                            ? 'bg-emerald-600 dark:bg-emerald-500 text-white shadow-md shadow-emerald-200 dark:shadow-emerald-900'
                                            : 'bg-slate-100 dark:bg-slate-700 text-slate-400 dark:text-slate-500 border border-slate-200 dark:border-slate-600' }}">
                                        {{ $loop->iteration }}
                                    </div>
                                </div>

                                {{-- Partner --}}
                                <div class="md:col-span-2">
                                    <p class="font-black text-slate-900 dark:text-white text-sm">{{ $ent->partner_name }}</p>
                                    <span class="inline-block mt-0.5 px-1.5 py-0.5 bg-slate-100 dark:bg-slate-700
                                                 text-slate-500 dark:text-slate-400 text-[8px] font-black rounded uppercase">
                                        {{ $ent->partner_type->label() }}
                                    </span>
                                    <p class="text-[9px] text-slate-400 dark:text-slate-500 mt-1 leading-snug">
                                        {{ $ent->contact_details }}
                                    </p>
                                </div>

                                {{-- Planned / Activity --}}
                                <div class="md:col-span-2">
                                    @if ($ent->planned_action)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 mb-1.5
                                                     bg-emerald-50 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400
                                                     text-[9px] font-black rounded border border-emerald-200 dark:border-emerald-800 uppercase">
                                            {{ $ent->plannedActionLabel() }}
                                        </span>
                                    @endif
                                    @if ($isDone && $ent->activity)
                                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                                            {{ Str::limit($ent->activity, 80) }}
                                        </p>
                                    @elseif (!$isDone)
                                        <p class="text-[10px] text-slate-300 dark:text-slate-600 italic">Not started</p>
                                    @endif
                                </div>

                                {{-- Feedback --}}
                                <div class="md:col-span-2">
                                    @if ($isDone && $ent->feedback)
                                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                                            {{ Str::limit($ent->feedback, 80) }}
                                        </p>
                                    @else
                                        <p class="text-[10px] text-slate-300 dark:text-slate-600 italic">—</p>
                                    @endif
                                </div>

                                {{-- Way Forward --}}
                                <div class="md:col-span-2">
                                    @if ($isDone && $ent->way_forward)
                                        <p class="text-xs text-emerald-700 dark:text-emerald-400 font-bold leading-relaxed">
                                            {{ Str::limit($ent->way_forward, 80) }}
                                        </p>
                                        @if ($ent->last_edited_by_id && $ent->last_edited_by_id !== $worksheet->user_id)
                                            <p class="text-[9px] text-slate-400 dark:text-slate-500 mt-1 italic">
                                                Edited by {{ $ent->lastEditor?->contact_person }}
                                            </p>
                                        @endif
                                    @else
                                        <p class="text-[10px] text-slate-300 dark:text-slate-600 italic">—</p>
                                    @endif
                                </div>

                                {{-- Follow-up --}}
                                <div class="md:col-span-2">
                                    @if ($ent->reminder_at)
                                        <div class="inline-flex flex-col px-2.5 py-1.5
                                                    bg-orange-50 dark:bg-orange-900/30
                                                    border border-orange-100 dark:border-orange-800 rounded-xl">
                                            <span class="text-[8px] font-black text-orange-400 uppercase">Follow-up</span>
                                            <span class="text-xs font-bold text-orange-700 dark:text-orange-400">
                                                {{ $ent->reminder_at->format('d M, H:i') }}
                                            </span>
                                            <span class="text-[9px] text-orange-500 italic">
                                                {{ $ent->reminder_at->diffForHumans() }}
                                            </span>
                                        </div>
                                    @else
                                        <p class="text-[10px] text-slate-300 dark:text-slate-600 italic">None</p>
                                    @endif
                                </div>

                                {{-- Status / Edit action --}}
                                <div class="md:col-span-1 flex flex-col items-start md:items-end gap-1.5">
                                    @if ($isDone)
                                        <div class="flex items-center gap-1 text-[9px] font-black text-emerald-600 dark:text-emerald-400 uppercase">
                                            <flux:icon.check-circle variant="micro" />
                                            Done
                                        </div>
                                        <span class="text-[9px] text-slate-400 dark:text-slate-500">
                                            {{ $ent->completed_at->format('H:i') }}
                                        </span>

                                        @if ($canEdit)
                                            <button wire:click="openEdit({{ $ent->id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="openEdit({{ $ent->id }})"
                                                type="button"
                                                class="cursor-pointer mt-1 flex items-center gap-1 px-2.5 py-1 rounded-lg
                                                       bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-600
                                                       hover:border-emerald-400 dark:hover:border-emerald-600
                                                       hover:bg-emerald-50 dark:hover:bg-emerald-900/30
                                                       text-[9px] font-black text-slate-500 dark:text-slate-400
                                                       hover:text-emerald-700 dark:hover:text-emerald-400 transition-all
                                                       disabled:opacity-50 disabled:cursor-not-allowed">
                                                <span wire:loading.remove wire:target="openEdit({{ $ent->id }})">
                                                    <flux:icon.pencil-square variant="micro" />
                                                </span>
                                                <span wire:loading wire:target="openEdit({{ $ent->id }})">
                                                    <flux:icon.arrow-path variant="micro" class="animate-spin" />
                                                </span>
                                                Edit
                                            </button>
                                        @else
                                            <span title="{{ $ent->editLockReason() }}"
                                                class="mt-1 flex items-center gap-1 px-2.5 py-1 rounded-lg
                                                       text-[9px] font-black text-slate-300 dark:text-slate-600 cursor-not-allowed">
                                                <flux:icon.lock-closed variant="micro" />
                                                Locked
                                            </span>
                                        @endif

                                        {{-- Private notes (owner only) --}}
                                        @if ($isOwner && $ent->private_notes)
                                            <div class="w-full mt-2 p-2.5 bg-amber-50 dark:bg-amber-900/20
                                                        border border-amber-100 dark:border-amber-800 rounded-xl">
                                                <p class="flex items-center gap-1 text-[8px] font-black
                                                          text-amber-600 dark:text-amber-400 uppercase mb-1">
                                                    <flux:icon.lock-closed variant="micro" /> Private
                                                </p>
                                                <p class="text-[10px] text-amber-800 dark:text-amber-300 italic leading-snug">
                                                    {{ $ent->private_notes }}
                                                </p>
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-[9px] font-black text-slate-400 dark:text-slate-600 uppercase">Pending</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Timing strip --}}
                            @if ($isDone)
                                <div class="border-t border-slate-50 dark:border-slate-800 px-5 py-2.5
                                            flex flex-wrap items-center gap-4
                                            text-[9px] text-slate-400 dark:text-slate-500 font-medium
                                            bg-slate-50/50 dark:bg-slate-800/30 rounded-b-2xl">
                                    @if ($ent->started_at)
                                        <span class="flex items-center gap-1">
                                            <flux:icon.play variant="micro" class="text-slate-300 dark:text-slate-600" />
                                            Started {{ $ent->started_at->format('H:i') }}
                                        </span>
                                        <span class="text-slate-200 dark:text-slate-700">·</span>
                                    @endif
                                    <span class="flex items-center gap-1">
                                        <flux:icon.check variant="micro" class="text-emerald-400" />
                                        Completed {{ $ent->completed_at->format('H:i') }}
                                    </span>
                                    @if ($ent->started_at)
                                        <span class="text-slate-200 dark:text-slate-700">·</span>
                                        <span>Duration: {{ $ent->started_at->diffForHumans($ent->completed_at, true) }}</span>
                                    @endif
                                    @if ($canEdit)
                                        @php $editMins = $ent->entryEditWindowMinutesRemaining(); @endphp
                                        <span class="ml-auto flex items-center gap-1 text-amber-500 dark:text-amber-400 font-black uppercase">
                                            <flux:icon.clock variant="micro" />
                                            Edit window: {{ intdiv($editMins, 60) }}h {{ $editMins % 60 }}m
                                        </span>
                                    @endif
                                </div>
                            @endif

                        @else
                            {{-- Expanded edit form --}}
                            @php
                                $windowClosesAt = $ent->completed_at
                                    ->addHours(WorksheetEntry::ENTRY_EDIT_WINDOW_HOURS)
                                    ->diffForHumans();
                            @endphp
                            <div class="p-4">
                                <x-worksheet._entry-edit-form
                                    :entry-id="$ent->id"
                                    :partner-name="$ent->partner_name"
                                    :window-closes-at="$windowClosesAt" />
                            </div>
                        @endif
                    </div>
                @endforeach

                @if ($entries->isEmpty())
                    <div class="py-20 bg-white dark:bg-slate-900 rounded-2xl border-2 border-dashed
                                border-slate-200 dark:border-slate-700 text-center">
                        <flux:icon.document-text class="h-10 w-10 text-slate-300 dark:text-slate-600 mx-auto mb-3" />
                        <p class="font-bold text-slate-600 dark:text-slate-400">No partners in this worksheet yet.</p>
                    </div>
                @endif
            </div>

            {{-- ════════════════════════════════════════════════════
                 ADD PARTNER PANEL
                 Shown only when the sequence window is still open
                 and the user has update permission.
            ════════════════════════════════════════════════════ --}}
            @if (!$worksheet->is_completed && !$worksheet->sequenceLocked())
                @can('update', $worksheet)
                    <div class="mt-4">
                        {{-- Toggle button --}}
                        @if (!$show_add_partner)
                            <button type="button"
                                wire:click="toggleAddPartner"
                                wire:loading.attr="disabled"
                                wire:target="toggleAddPartner"
                                class="cursor-pointer w-full flex items-center justify-center gap-2 py-3
                                       bg-white dark:bg-slate-900 border-2 border-dashed
                                       border-emerald-300 dark:border-emerald-800 rounded-2xl
                                       text-sm font-bold text-emerald-700 dark:text-emerald-400
                                       hover:bg-emerald-50 dark:hover:bg-emerald-900/20
                                       hover:border-emerald-500 dark:hover:border-emerald-600
                                       transition-all disabled:opacity-50">
                                <flux:icon.plus variant="micro" />
                                Add Partner to Sequence
                            </button>
                        @else
                            {{-- Add partner form --}}
                            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-emerald-300 dark:border-emerald-700
                                        shadow-md p-5 space-y-5 animate-in fade-in slide-in-from-bottom-2 duration-200">

                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="font-black text-slate-900 dark:text-white text-sm">Add Partner to Sequence</h3>
                                        @php $seqMins = $worksheet->sequenceMinutesRemaining(); @endphp
                                        <p class="text-[10px] text-amber-600 dark:text-amber-400 mt-0.5">
                                            Sequence window closes in {{ intdiv($seqMins, 60) }}h {{ $seqMins % 60 }}m
                                        </p>
                                    </div>
                                    <button type="button" wire:click="toggleAddPartner"
                                        class="cursor-pointer p-2 rounded-xl text-slate-400 hover:bg-slate-100
                                               dark:hover:bg-slate-700 hover:text-slate-700 dark:hover:text-white transition-colors">
                                        <flux:icon.x-mark variant="micro" />
                                    </button>
                                </div>

                                {{-- Partner search --}}
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="relative md:col-span-1">
                                        <flux:input wire:model.live="p_name" label="Partner Name / Search"
                                            placeholder="Type to search users…" />
                                        @if ($p_name && !$p_id && $available_partners->isNotEmpty())
                                            <div class="absolute z-30 w-full bg-white dark:bg-slate-800 border
                                                        border-slate-200 dark:border-slate-600 shadow-2xl rounded-xl
                                                        mt-1 overflow-hidden">
                                                @foreach ($available_partners as $u)
                                                    <button type="button"
                                                        wire:click="selectPartner({{ $u->id }}, '{{ addslashes($u->contact_person) }}', '{{ $u->contact_phone }}', '{{ $u->whatsapp }}')"
                                                        class="cursor-pointer w-full text-left p-3 hover:bg-emerald-50
                                                               dark:hover:bg-emerald-900/30 border-b last:border-0
                                                               border-slate-100 dark:border-slate-700 transition-colors">
                                                        <p class="text-sm font-bold text-slate-900 dark:text-white">
                                                            {{ $u->contact_person }}
                                                        </p>
                                                        <p class="text-[10px] text-slate-500 dark:text-slate-400">
                                                            {{ $u->email }}
                                                        </p>
                                                    </button>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    <flux:select wire:model="p_type" label="Partner Type">
                                        @foreach ($partnerTypes as $pt)
                                            <option value="{{ $pt->value }}">{{ $pt->label() }}</option>
                                        @endforeach
                                    </flux:select>

                                    <flux:input wire:model="p_contact" label="Contact Details" />
                                </div>

                                {{-- Planned action --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <flux:select wire:model.live="p_planned_action" label="Planned Action">
                                        <option value="">— Select action —</option>
                                        @foreach ($plannedActions as $pa)
                                            <option value="{{ $pa->value }}">{{ $pa->label() }}</option>
                                        @endforeach
                                    </flux:select>

                                    @if ($p_planned_action === 'custom')
                                        <flux:input wire:model="p_planned_custom"
                                            label="Describe Custom Action"
                                            placeholder="e.g. To present rate card…" />
                                    @endif
                                </div>

                                @error('p_name')    <p class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                @error('p_contact') <p class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror

                                {{-- Actions --}}
                                <div class="flex items-center gap-3 pt-1">
                                    <button type="button"
                                        wire:click="addPartner"
                                        wire:loading.attr="disabled"
                                        wire:target="addPartner"
                                        class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 bg-emerald-600
                                               hover:bg-emerald-700 dark:bg-emerald-500 dark:hover:bg-emerald-600
                                               text-white text-sm font-bold rounded-xl transition-colors
                                               shadow-lg shadow-emerald-100 dark:shadow-none
                                               disabled:opacity-60 disabled:cursor-not-allowed">
                                        <span wire:loading.remove wire:target="addPartner">
                                            <flux:icon.plus variant="micro" />
                                        </span>
                                        <span wire:loading wire:target="addPartner">
                                            <flux:icon.arrow-path variant="micro" class="animate-spin" />
                                        </span>
                                        <span wire:loading.remove wire:target="addPartner">Add to Sequence</span>
                                        <span wire:loading wire:target="addPartner">Adding…</span>
                                    </button>

                                    <button type="button" wire:click="toggleAddPartner"
                                        class="cursor-pointer px-4 py-2 text-sm font-bold text-slate-600
                                               dark:text-slate-300 hover:text-slate-900 dark:hover:text-white
                                               rounded-xl border border-slate-200 dark:border-slate-600
                                               hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                @endcan
            @elseif (!$worksheet->is_completed && $worksheet->sequenceLocked())
                <div class="mt-4 p-4 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700
                            rounded-2xl flex items-center gap-3 text-sm text-slate-500 dark:text-slate-400">
                    <flux:icon.lock-closed variant="micro" class="flex-shrink-0 text-slate-400" />
                    The 8-hour sequence window has closed. No new partners can be added to this worksheet.
                </div>
            @endif
        </div>

        {{-- ════════════════════════════════════════════════════════
             SESSION SUMMARY
        ════════════════════════════════════════════════════════ --}}
        @if ($completedCount > 0)
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-700
                        shadow-sm p-6 md:p-8">
                <h3 class="text-lg font-black text-slate-900 dark:text-white mb-5 tracking-tight">
                    Session Summary
                </h3>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 text-center">
                        <p class="text-3xl font-black text-slate-900 dark:text-white">{{ $progress }}%</p>
                        <p class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-1">Completion</p>
                    </div>
                    <div class="p-5 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-100 dark:border-emerald-800 text-center">
                        <p class="text-3xl font-black text-emerald-700 dark:text-emerald-400">{{ $completedCount }}</p>
                        <p class="text-[10px] font-black text-emerald-600 dark:text-emerald-500 uppercase tracking-widest mt-1">Done</p>
                    </div>
                    <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 text-center">
                        <p class="text-3xl font-black text-slate-500 dark:text-slate-400">{{ $totalCount - $completedCount }}</p>
                        <p class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-1">Remaining</p>
                    </div>
                    @php
                        $overdueCount = $worksheet->entries()
                            ->whereNotNull('reminder_at')->where('reminder_at', '<', now())->count();
                    @endphp
                    <div class="p-5 rounded-2xl text-center
                        {{ $overdueCount > 0
                            ? 'bg-red-50 dark:bg-red-900/30 border border-red-100 dark:border-red-800'
                            : 'bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700' }}">
                        <p class="text-3xl font-black {{ $overdueCount > 0 ? 'text-red-600 dark:text-red-400' : 'text-slate-400 dark:text-slate-500' }}">
                            {{ $overdueCount }}
                        </p>
                        <p class="text-[10px] font-black uppercase tracking-widest mt-1
                                  {{ $overdueCount > 0 ? 'text-red-500' : 'text-slate-400 dark:text-slate-500' }}">
                            Overdue
                        </p>
                    </div>
                </div>

                {{-- Upcoming reminders --}}
                @php
                    $reminders = $worksheet->entries()
                        ->whereNotNull('reminder_at')->where('reminder_at', '>', now())
                        ->orderBy('reminder_at')->limit(5)->get();
                @endphp
                @if ($reminders->isNotEmpty())
                    <div class="mt-6 pt-6 border-t border-slate-100 dark:border-slate-700">
                        <p class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-4">
                            Upcoming Follow-ups
                        </p>
                        <div class="space-y-3">
                            @foreach ($reminders as $r)
                                <div class="flex items-center justify-between p-3
                                            bg-orange-50 dark:bg-orange-900/20
                                            border border-orange-100 dark:border-orange-800 rounded-xl">
                                    <div class="flex items-center gap-3">
                                        <flux:icon.clock variant="micro" class="text-orange-400 flex-shrink-0" />
                                        <div>
                                            <p class="text-sm font-bold text-slate-800 dark:text-white">{{ $r->partner_name }}</p>
                                            <p class="text-[10px] text-slate-500 dark:text-slate-400">
                                                {{ Str::limit($r->way_forward, 60) }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right flex-shrink-0 ml-4">
                                        <p class="text-xs font-bold text-orange-700 dark:text-orange-400">
                                            {{ $r->reminder_at->format('d M Y, H:i') }}
                                        </p>
                                        <p class="text-[9px] text-orange-500 italic">{{ $r->reminder_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- ════════════════════════════════════════════════════════════
         SHARE MODAL
    ════════════════════════════════════════════════════════════ --}}
    <flux:modal name="share-modal" class="md:w-[500px]">
        <div class="space-y-5">
            <div>
                <flux:heading size="lg">Share Worksheet</flux:heading>
                <flux:subheading>
                    Grant or revoke colleague access to
                    <strong class="text-slate-900 dark:text-white">{{ $worksheet->name }}</strong>
                </flux:subheading>
            </div>

            <flux:input wire:model.live.debounce.300ms="staff_search"
                placeholder="Search staff by name or email…"
                icon="magnifying-glass" />

            <div class="max-h-72 overflow-y-auto space-y-2 pr-1">
                @forelse ($available_staff as $staff)
                    <div class="flex items-center justify-between p-3 rounded-xl border
                                border-slate-100 dark:border-slate-700
                                hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="h-9 w-9 rounded-full bg-emerald-100 dark:bg-emerald-900 flex items-center
                                        justify-center text-sm font-black text-emerald-700 dark:text-emerald-400">
                                {{ strtoupper(substr($staff->contact_person, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-black text-slate-900 dark:text-white leading-none">
                                    {{ $staff->contact_person }}
                                </p>
                                <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5 italic">
                                    {{ $staff->email }}
                                </p>
                                <span class="text-[9px] text-emerald-600 dark:text-emerald-400 font-black uppercase
                                             bg-emerald-50 dark:bg-emerald-900/40 px-1.5 py-0.5 rounded mt-1 inline-block">
                                    {{ $staff->roles->first()?->name ?? 'Staff' }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            {{-- Checkmark shows current state --}}
                            <span class="text-[9px] font-black uppercase
                                {{ in_array($staff->id, $selectedStaff) ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-300 dark:text-slate-600' }}">
                                {{ in_array($staff->id, $selectedStaff) ? 'Access' : '' }}
                            </span>
                            <button type="button"
                                wire:click="toggleShare({{ $staff->id }})"
                                wire:loading.attr="disabled"
                                wire:target="toggleShare({{ $staff->id }})"
                                class="cursor-pointer relative h-9 w-9 rounded-xl border transition-all
                                       disabled:opacity-50 disabled:cursor-not-allowed
                                    {{ in_array($staff->id, $selectedStaff)
                                        ? 'bg-emerald-600 border-emerald-700 text-white hover:bg-red-600 hover:border-red-700'
                                        : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-600 text-slate-400 hover:border-emerald-500 hover:text-emerald-600' }}">
                                <span wire:loading.remove wire:target="toggleShare({{ $staff->id }})">
                                    @if (in_array($staff->id, $selectedStaff))
                                        <flux:icon.check variant="micro" class="mx-auto" />
                                    @else
                                        <flux:icon.plus variant="micro" class="mx-auto" />
                                    @endif
                                </span>
                                <span wire:loading wire:target="toggleShare({{ $staff->id }})">
                                    <flux:icon.arrow-path variant="micro" class="mx-auto animate-spin" />
                                </span>
                            </button>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-400 dark:text-slate-500 text-center py-6 italic">
                        No staff found{{ $staff_search ? ' matching "' . $staff_search . '"' : '' }}.
                    </p>
                @endforelse
            </div>

            {{-- Currently shared summary --}}
            @if (!empty($selectedStaff))
                <div class="pt-3 border-t border-slate-100 dark:border-slate-700">
                    <p class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">
                        Currently Shared With ({{ count($selectedStaff) }})
                    </p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($available_staff->filter(fn($s) => in_array($s->id, $selectedStaff)) as $shared)
                            <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-emerald-50 dark:bg-emerald-900/40
                                         border border-emerald-200 dark:border-emerald-800 text-xs font-bold text-emerald-700 dark:text-emerald-400">
                                <div class="h-4 w-4 rounded-full bg-emerald-600 flex items-center justify-center text-[7px] text-white font-black">
                                    {{ strtoupper(substr($shared->contact_person, 0, 1)) }}
                                </div>
                                {{ $shared->contact_person }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="flex justify-end pt-2">
                <flux:modal.close>
                    <button type="button"
                        class="cursor-pointer px-5 py-2 text-sm font-bold text-slate-600 dark:text-slate-300
                               hover:text-slate-900 dark:hover:text-white rounded-xl border border-slate-200
                               dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                        Done
                    </button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
    
</div>