<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\WorksheetHeader;
use App\Models\Territory;
use App\Models\User;
use App\Enums\WorksheetType;

new class extends Component {
    use WithPagination;

    // ── Filter / tab state ───────────────────────────────────────
    public string $search          = '';
    public string $author_search   = '';
    public string $territory_id    = '';
    public string $status          = 'all';
    public string $worksheet_type  = 'all';   // all|scouting|daily|weekly|monthly
    public        $date_from       = null;
    public        $date_to         = null;
    public string $reminder_filter = 'all';
    public int    $perPage         = 10;
    public bool   $is_global       = false;

    // ── Share modal state ────────────────────────────────────────
    public ?WorksheetHeader $sharingWorksheet = null;
    public array            $selectedStaff    = [];
    public string           $staff_search     = '';

    protected $queryString = [
        'search'         => ['except' => ''],
        'status'         => ['except' => 'all'],
        'worksheet_type' => ['except' => 'all'],
        'perPage'        => ['except' => 10],
    ];

    // ── Sharing ──────────────────────────────────────────────────
    public function openShareModal(int $id): void
    {
        $this->sharingWorksheet = WorksheetHeader::with('sharedWith')->findOrFail($id);
        $this->selectedStaff    = $this->sharingWorksheet->sharedWith->pluck('id')->toArray();
        $this->modal('share-modal')->show();
    }

    public function toggleShare(int $userId): void
    {
        $targetUser = User::findOrFail($userId);

        if (in_array($userId, $this->selectedStaff)) {
            $this->sharingWorksheet->sharedWith()->detach($userId);
            $this->selectedStaff = array_values(array_diff($this->selectedStaff, [$userId]));
            $targetUser->notify(new \App\Notifications\WorksheetSharedNotification($this->sharingWorksheet, 'withdrawn'));
            $this->dispatch('notify', message: 'Access withdrawn.');
        } else {
            $this->sharingWorksheet->sharedWith()->attach($userId);
            $this->selectedStaff[] = $userId;
            $targetUser->notify(new \App\Notifications\WorksheetSharedNotification($this->sharingWorksheet, 'granted'));
            $this->dispatch('notify', message: 'Access granted.');
        }
    }

    // ── Pagination resets ────────────────────────────────────────
    public function updatingSearch():        void { $this->resetPage(); }
    public function updatingStatus():        void { $this->resetPage(); }
    public function updatingWorksheetType(): void { $this->resetPage(); }

    public function setType(string $type): void
    {
        $this->worksheet_type = $type;
        $this->resetPage();
    }

    // ── Data ─────────────────────────────────────────────────────
    public function with(): array
    {
        $user  = auth()->user();
        $query = WorksheetHeader::query()
            ->with(['user.territories', 'sharedWith'])
            ->withCount([
                'entries',
                'entries as completed_entries_count' => fn($q) => $q->whereNotNull('completed_at'),
            ]);

        if (!$this->is_global && !$user->hasAnyRole(['admin', 'superadmin'])) {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('sharedWith', fn($sq) => $sq->where('user_id', $user->id));
            });
        }

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        if ($this->author_search) {
            $query->whereHas('user', fn($q) =>
                $q->whereAny(['contact_person', 'email', 'organisation'], 'like', '%' . $this->author_search . '%')
            );
        }

        match ($this->status) {
            'completed' => $query->where('is_completed', true),
            'active'    => $query->where('is_completed', false),
            default     => null,
        };

        if ($this->worksheet_type !== 'all') {
            $query->where('worksheet_type', $this->worksheet_type);
        }

        if ($this->date_from) $query->whereDate('created_at', '>=', $this->date_from);
        if ($this->date_to)   $query->whereDate('created_at', '<=', $this->date_to);

        if ($this->territory_id) {
            $query->whereHas('user.territories', fn($q) => $q->where('territories.id', $this->territory_id));
        }

        match ($this->reminder_filter) {
            'overdue'     => $query->whereHas('entries', fn($q) =>
                                $q->whereNotNull('reminder_at')->where('reminder_at', '<', now())),
            'forthcoming' => $query->whereHas('entries', fn($q) =>
                                $q->whereNotNull('reminder_at')->where('reminder_at', '>', now())),
            default       => null,
        };

        return [
            'worksheets'      => $query->latest()->paginate($this->perPage),
            'all_territories' => Territory::orderBy('name')->get(),
            'worksheetTypes'  => WorksheetType::cases(),
            'available_staff' => User::where('id', '!=', auth()->id())
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

    public function mount(): void
    {
        if (!auth()->user()->can('viewAny', WorksheetHeader::class)) {
            abort(403);
        }
    }
}; ?>

<div class="min-h-screen bg-slate-50 dark:bg-slate-950">

    <x-worksheet._toast />

    {{-- ════════════════════════════════════════════════════════════
         STICKY TOP NAV
    ════════════════════════════════════════════════════════════ --}}
    <div class="sticky top-0 z-20 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800">
        <div class="max-w-7xl mx-auto px-4 md:px-8 h-14 flex items-center justify-between gap-4">
            <div>
                <h1 class="text-base font-black text-slate-900 dark:text-white tracking-tight leading-none">
                    Worksheets
                </h1>
                <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">Partner interactions & logistics leads</p>
            </div>
            <a href="{{ route('worksheets.create') }}" wire:navigate
               class="cursor-pointer inline-flex items-center gap-1.5 px-4 py-2 text-xs font-black uppercase
                      tracking-widest rounded-xl bg-lime-400 hover:bg-lime-500 dark:bg-lime-400
                      dark:hover:bg-lime-500 text-slate-900 transition-colors shadow-sm shadow-lime-200
                      dark:shadow-none">
                <flux:icon.plus variant="micro" />
                New Worksheet
            </a>
        </div>

        {{-- Type tab bar --}}
        <div class="max-w-7xl mx-auto px-4 md:px-8 flex items-center gap-0 overflow-x-auto">
            @php
                $tabs = [
                    ['value' => 'all',      'label' => 'All'],
                    ['value' => 'scouting', 'label' => 'Scouting'],
                    ['value' => 'daily',    'label' => 'Daily'],
                    ['value' => 'weekly',   'label' => 'Weekly'],
                    ['value' => 'monthly',  'label' => 'Monthly'],
                ];
            @endphp
            @foreach ($tabs as $tab)
                <button type="button"
                    wire:click="setType('{{ $tab['value'] }}')"
                    wire:loading.attr="disabled"
                    wire:target="setType"
                    class="cursor-pointer relative px-4 py-3 text-xs font-bold whitespace-nowrap border-b-2
                           transition-colors focus:outline-none
                           {{ $worksheet_type === $tab['value']
                               ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400 dark:border-emerald-400'
                               : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700
                                  dark:hover:text-slate-300 hover:border-slate-300 dark:hover:border-slate-600' }}">
                    {{ $tab['label'] }}
                    {{-- Loading spinner --}}
                    <span wire:loading wire:target="setType('{{ $tab['value'] }}')"
                        class="absolute right-1 top-1/2 -translate-y-1/2">
                        <flux:icon.arrow-path variant="micro" class="animate-spin text-emerald-500" />
                    </span>
                </button>
            @endforeach
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 md:px-8 py-6 space-y-5">

        {{-- ════════════════════════════════════════════════════════
             FILTER PANEL
        ════════════════════════════════════════════════════════ --}}
        <details class="group bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
            <summary class="cursor-pointer flex items-center justify-between px-5 py-4 select-none
                            text-sm font-bold text-slate-700 dark:text-slate-200 list-none">
                <div class="flex items-center gap-2">
                    <flux:icon.funnel variant="micro" class="text-slate-400" />
                    Filters & Search
                    @if ($search || $author_search || $status !== 'all' || $date_from || $date_to || $territory_id || $reminder_filter !== 'all')
                        <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    @endif
                </div>
                <flux:icon.chevron-down variant="micro"
                    class="text-slate-400 transition-transform group-open:rotate-180" />
            </summary>

            <div class="px-5 pb-5 space-y-4 border-t border-slate-100 dark:border-slate-800 pt-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 items-end">
                    <flux:input label="Worksheet Name" wire:model.live.debounce.300ms="search"
                        icon="magnifying-glass" placeholder="Search…" />
                    <flux:input label="Author" wire:model.live.debounce.300ms="author_search"
                        icon="user" placeholder="Team member…" />
                    <flux:select wire:model.live="status" label="Status">
                        <option value="all">All Statuses</option>
                        <option value="completed">Completed</option>
                        <option value="active">Active</option>
                    </flux:select>
                    <flux:input type="date" wire:model.live="date_from" label="From" />
                    <flux:input type="date" wire:model.live="date_to" label="To" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 items-end">
                    <flux:select label="Territory" wire:model.live="territory_id">
                        <x-slot name="icon"><flux:icon.map-pin variant="micro" /></x-slot>
                        <option value="">All Regions</option>
                        @foreach ($all_territories as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </flux:select>

                    <flux:select label="Reminders" wire:model.live="reminder_filter">
                        <x-slot name="icon"><flux:icon.bell variant="micro" /></x-slot>
                        <option value="all">All Reminders</option>
                        <option value="overdue">Overdue</option>
                        <option value="forthcoming">Forthcoming</option>
                    </flux:select>

                    <div class="flex flex-col gap-1.5">
                        @can('viewGlobal', App\Models\WorksheetHeader::class)
                            <flux:checkbox wire:model.live="is_global" label="Global View (all staff)" />
                        @endcan
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Show</span>
                            <select wire:model.live="perPage"
                                class="cursor-pointer text-xs font-bold border border-slate-200 dark:border-slate-600
                                       rounded-lg bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 py-1 px-2">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="button"
                        wire:click="$set('search',''); $set('author_search',''); $set('status','all');
                                    $set('date_from',null); $set('date_to',null); $set('is_global',false);
                                    $set('reminder_filter','all'); $set('territory_id','')"
                        class="cursor-pointer text-xs font-bold text-slate-500 dark:text-slate-400
                               hover:text-red-600 dark:hover:text-red-400 transition-colors">
                        Clear all filters
                    </button>
                </div>
            </div>
        </details>

        {{-- ════════════════════════════════════════════════════════
             RESULTS COUNT
        ════════════════════════════════════════════════════════ --}}
        <div class="flex items-center justify-between text-[10px] font-bold text-slate-400 dark:text-slate-500
                    uppercase tracking-widest">
            <span>
                {{ $worksheets->total() }} worksheet{{ $worksheets->total() !== 1 ? 's' : '' }}
                @if ($worksheet_type !== 'all')
                    · {{ \App\Enums\WorksheetType::from($worksheet_type)->label() }}
                @endif
            </span>
            <span wire:loading wire:target="setType,updatingSearch,updatingStatus"
                class="flex items-center gap-1 text-emerald-600 dark:text-emerald-400">
                <flux:icon.arrow-path variant="micro" class="animate-spin" />
                Loading…
            </span>
        </div>

        {{-- ════════════════════════════════════════════════════════
             WORKSHEET CARDS
        ════════════════════════════════════════════════════════ --}}
        <div class="space-y-3" wire:loading.class="opacity-60">
            @forelse ($worksheets as $ws)
                @php
                    $isOwner        = $ws->user_id === auth()->id();
                    $isCollaborator = !$isOwner && $ws->sharedWith->contains(auth()->id());
                    $hasOverdue     = $ws->entries()
                                        ->whereNotNull('reminder_at')
                                        ->where('reminder_at', '<', now())
                                        ->exists();
                @endphp

                <div class="relative bg-white dark:bg-slate-900 rounded-2xl border shadow-sm
                            transition-all hover:shadow-md
                            {{ $isCollaborator
                                ? 'border-l-4 border-l-emerald-500 border-y border-r border-slate-200 dark:border-slate-700'
                                : 'border-slate-200 dark:border-slate-700' }}">

                    {{-- Collaborator pill --}}
                    @if ($isCollaborator)
                        <div class="absolute -top-3 left-5 flex items-center gap-1.5 px-3 py-1
                                    bg-emerald-600 text-white text-[9px] font-black uppercase
                                    tracking-widest rounded-full shadow border border-emerald-400">
                            <flux:icon.users variant="micro" />
                            Collaborator
                        </div>
                    @endif

                    <div class="p-5 md:p-6">
                        <div class="flex flex-col md:flex-row justify-between items-start gap-4">

                            {{-- Left: name + meta --}}
                            <div class="flex-grow min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <a href="{{ route('worksheets.show', $ws->id) }}" wire:navigate
                                       class="cursor-pointer text-base font-black text-slate-900 dark:text-white
                                              hover:text-emerald-700 dark:hover:text-emerald-400
                                              tracking-tight transition-colors">
                                        {{ $ws->name }}
                                    </a>
                                    <x-worksheet._type-badge :type="$ws->worksheet_type" />
                                    <span class="px-1.5 py-0.5 text-[9px] font-black uppercase rounded
                                        {{ $ws->is_completed
                                            ? 'bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400'
                                            : 'bg-emerald-50 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400' }}">
                                        {{ $ws->is_completed ? 'Completed' : 'Active' }}
                                    </span>
                                    @if ($hasOverdue && !$ws->is_completed)
                                        <span class="flex items-center gap-1 px-1.5 py-0.5 rounded
                                                     bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400
                                                     text-[9px] font-black uppercase animate-pulse">
                                            <flux:icon.exclamation-triangle variant="micro" />
                                            Overdue
                                        </span>
                                    @endif
                                </div>

                                {{-- Authored by --}}
                                <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1.5 flex items-center gap-1">
                                    <flux:icon.user variant="micro" class="text-slate-300 dark:text-slate-600" />
                                    <span class="{{ $isOwner ? 'text-emerald-600 dark:text-emerald-400 font-bold' : '' }}">
                                        {{ $isOwner ? 'You' : $ws->user->contact_person }}
                                    </span>
                                    <span class="text-slate-300 dark:text-slate-600">·</span>
                                    <flux:icon.calendar variant="micro" class="text-slate-300 dark:text-slate-600" />
                                    {{ $ws->created_at->format('d M Y') }}
                                    <span class="text-slate-300 dark:text-slate-600">·</span>
                                    {{ $ws->created_at->diffForHumans() }}
                                </p>

                                {{-- Sequence lock --}}
                                @if (!$ws->is_completed)
                                    @php $seqMins = $ws->sequenceMinutesRemaining(); @endphp
                                    <p class="text-[9px] font-black uppercase tracking-widest mt-1 flex items-center gap-1
                                        {{ $seqMins === 0
                                            ? 'text-red-400 dark:text-red-500'
                                            : 'text-amber-500 dark:text-amber-400' }}">
                                        <flux:icon.clock variant="micro" />
                                        {{ $seqMins === 0
                                            ? 'Sequence locked — no new partners'
                                            : intdiv($seqMins, 60) . 'h ' . ($seqMins % 60) . 'm to add partners' }}
                                    </p>
                                @endif

                                {{-- Collaborator avatars --}}
                                @php $otherCollabs = $ws->sharedWith->filter(fn($u) => $u->id !== auth()->id()); @endphp
                                @if ($otherCollabs->isNotEmpty())
                                    <div class="mt-3 flex flex-wrap items-center gap-1.5">
                                        <span class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase">Team:</span>
                                        @foreach ($otherCollabs as $collab)
                                            <a href="{{ route('users.show', $collab->slug) }}" wire:navigate
                                               class="cursor-pointer flex items-center gap-1.5 px-2 py-1 rounded-lg
                                                      bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700
                                                      hover:border-emerald-400 dark:hover:border-emerald-600 transition-all group">
                                                <div class="h-4 w-4 rounded-full bg-emerald-100 dark:bg-emerald-900 flex items-center
                                                            justify-center text-[7px] font-black text-emerald-700 dark:text-emerald-400
                                                            group-hover:bg-emerald-600 dark:group-hover:bg-emerald-500 group-hover:text-white">
                                                    {{ strtoupper(substr($collab->contact_person, 0, 1)) }}
                                                </div>
                                                <span class="text-[9px] font-bold text-slate-600 dark:text-slate-400
                                                             group-hover:text-emerald-700 dark:group-hover:text-emerald-400">
                                                    {{ $collab->contact_person }}
                                                </span>
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            {{-- Right: action buttons --}}
                            <div class="flex items-center gap-2 flex-shrink-0 w-full md:w-auto justify-end">
                                @if ($isOwner)
                                    <button type="button"
                                        wire:click="openShareModal({{ $ws->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="openShareModal({{ $ws->id }})"
                                        class="cursor-pointer p-2 rounded-xl border border-slate-200 dark:border-slate-700
                                               text-slate-500 dark:text-slate-400 hover:border-emerald-400
                                               hover:text-emerald-700 dark:hover:text-emerald-400 transition-all">
                                        <flux:icon.share variant="micro" />
                                    </button>
                                @endif

                                @if (!$ws->is_completed)
                                    @can('update', $ws)
                                        <a href="{{ route('worksheets.create') }}" wire:navigate
                                           class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 text-[10px]
                                                  font-black uppercase tracking-widest rounded-xl bg-emerald-600
                                                  hover:bg-emerald-700 dark:bg-emerald-500 dark:hover:bg-emerald-600
                                                  text-white transition-colors shadow-sm">
                                            <flux:icon.pencil-square variant="micro" />
                                            {{ $isOwner ? 'Manage' : 'Contribute' }}
                                        </a>
                                    @endcan
                                @endif

                                <a href="{{ route('worksheets.show', $ws->id) }}" wire:navigate
                                   class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 text-[10px]
                                          font-black uppercase tracking-widest rounded-xl border
                                          border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300
                                          hover:border-emerald-500 hover:text-emerald-700 dark:hover:border-emerald-500
                                          dark:hover:text-emerald-400 bg-white dark:bg-slate-800 transition-all">
                                    <flux:icon.eye variant="micro" />
                                    {{ $ws->is_completed ? 'Archive' : 'View' }}
                                </a>
                            </div>
                        </div>

                        {{-- Progress bar --}}
                        <x-worksheet._progress-bar
                            :completed="$ws->completed_entries_count"
                            :total="$ws->entries_count"
                            :muted="$ws->is_completed"
                            class="mt-4" />
                    </div>
                </div>

            @empty
                <div class="flex flex-col items-center justify-center py-20 bg-white dark:bg-slate-900
                            rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700">
                    <flux:icon.document-magnifying-glass class="h-12 w-12 text-slate-300 dark:text-slate-600 mb-4" />
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">No Worksheets Found</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Adjust your filters or start a new worksheet.</p>
                    <a href="{{ route('worksheets.create') }}" wire:navigate
                       class="cursor-pointer mt-5 inline-flex items-center gap-1.5 px-4 py-2 text-xs font-black uppercase
                              tracking-widest rounded-xl bg-lime-400 hover:bg-lime-500 text-slate-900 transition-colors">
                        <flux:icon.plus variant="micro" /> New Worksheet
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="py-2">
            {{ $worksheets->links() }}
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         SHARE MODAL
    ════════════════════════════════════════════════════════════ --}}
    <flux:modal name="share-modal" class="md:w-[480px]">
        <div class="space-y-5">
            <div>
                <flux:heading size="lg">Manage Sharing</flux:heading>
                <flux:subheading>
                    Collaborators for <strong>{{ $sharingWorksheet?->name }}</strong>
                </flux:subheading>
            </div>

            <flux:input wire:model.live.debounce.300ms="staff_search"
                placeholder="Search staff by name…" icon="magnifying-glass" />

            <div class="max-h-72 overflow-y-auto space-y-2 pr-1">
                @foreach ($available_staff as $staff)
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
                        <flux:checkbox
                            wire:key="staff-{{ $staff->id }}-{{ count($selectedStaff) }}"
                            :checked="in_array($staff->id, $selectedStaff)"
                            wire:click="toggleShare({{ $staff->id }})"
                            class="cursor-pointer" />
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end">
                <flux:modal.close>
                    <flux:button variant="ghost" class="cursor-pointer">Done</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>