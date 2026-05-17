<?php

use App\Models\User;
use App\Models\WorksheetHeader;
use App\Models\WorksheetEntry;
use App\Enums\PartnerType;
use App\Enums\WorksheetType;
use App\Enums\PlannedAction;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;
use App\Traits\SearchesPartners;

new class extends Component {
    use SearchesPartners;

    // ── Active worksheet selector ─────────────────────────────────
    // null = show planning view; set to a header id to execute that worksheet
    public ?int    $active_id       = null;
    // Which tab is shown in the planning/switcher view
    public string  $view_tab        = 'new'; // new | scouting | daily | weekly | monthly

    protected $queryString = ['active_id'];

    // ── Planning form ─────────────────────────────────────────────
    public string  $worksheet_name = '';
    public string  $worksheet_type = '';
    public array   $temp_partners  = [];

    // Partner add fields
    public         $p_id                    = null;
    public string  $p_name                  = '';
    public string  $p_contact               = '';
    public string  $p_type                  = 'general';
    public string  $p_planned_action        = '';
    public string  $p_planned_action_custom = '';

    // ── Execution form ────────────────────────────────────────────
    public string  $activity      = '';
    public string  $feedback      = '';
    public string  $way_forward   = '';
    public string  $private_notes = '';
    public string  $reminder_at   = '';

    // ── Edit-in-place state ───────────────────────────────────────
    public ?int    $editing_entry_id        = null;
    public string  $edit_activity           = '';
    public string  $edit_feedback           = '';
    public string  $edit_way_forward        = '';
    public string  $edit_private_notes      = '';
    public string  $edit_reminder_at        = '';

    // ────────────────────────────────────────────────────────────
    // with()
    // ────────────────────────────────────────────────────────────
    public function with(): array
    {
        $userId = Auth::id();

        // Active worksheet for execution
        $activeHeader = null;
        if ($this->active_id) {
            $activeHeader = WorksheetHeader::where('id', $this->active_id)
                ->where('is_completed', false)
                ->where(function ($q) use ($userId) {
                    $q->where('user_id', $userId)
                      ->orWhereHas('sharedWith', fn($sq) => $sq->where('user_id', $userId));
                })
                ->first();
        }

        $currentEntry = null;
        $progress = $completedCount = $totalCount = 0;

        if ($activeHeader) {
            $allEntries     = $activeHeader->entries;
            $totalCount     = $allEntries->count();
            $completedCount = $allEntries->whereNotNull('completed_at')->count();
            $currentEntry   = $allEntries->whereNull('completed_at')->first();
            $progress       = $totalCount > 0 ? ($completedCount / $totalCount) * 100 : 0;
        }

        // Blocked types (one-active-per-type rule)
        $blockedTypes = [];
        foreach (WorksheetType::cases() as $wt) {
            if (! $wt->allowsMultipleActive()) {
                if (WorksheetHeader::where('user_id', $userId)
                    ->where('worksheet_type', $wt->value)
                    ->where('is_completed', false)
                    ->exists()) {
                    $blockedTypes[] = $wt->value;
                }
            }
        }

        // All active (incomplete) worksheets owned by or shared with user, grouped by type
        $activeByType = WorksheetHeader::where('is_completed', false)
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhereHas('sharedWith', fn($sq) => $sq->where('user_id', $userId));
            })
            ->withCount([
                'entries',
                'entries as completed_entries_count' => fn($q) => $q->whereNotNull('completed_at'),
            ])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(fn($h) => $h->worksheet_type->value);

        return [
            'activeHeader'       => $activeHeader,
            'currentEntry'       => $currentEntry,
            'progress'           => $progress,
            'completedCount'     => $completedCount,
            'totalCount'         => $totalCount,
            'partnerTypes'       => PartnerType::cases(),
            'plannedActions'     => PlannedAction::cases(),
            'worksheetTypes'     => WorksheetType::cases(),
            'blockedTypes'       => $blockedTypes,
            'activeByType'       => $activeByType,
            'available_partners' => $this->searchPartners($this->p_name),
            'history' => WorksheetHeader::withCount([
                    'entries',
                    'entries as completed_entries_count' => fn($q) => $q->whereNotNull('completed_at'),
                ])
                ->where(function ($q) use ($userId) {
                    $q->where('user_id', $userId)
                      ->orWhereHas('sharedWith', fn($sq) => $sq->where('user_id', $userId));
                })
                ->latest()
                ->limit(10)
                ->get(),
        ];
    }

    // ────────────────────────────────────────────────────────────
    // Navigation
    // ────────────────────────────────────────────────────────────
    public function switchToWorksheet(int $id): void
    {
        $this->active_id      = $id;
        $this->editing_entry_id = null;
        $this->reset(['activity', 'feedback', 'way_forward', 'private_notes', 'reminder_at']);
    }

    public function exitExecution(): void
    {
        $this->active_id = null;
        $this->view_tab  = 'new';
    }

    public function setTab(string $tab): void
    {
        $this->view_tab = $tab;
    }

    // ────────────────────────────────────────────────────────────
    // Partner helpers
    // ────────────────────────────────────────────────────────────
    public function selectPartner(int $id, string $name, ?string $phone, ?string $whatsapp): void
    {
        $this->p_id   = $id;
        $this->p_name = $name;

        $details = [];
        if ($phone)    $details[] = "Phone: {$phone}";
        if ($whatsapp) $details[] = "WA: {$whatsapp}";
        $this->p_contact = implode(' | ', $details);
    }

    public function addPartnerToDraft(): void
    {
        $rules = [
            'p_name'           => 'required',
            'p_contact'        => 'required',
            'p_type'           => 'required',
            'p_planned_action' => 'required',
        ];

        if ($this->p_planned_action === PlannedAction::Custom->value) {
            $rules['p_planned_action_custom'] = 'required|string|max:100';
        }

        $this->validate($rules);

        $this->temp_partners[] = [
            'name'                  => $this->p_name,
            'contact'               => $this->p_contact,
            'type'                  => $this->p_type,
            'planned_action'        => $this->p_planned_action,
            'planned_action_custom' => $this->p_planned_action_custom,
        ];

        $this->reset(['p_name', 'p_contact', 'p_id', 'p_type', 'p_planned_action', 'p_planned_action_custom']);
    }

    public function removePartner(int $index): void
    {
        unset($this->temp_partners[$index]);
        $this->temp_partners = array_values($this->temp_partners);
    }

    public function movePartner(int $index, string $direction): void
    {
        $newIndex = $direction === 'up' ? $index - 1 : $index + 1;

        if (isset($this->temp_partners[$newIndex])) {
            $out = array_splice($this->temp_partners, $index, 1);
            array_splice($this->temp_partners, $newIndex, 0, $out);
        }
    }

    // ────────────────────────────────────────────────────────────
    // Worksheet lifecycle
    // ────────────────────────────────────────────────────────────
    public function createWorksheet(): void
    {
        $this->authorize('create', WorksheetHeader::class);

        $this->validate([
            'worksheet_name' => 'required|string|max:255',
            'worksheet_type' => 'required|in:scouting,daily,weekly,monthly',
            'temp_partners'  => 'required|array|min:1',
        ]);

        $type   = WorksheetType::from($this->worksheet_type);
        $userId = Auth::id();

        if (! $type->allowsMultipleActive()) {
            $conflict = WorksheetHeader::where('user_id', $userId)
                ->where('worksheet_type', $this->worksheet_type)
                ->where('is_completed', false)
                ->exists();

            if ($conflict) {
                $this->addError(
                    'worksheet_type',
                    "You already have an active {$type->label()} worksheet. Complete it before starting a new one."
                );
                return;
            }
        }

        $header = WorksheetHeader::create([
            'user_id'        => $userId,
            'name'           => $this->worksheet_name,
            'worksheet_type' => $this->worksheet_type,
        ]);

        foreach ($this->temp_partners as $idx => $p) {
            WorksheetEntry::create([
                'header_id'             => $header->id,
                'partner_name'          => $p['name'],
                'contact_details'       => $p['contact'],
                'partner_type'          => $p['type'],
                'planned_action'        => $p['planned_action'] ?: null,
                'planned_action_custom' => $p['planned_action_custom'] ?: null,
                'sort_order'            => $idx,
            ]);
        }

        $this->reset(['worksheet_name', 'worksheet_type', 'temp_partners']);
        // Auto-enter execution view for the new worksheet
        $this->active_id = $header->id;
    }

    // ────────────────────────────────────────────────────────────
    // Entry execution
    // ────────────────────────────────────────────────────────────
    public function startEntry(int $id): void
    {
        WorksheetEntry::find($id)?->update(['started_at' => now()]);
    }

    public function completeEntry(int $id): void
    {
        $this->validate(
            [
                'activity'      => 'required',
                'feedback'      => 'required',
                'way_forward'   => 'required',
                'reminder_at'   => 'nullable|date|after:now',
                'private_notes' => 'nullable|string',
            ],
            ['reminder_at.after' => 'The follow-up reminder must be a future date and time.'],
        );

        $entry = WorksheetEntry::findOrFail($id);
        $this->authorize('update', $entry->header);

        $entry->update([
            'activity'          => $this->activity,
            'feedback'          => $this->feedback,
            'way_forward'       => $this->way_forward,
            'private_notes'     => $this->private_notes,
            'reminder_at'       => $this->reminder_at ?: null,
            'completed_at'      => now(),
            'last_edited_by_id' => Auth::id(),
        ]);

        $this->reset(['activity', 'feedback', 'way_forward', 'private_notes', 'reminder_at']);

        if (WorksheetEntry::where('header_id', $entry->header_id)->whereNull('completed_at')->count() === 0) {
            WorksheetHeader::find($entry->header_id)->update(['is_completed' => true]);
            $this->active_id = null;
        }
    }

    // ────────────────────────────────────────────────────────────
    // Edit-in-place (per-entry 8-hour window)
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
        $this->edit_reminder_at   = $entry->reminder_at
            ? $entry->reminder_at->format('Y-m-d\TH:i')
            : '';
    }

    public function cancelEdit(): void
    {
        $this->editing_entry_id = null;
        $this->reset(['edit_activity', 'edit_feedback', 'edit_way_forward', 'edit_private_notes', 'edit_reminder_at']);
    }

    public function saveEdit(int $id): void
    {
        $entry = WorksheetEntry::findOrFail($id);

        if (! auth()->user()->can('updateEntry', [$entry->header, $entry])) {
            $this->addError('edit_lock', $entry->editLockReason() ?? 'You do not have permission to edit this entry.');
            return;
        }

        $this->validate([
            'edit_activity'      => 'required',
            'edit_feedback'      => 'required',
            'edit_way_forward'   => 'required',
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

        $this->cancelEdit();
    }

    public function mount(): void
    {
        if (auth()->user()->cannot('create', WorksheetHeader::class)) {
            abort(403, 'You are not authorized to access worksheets.');
        }
    }
}; ?>

<div class="min-h-screen bg-lime-50/50 dark:bg-slate-900 dark:border-slate-800">

    {{-- ══════════════════════════════════════════════════════════════
         EXECUTION VIEW — a specific worksheet is being worked
    ══════════════════════════════════════════════════════════════ --}}
    @if ($activeHeader)
        <div class="max-w-7xl mx-auto px-4 md:px-6 py-6 space-y-6">

            {{-- Top bar --}}
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div class="flex items-center gap-3">
                    <button wire:click="exitExecution"
                        class="flex items-center gap-1.5 text-sm font-bold text-slate-500 hover:text-emerald-600 transition-colors">
                        <flux:icon.chevron-left variant="micro" />
                        All Worksheets
                    </button>
                    <span class="text-slate-200">/</span>
                    <span class="text-sm font-black text-slate-800 truncate max-w-xs">{{ $activeHeader->name }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 text-[9px] font-black uppercase rounded border {{ $activeHeader->worksheet_type->badgeClasses() }}">
                        {{ $activeHeader->worksheet_type->label() }}
                    </span>
                    <span class="px-2 py-0.5 text-[9px] font-black uppercase rounded bg-slate-100 text-slate-500 border border-slate-200">
                        {{ $activeHeader->user_id == auth()->id() ? 'Yours' : 'Collaborative' }}
                    </span>
                    <flux:button href="{{ route('worksheets.show', $activeHeader->id) }}" wire:navigate
                        variant="ghost" size="sm" icon="eye">
                        Full View
                    </flux:button>
                </div>
            </div>

            {{-- Collaborative banner --}}
            @if ($activeHeader->user_id != auth()->id())
                <div class="bg-emerald-600 text-white p-4 rounded-2xl flex items-center justify-between shadow-lg">
                    <div class="flex items-center gap-3">
                        <flux:icon.users />
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest opacity-80 leading-none">Collaborative Worksheet</p>
                            <p class="text-sm font-bold">Assisting {{ $activeHeader->user->contact_person ?? 'Owner' }}</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Worksheet header card --}}
            <div class="bg-slate-900 rounded-3xl p-6 md:p-8 text-white shadow-2xl">
                <div class="flex justify-between items-start gap-4 mb-5">
                    <div class="min-w-0">
                        <h1 class="text-2xl md:text-3xl font-black tracking-tight truncate">{{ $activeHeader->name }}</h1>
                        <p class="text-slate-400 text-sm mt-1">
                            Started {{ $activeHeader->created_at->format('d M Y, H:i') }}
                        </p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <div class="text-4xl font-black text-emerald-400 leading-none">{{ round($progress) }}%</div>
                        <p class="text-[10px] text-slate-400 uppercase font-black tracking-widest mt-0.5">
                            {{ $completedCount }} / {{ $totalCount }} partners
                        </p>
                    </div>
                </div>

                {{-- Progress bar --}}
                <div class="w-full bg-slate-800 h-2.5 rounded-full overflow-hidden mb-4">
                    <div class="bg-emerald-500 h-full rounded-full transition-all duration-700"
                        style="width: {{ $progress }}%"></div>
                </div>

                {{-- Sequence lock countdown --}}
                @php
                    $seqMins   = $activeHeader->sequenceMinutesRemaining();
                    $seqLocked = $activeHeader->sequenceLocked();
                    $seqPct    = $seqLocked ? 100 : (1 - $seqMins / (WorksheetHeader::SEQUENCE_LOCK_HOURS * 60)) * 100;
                @endphp
                <div class="p-3 rounded-2xl border flex items-center gap-3
                    {{ $seqLocked ? 'bg-red-900/30 border-red-700/40' : 'bg-slate-800 border-slate-700' }}">
                    <flux:icon.clock variant="micro" class="{{ $seqLocked ? 'text-red-400' : 'text-amber-400' }}" />
                    <div class="flex-grow">
                        @if ($seqLocked)
                            <p class="text-[10px] font-black text-red-300">
                                Sequence locked — no new partners can be added. Interactions continue.
                            </p>
                        @else
                            <p class="text-[10px] font-black text-amber-300">
                                Sequence open · {{ intdiv($seqMins, 60) }}h {{ $seqMins % 60 }}m left to add partners
                            </p>
                            <div class="mt-1.5 w-full bg-slate-700 h-1 rounded-full overflow-hidden">
                                <div class="bg-amber-400 h-full transition-all" style="width: {{ $seqPct }}%"></div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Main execution grid --}}
            @if ($currentEntry)
                <div class="grid grid-cols-1 xl:grid-cols-5 gap-6">

                    {{-- Current entry form — left 3 cols --}}
                    <div class="xl:col-span-3 bg-white dark:bg-slate-900 dark:border-slate-700 rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
                            <div class="h-8 w-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-black text-sm shadow-md shadow-emerald-200">
                                {{ $completedCount + 1 }}
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h2 class="font-black text-slate-900 text-lg leading-none">{{ $currentEntry->partner_name }}</h2>
                                    <span class="px-2 py-0.5 bg-slate-100 text-slate-500 text-[9px] font-black rounded uppercase">
                                        {{ $currentEntry->partner_type->label() }}
                                    </span>
                                    @if ($currentEntry->planned_action)
                                        <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 text-[9px] font-black rounded uppercase border border-emerald-200">
                                            {{ $currentEntry->plannedActionLabel() }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-slate-400 text-sm mt-1 truncate">{{ $currentEntry->contact_details }}</p>
                            </div>
                        </div>

                        <div class="p-6 ">
                            @if (!$currentEntry->started_at)
                                <div class="py-16 border-2 border-dashed border-slate-200 rounded-2xl text-center
                                    group hover:border-emerald-300 hover:bg-emerald-50/30 transition-all cursor-pointer"
                                    wire:click="startEntry({{ $currentEntry->id }})">
                                    <div class="h-12 w-12 bg-white rounded-full flex items-center justify-center mx-auto shadow-md mb-3 group-hover:scale-110 transition-transform border border-slate-200">
                                        <flux:icon.play variant="micro" class="text-emerald-600" />
                                    </div>
                                    <p class="font-bold text-slate-700">Begin Interaction</p>
                                    <p class="text-xs text-slate-400 mt-1">Click to start timer and unlock entry form</p>
                                </div>
                            @else
                                <form wire:submit.prevent="completeEntry({{ $currentEntry->id }})" class="space-y-5">
                                    @error('activity')
                                        <div class="p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-xl">{{ $message }}</div>
                                    @enderror

                                    <flux:textarea wire:model="activity" label="Action Taken" rows="auto"
                                        placeholder="Described service offering, discussed rate requirements…" />

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <flux:textarea wire:model="feedback" label="Partner Feedback" rows="auto" />
                                        <flux:textarea wire:model="way_forward" label="Way Forward" rows="auto" />
                                    </div>

                                    <div class="p-4 bg-amber-50/60 rounded-2xl border border-amber-100 space-y-3">
                                        <p class="flex items-center gap-1.5 text-[10px] font-black text-amber-800 uppercase tracking-widest">
                                            <flux:icon.lock-closed variant="micro" /> Confidential & Scheduling
                                        </p>
                                        <flux:textarea wire:model="private_notes" rows="auto"
                                            placeholder="Internal intelligence (desperation levels, back-haul needs…)" />
                                        <flux:input type="datetime-local" wire:model="reminder_at" label="Follow-up Reminder" />
                                    </div>

                                    <flux:button type="submit" variant="primary" class="w-full font-bold shadow-lg shadow-emerald-100" icon="check-circle">
                                        Finalise Partner {{ $completedCount + 1 }} of {{ $totalCount }}
                                    </flux:button>
                                </form>
                            @endif
                        </div>
                    </div>

                    {{-- Right panel — remaining queue + completed log —  2 cols --}}
                    <div class="xl:col-span-2 space-y-5 dark:bg-slate-900 dark:border-slate-700">

                        {{-- Switch worksheet panel --}}
                        <div class="bg-white dark:bg-slate-900 dark:border-slate-700 rounded-3xl border border-slate-200 shadow-sm p-5">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ">Switch Worksheet</p>
                            <div class="space-y-2 hover:grayscale-0">
                                @foreach ($activeByType as $typeVal => $headers)
                                    @foreach ($headers as $h)
                                        @php $isCurrent = $h->id === $activeHeader->id; @endphp
                                        <flux:button
                                            wire:click="switchToWorksheet({{ $h->id }})"
                                            variant="{{ $isCurrent ? 'filled' : 'ghost' }}"
                                            size="sm"
                                            class="w-full justify-start {{ $isCurrent ? 'cursor-default' : '' }}"
                                            :disabled="$isCurrent">
                                            <div class="flex-grow min-w-0 text-left">
                                                <p class="text-xs font-black truncate">{{ $h->name }}</p>
                                                <div class="flex items-center gap-2 mt-0.5">
                                                    <span class="text-[9px] font-black uppercase {{ WorksheetType::from($typeVal)->badgeClasses() }} px-1.5 py-0.5 rounded border">
                                                        {{ WorksheetType::from($typeVal)->label() }}
                                                    </span>
                                                    <span class="text-[9px] opacity-70">
                                                        {{ $h->completed_entries_count }}/{{ $h->entries_count }} done
                                                    </span>
                                                </div>
                                            </div>
                                            @php $hPct = $h->entries_count > 0 ? round(($h->completed_entries_count / $h->entries_count) * 100) : 0; @endphp
                                            <span class="flex-shrink-0 text-sm font-black {{ $isCurrent ? 'text-emerald-600' : 'opacity-60' }}">
                                                {{ $hPct }}%
                                            </span>
                                        </flux:button>
                                    @endforeach
                                @endforeach
                                <flux:button wire:click="exitExecution" variant="primary" size="sm" color="lime"
                                    icon="plus" class="w-full cursor-pointer my-4">
                                    Create New Worksheet
                                </flux:button>
                            </div>
                        </div>

                        {{-- Remaining queue --}}
                        <div class="bg-white dark:bg-slate-900 dark:border-slate-700 rounded-3xl border border-slate-200 shadow-sm p-5">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Remaining Queue</p>
                            <div class="space-y-2">
                                @php
                                    $upcoming = \App\Models\WorksheetEntry::where('header_id', $activeHeader->id)
                                        ->whereNull('completed_at')
                                        ->where('id', '!=', $currentEntry->id)
                                        ->orderBy('sort_order')
                                        ->get();
                                @endphp
                                @forelse($upcoming as $u)
                                    <div class="flex items-center gap-3 opacity-50 grayscale hover:grayscale-0 hover:opacity-100 transition-all">
                                        <div class="h-7 w-7 rounded-full border border-slate-200 flex items-center justify-center text-[9px] font-bold text-slate-400 flex-shrink-0">
                                            {{ $u->sort_order + 1 }}
                                        </div>
                                        <div class="flex-grow min-w-0">
                                            <p class="text-xs font-bold text-slate-700 truncate">{{ $u->partner_name }}</p>
                                            @if ($u->planned_action)
                                                <p class="text-[9px] text-slate-400">{{ $u->plannedActionLabel() }}</p>
                                            @endif
                                        </div>
                                        <flux:icon.lock-closed variant="micro" class="text-slate-200 flex-shrink-0" />
                                    </div>
                                @empty
                                    <div class="p-4 bg-emerald-50 border border-emerald-100 rounded-2xl flex items-center gap-3">
                                        <flux:icon.check-circle variant="micro" class="text-emerald-600" />
                                        <p class="text-xs font-bold text-emerald-800">This is the final entry!</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        {{-- Completed log --}}
                        <div class="bg-white dark:bg-slate-900 dark:border-slate-700 rounded-3xl border border-slate-200 shadow-sm p-5">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Completed Log</p>

                            @error('edit_lock')
                                <div class="mb-3 p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-xl">{{ $message }}</div>
                            @enderror

                            <div class="space-y-2 dark:bg-slate-900 dark:border-slate-700">
                                @php
                                    $done = \App\Models\WorksheetEntry::where('header_id', $activeHeader->id)
                                        ->whereNotNull('completed_at')
                                        ->orderBy('completed_at', 'desc')
                                        ->get();
                                @endphp
                                @foreach ($done as $d)
                                    @php
                                        $canEdit   = auth()->user()->can('updateEntry', [$d->header, $d]);
                                        $isEditing = $editing_entry_id === $d->id;
                                        $minsLeft  = $d->entryEditWindowMinutesRemaining();
                                    @endphp
                                    <div class="rounded-2xl border overflow-hidden transition-all
                                        {{ $isEditing ? 'border-emerald-300 ring-1 ring-emerald-300 bg-white' : 'border-slate-100 bg-slate-50 hover:bg-white' }}">

                                        <div class="flex justify-between items-center px-3 py-2.5">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <flux:icon.check-circle variant="micro" class="text-emerald-500 flex-shrink-0" />
                                                <div class="min-w-0">
                                                    <span class="text-xs font-bold text-slate-800 truncate block">{{ $d->partner_name }}</span>
                                                    <p class="text-[9px] text-slate-400">
                                                        {{ $d->completed_at->format('H:i') }}
                                                        @if ($canEdit && $minsLeft > 0)
                                                            · <span class="text-amber-500 font-bold">{{ intdiv($minsLeft, 60) }}h {{ $minsLeft % 60 }}m to edit</span>
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                            @if ($canEdit && !$isEditing)
                                                <flux:button wire:click="openEdit({{ $d->id }})"
                                                    variant="ghost" size="xs" icon="pencil-square" />
                                            @elseif (!$canEdit)
                                                <span title="{{ $d->editLockReason() }}" class="p-1.5 text-slate-200 cursor-not-allowed flex-shrink-0">
                                                    <flux:icon.lock-closed variant="micro" />
                                                </span>
                                            @endif
                                        </div>

                                        @if ($isEditing)
                                            <div class="px-3 pb-4 pt-1 border-t border-emerald-100 space-y-3">
                                                <flux:textarea wire:model="edit_activity" label="Action Taken" rows="auto" />
                                                <div class="grid grid-cols-2 gap-3">
                                                    <flux:textarea wire:model="edit_feedback" label="Feedback" rows="auto" />
                                                    <flux:textarea wire:model="edit_way_forward" label="Way Forward" rows="auto" />
                                                </div>
                                                <flux:textarea wire:model="edit_private_notes" label="Private Notes" rows="auto" />
                                                <flux:input type="datetime-local" wire:model="edit_reminder_at" label="Follow-up" />
                                                <div class="flex gap-2">
                                                    <flux:button wire:click="saveEdit({{ $d->id }})" variant="primary" size="sm" icon="check">Save</flux:button>
                                                    <flux:button wire:click="cancelEdit" variant="ghost" size="sm">Cancel</flux:button>
                                                </div>
                                            </div>
                                        @else
                                            <div class="px-3 pb-3 grid grid-cols-3 gap-2 text-[9px]">
                                                <div>
                                                    <p class="font-black text-slate-300 uppercase mb-0.5">Activity</p>
                                                    <p class="text-slate-600 leading-snug">{{ Str::limit($d->activity, 50) }}</p>
                                                </div>
                                                <div>
                                                    <p class="font-black text-slate-300 uppercase mb-0.5">Feedback</p>
                                                    <p class="text-slate-600 leading-snug">{{ Str::limit($d->feedback, 50) }}</p>
                                                </div>
                                                <div>
                                                    <p class="font-black text-slate-300 uppercase mb-0.5">Way Forward</p>
                                                    <p class="text-emerald-700 font-bold leading-snug">{{ Str::limit($d->way_forward, 50) }}</p>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @else
                {{-- All entries completed --}}
                <div class="bg-white rounded-3xl border border-emerald-200 p-12 text-center shadow-sm">
                    <div class="h-16 w-16 bg-emerald-500 text-white rounded-full flex items-center justify-center mx-auto shadow-xl shadow-emerald-200 mb-4">
                        <flux:icon.check variant="mini" />
                    </div>
                    <h2 class="text-2xl font-black text-slate-900 mb-2">Worksheet Complete!</h2>
                    <p class="text-slate-500 mb-6">All {{ $totalCount }} partners have been attended to.</p>
                    <div class="flex gap-3 justify-center">
                        <flux:button href="{{ route('worksheets.show', $activeHeader->id) }}" wire:navigate
                            variant="primary" icon="eye">
                            View Full Report
                        </flux:button>
                        <flux:button wire:click="exitExecution" variant="ghost">
                            Back to Overview
                        </flux:button>
                    </div>
                </div>
            @endif
        </div>

    {{-- ══════════════════════════════════════════════════════════════
         PLANNING VIEW — no active worksheet selected
    ══════════════════════════════════════════════════════════════ --}}
    @else
        <div class="max-w-7xl mx-auto px-4 md:px-6 py-6 space-y-6">

            {{-- Page header --}}
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <flux:button href="{{ route('worksheets.index') }}" variant="ghost" size="sm" icon="chevron-left"
                            wire:navigate class="text-emerald-600 hover:text-emerald-700 -ml-2">
                            All Worksheets
                        </flux:button>
                    </div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Worksheet Hub</h1>
                    <p class="text-slate-500 text-sm">Manage all your active worksheets or create a new one.</p>
                </div>
            </div>

            {{-- Tab bar --}}
            <div class="flex items-center gap-1 p-1 bg-white dark:bg-slate-900 dark:border-slate-700 border border-slate-200 rounded-2xl shadow-sm w-fit flex-wrap dark:bg-slate-900 dark:border-slate-700">
                @foreach ([
                    ['tab' => 'new',      'label' => 'New Worksheet', 'icon' => 'plus'],
                    ['tab' => 'scouting', 'label' => 'Scouting',      'icon' => 'map'],
                    ['tab' => 'daily',    'label' => 'Daily',         'icon' => 'sun'],
                    ['tab' => 'weekly',   'label' => 'Weekly',        'icon' => 'calendar-days'],
                    ['tab' => 'monthly',  'label' => 'Monthly',       'icon' => 'calendar'],
                ] as $t)
                    @php
                        $hasActive = isset($activeByType[$t['tab']]) && $activeByType[$t['tab']]->isNotEmpty();
                        $isBlocked = in_array($t['tab'], $blockedTypes ?? []);
                    @endphp
                    <flux:button type="button" wire:click="setTab('{{ $t['tab'] }}')"
                        variant="{{ $view_tab === $t['tab'] ? 'primary' : 'ghost' }}"
                        size="sm"
                        icon="{{ $t['icon'] }}"
                        class="relative cursor-pointer">
                        {{ $t['label'] }}
                        @if ($t['tab'] !== 'new' && $hasActive)
                            <span class="ml-1 h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                        @endif
                    </flux:button>
                @endforeach
            </div>

            {{-- Tab content --}}
            @if ($view_tab === 'new')
                {{-- ── New worksheet creation form ── --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-3xl dark:bg-slate-900 dark:border-slate-700  border border-slate-200 shadow-sm p-8 space-y-7">
                            <h2 class="text-xl font-black text-slate-800 tracking-tight">Plan New Worksheet</h2>

                            {{-- Step 1: Type --}}
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">
                                    Step 1 — Choose Type
                                </p>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 dark:bg-slate-900 dark:border-slate-700">
                                    @foreach ($worksheetTypes as $wt)
                                        @php $blocked = in_array($wt->value, $blockedTypes); @endphp
                                        <button type="button"
                                            @if(!$blocked) wire:click="$set('worksheet_type', '{{ $wt->value }}')" @endif
                                            class="relative p-4 rounded-2xl border-2 text-left cursor-pointer transition-all
                                                {{ $worksheet_type === $wt->value
                                                    ? 'border-slate-900 bg-lime-900 text-white shadow-lg'
                                                    : ($blocked
                                                        ? 'border-slate-100 bg-slate-50 text-slate-300 cursor-not-allowed'
                                                        : 'border-slate-200 bg-white text-slate-700 hover:border-slate-400 hover:shadow') }}">
                                            @if ($worksheet_type === $wt->value)
                                                <span class="absolute top-2.5 right-2.5 h-2 w-2 rounded-full bg-emerald-400"></span>
                                            @endif
                                            <span class="block text-sm font-black mb-1">{{ $wt->label() }}</span>
                                            <span class="block text-[10px] leading-snug {{ $worksheet_type === $wt->value ? 'text-slate-300' : 'text-slate-400' }}">
                                                {{ $wt->description() }}
                                            </span>
                                            @if ($blocked)
                                                <span class="absolute bottom-2 right-2 px-1.5 py-0.5 text-[8px] font-black uppercase rounded bg-orange-100 text-orange-600">Active</span>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                                @error('worksheet_type')
                                    <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Step 2: Name --}}
                            @if ($worksheet_type)
                                <div class="animate-in fade-in slide-in-from-bottom-2 duration-300">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">
                                        Step 2 — Name Your Worksheet
                                    </p>
                                    <flux:input wire:model="worksheet_name" label="Worksheet Name"
                                        placeholder="{{ match($worksheet_type) {
                                            'daily'   => 'Daily Partner Calls — ' . now()->format('d M'),
                                            'weekly'  => 'Weekly Lane Review — Week ' . now()->weekOfYear,
                                            'monthly' => 'Monthly Partner Review — ' . now()->format('M Y'),
                                            default   => 'Harare-Beira Lane Scouting',
                                        } }}" />
                                </div>

                                {{-- Step 3: Partners --}}
                                <div class="animate-in fade-in slide-in-from-bottom-2 duration-300">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">
                                        Step 3 — Build Partner Sequence
                                    </p>

                                    <div class="p-5 bg-slate-50 rounded-2xl dark:bg-slate-900 dark:border-slate-700 border border-dashed border-slate-200 space-y-4"
                                        wire:key="partner-form-{{ count($temp_partners) }}">

                                        {{-- Row 1: Search + Type + Planned Action --}}
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
                                            {{-- Partner search --}}
                                            <div class="relative">
                                                <flux:input wire:model.live="p_name" label="Partner" placeholder="Search contact person…" />
                                                @if ($p_name && !$p_id && count($available_partners) > 0)
                                                    <div class="absolute z-30 w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 shadow-2xl rounded-xl mt-1 overflow-hidden">
                                                        @foreach ($available_partners as $u)
                                                            <flux:button type="button"
                                                                wire:click="selectPartner({{ $u->id }}, '{{ addslashes($u->contact_person) }}', '{{ addslashes($u->contact_phone ?? '') }}', '{{ addslashes($u->whatsapp ?? '') }}')"
                                                                variant="ghost"
                                                                class="w-full justify-start rounded-none border-b last:border-0 border-slate-100 dark:border-slate-700">
                                                                <div class="text-left">
                                                                    <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $u->contact_person }}</p>
                                                                    <p class="text-[10px] text-slate-400 dark:text-slate-500">{{ $u->email }}</p>
                                                                </div>
                                                            </flux:button>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>

                                            {{-- Partner type --}}
                                            <flux:select wire:model="p_type" label="Partner Type">
                                                @foreach ($partnerTypes as $pt)
                                                    <option value="{{ $pt->value }}">{{ $pt->label() }}</option>
                                                @endforeach
                                            </flux:select>

                                            {{-- Planned action --}}
                                            <flux:select wire:model.live="p_planned_action" label="Planned Action">
                                                <option value="">— Select action —</option>
                                                @foreach ($plannedActions as $pa)
                                                    <option value="{{ $pa->value }}">{{ $pa->label() }}</option>
                                                @endforeach
                                            </flux:select>
                                        </div>

                                        {{-- Row 2: Contact + Custom action (conditional) --}}
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <flux:input wire:model="p_contact" label="Contact Details" placeholder="Phone / WhatsApp / Email" />
                                            @if ($p_planned_action === \App\Enums\PlannedAction::Custom->value)
                                                <div class="animate-in fade-in duration-200">
                                                    <flux:input wire:model="p_planned_action_custom" label="Describe the custom action"
                                                        placeholder="e.g. To hand-deliver contract…" />
                                                </div>
                                            @endif
                                        </div>

                                        @error('p_name') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                        @error('p_planned_action') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                        @error('p_planned_action_custom') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

                                        <flux:button wire:click="addPartnerToDraft" variant="filled" class="w-full" icon="plus">
                                            Add to Sequence
                                        </flux:button>
                                    </div>

                                    {{-- Draft sequence --}}
                                    @if (count($temp_partners) > 0)
                                        <div class="mt-4 border border-slate-200 rounded-2xl bg-white divide-y overflow-hidden shadow-sm">
                                            <div class="px-4 py-2.5 bg-slate-50 flex justify-between items-center">
                                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Planned Sequence</span>
                                                <span class="text-[10px] font-bold text-emerald-600">{{ count($temp_partners) }} partners</span>
                                            </div>
                                            @foreach ($temp_partners as $index => $tp)
                                                <div class="p-4 flex items-center gap-4 group hover:bg-slate-50 transition-colors">
                                                    <span class="h-6 w-6 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center text-[10px] font-bold flex-shrink-0">
                                                        {{ $index + 1 }}
                                                    </span>
                                                    <div class="flex-grow min-w-0">
                                                        <div class="flex items-center gap-2 flex-wrap">
                                                            <span class="font-bold text-slate-800 text-sm">{{ $tp['name'] }}</span>
                                                            <span class="px-1.5 py-0.5 bg-slate-100 text-slate-500 text-[9px] font-black rounded uppercase">{{ $tp['type'] }}</span>
                                                            @if (!empty($tp['planned_action']))
                                                                <span class="px-1.5 py-0.5 bg-emerald-50 text-emerald-700 text-[9px] font-black rounded uppercase border border-emerald-200">
                                                                    {{ !empty($tp['planned_action_custom'])
                                                                        ? $tp['planned_action_custom']
                                                                        : \App\Enums\PlannedAction::from($tp['planned_action'])->label() }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <p class="text-[10px] text-slate-400 mt-0.5 truncate">{{ $tp['contact'] }}</p>
                                                    </div>
                                                    <div class="flex items-center gap-1 flex-shrink-0">
                                                        @if (!$loop->first)
                                                            <button wire:click="movePartner({{ $index }}, 'up')" type="button"
                                                                class="p-1 hover:bg-slate-200 rounded text-slate-400 hover:text-slate-700 transition-colors">
                                                                <flux:icon.chevron-up variant="micro" />
                                                            </button>
                                                        @endif
                                                        @if (!$loop->last)
                                                            <button wire:click="movePartner({{ $index }}, 'down')" type="button"
                                                                class="p-1 hover:bg-slate-200 rounded text-slate-400 hover:text-slate-700 transition-colors">
                                                                <flux:icon.chevron-down variant="micro" />
                                                            </button>
                                                        @endif
                                                        <div class="w-px h-4 bg-slate-200 mx-1"></div>
                                                        <button wire:click="removePartner({{ $index }})" type="button"
                                                            class="p-1 hover:bg-red-50 rounded text-slate-300 hover:text-red-500 transition-colors">
                                                            <flux:icon.trash variant="micro" />
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                        <flux:button wire:click="createWorksheet" variant="primary"
                                            class="w-full mt-4 shadow-lg shadow-emerald-100" icon="play">
                                            Initialise & Begin Worksheet
                                        </flux:button>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- History sidebar --}}
                    <div class="space-y-4">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Recent History</p>
                        <div class="space-y-3">
                            @forelse($history as $h)
                                @php
                                    $hPct = $h->entries_count > 0
                                        ? round(($h->completed_entries_count / $h->entries_count) * 100)
                                        : 0;
                                @endphp
                                <a href="{{ route('worksheets.show', $h->id) }}" wire:navigate
                                    class="block p-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700
                                           rounded-2xl hover:border-emerald-400 dark:hover:border-emerald-600
                                           hover:shadow-md transition-all group cursor-pointer">
                                    <div class="flex justify-between items-start gap-2">
                                        <p class="font-bold text-slate-800 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors text-sm truncate">
                                            {{ $h->name }}
                                        </p>
                                        <span class="px-2 py-0.5 text-[9px] font-black uppercase rounded border {{ $h->worksheet_type->badgeClasses() }} flex-shrink-0">
                                            {{ $h->worksheet_type->label() }}
                                        </span>
                                    </div>
                                    <div class="mt-3">
                                        <div class="flex justify-between items-center mb-1">
                                            <span class="text-[10px] text-slate-400 dark:text-slate-500 font-bold">{{ $h->completed_entries_count }}/{{ $h->entries_count }} partners</span>
                                            <span class="text-[10px] font-black {{ $h->is_completed ? 'text-slate-400 dark:text-slate-500' : 'text-emerald-600 dark:text-emerald-400' }}">{{ $hPct }}%</span>
                                        </div>
                                        <div class="w-full bg-slate-100 dark:bg-slate-700 h-1.5 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full {{ $h->is_completed ? 'bg-slate-300 dark:bg-slate-600' : 'bg-emerald-500 dark:bg-emerald-400' }}" style="width: {{ $hPct }}%"></div>
                                        </div>
                                    </div>
                                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-2">{{ $h->created_at->format('d M Y') }} · {{ $h->created_at->diffForHumans() }}</p>
                                </a>
                            @empty
                                <div class="p-8 border-2 border-dashed rounded-2xl text-center text-slate-300">
                                    <p class="text-xs font-medium">No history yet</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

            @else
                {{-- ── Active worksheets for selected type tab ── --}}
                @php $tabType = WorksheetType::from($view_tab); @endphp
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-black text-slate-800">
                            Active {{ $tabType->label() }} Worksheets
                        </h2>
                        @if (!in_array($view_tab, $blockedTypes))
                            <flux:button wire:click="setTab('new'); $set('worksheet_type', '{{ $view_tab }}')"
                                variant="primary" size="sm" icon="plus" class=" cursor-pointer">
                                New {{ $tabType->label() }}
                            </flux:button>
                        @endif
                    </div>

                    @if (isset($activeByType[$view_tab]) && $activeByType[$view_tab]->isNotEmpty())
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                            @foreach ($activeByType[$view_tab] as $h)
                                @php
                                    $hPct = $h->entries_count > 0
                                        ? round(($h->completed_entries_count / $h->entries_count) * 100)
                                        : 0;
                                    $hSeqLocked = $h->sequenceLocked();
                                @endphp
                                <div class="bg-white dark:bg-slate-900 dark:border-slate-700 rounded-3xl border border-slate-200 shadow-sm p-6 space-y-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <h3 class="font-black text-slate-900 text-base truncate">{{ $h->name }}</h3>
                                            <p class="text-[10px] text-slate-400 mt-0.5">{{ $h->created_at->format('d M Y, H:i') }}</p>
                                        </div>
                                        <span class="px-2 py-0.5 text-[9px] font-black uppercase rounded border {{ $tabType->badgeClasses() }} flex-shrink-0">
                                            {{ $tabType->label() }}
                                        </span>
                                    </div>

                                    {{-- Progress --}}
                                    <div>
                                        <div class="flex justify-between items-center mb-1.5">
                                            <span class="text-[10px] text-slate-500 font-bold">{{ $h->completed_entries_count }} / {{ $h->entries_count }} partners</span>
                                            <span class="text-sm font-black text-emerald-600">{{ $hPct }}%</span>
                                        </div>
                                        <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                            <div class="bg-emerald-500 h-full rounded-full transition-all" style="width: {{ $hPct }}%"></div>
                                        </div>
                                    </div>

                                    {{-- Sequence lock indicator --}}
                                    @if ($hSeqLocked)
                                        <div class="flex items-center gap-2 text-[9px] font-black text-red-500 uppercase">
                                            <flux:icon.lock-closed variant="micro" /> Sequence locked
                                        </div>
                                    @else
                                        @php $sm = $h->sequenceMinutesRemaining(); @endphp
                                        <div class="flex items-center gap-2 text-[9px] font-black text-amber-600 uppercase">
                                            <flux:icon.clock variant="micro" /> {{ intdiv($sm, 60) }}h {{ $sm % 60 }}m to add partners
                                        </div>
                                    @endif

                                    <div class="flex gap-2">
                                        <flux:button wire:click="switchToWorksheet({{ $h->id }})" variant="primary" size="sm" icon="play" class="flex- cursor-pointer">
                                            Continue
                                        </flux:button>
                                        <flux:button href="{{ route('worksheets.show', $h->id) }}" wire:navigate
                                            variant="ghost" size="sm" icon="eye" class="coursor-pointer">
                                            View
                                        </flux:button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-20 bg-white rounded-3xl dark:bg-slate-900 dark:border-slate-700 border-2 border-dashed border-slate-200 text-center">
                            <p class="text-slate-400 font-bold">No active {{ $tabType->label() }} worksheets</p>
                            <flux:button wire:click="setTab('new'); $set('worksheet_type', '{{ $view_tab }}')"
                                variant="primary" size="sm" icon="plus" class="mt-4 cursor-pointer">
                                Create One
                            </flux:button>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    @endif
</div>