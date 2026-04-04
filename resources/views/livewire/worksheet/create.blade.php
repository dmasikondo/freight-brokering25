<?php

use App\Models\User;
use App\Models\WorksheetHeader;
use App\Models\WorksheetEntry;
use App\Enums\PartnerType;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    // Planning
    public $worksheet_name = '';
    public $temp_partners = [];
    public $p_name = '',
        $p_id = null,
        $p_contact = '',
        $p_type = 'general';

    // Execution
    public $activity = '',
        $feedback = '',
        $way_forward = '',
        $private_notes = '',
        $reminder_at = '';

    public $viewing_header_id = null;

    public function with()
    {
        $userId = Auth::id();
        $activeHeader = WorksheetHeader::where('user_id', $userId)->where('is_completed', false)->first();

        $currentEntry = null;
        $progress = 0;
        $completedCount = 0;
        $totalCount = 0;

        if ($activeHeader) {
            $allEntries = $activeHeader->entries;
            $totalCount = $allEntries->count();
            $completedCount = $allEntries->whereNotNull('completed_at')->count();
            $currentEntry = $allEntries->whereNull('completed_at')->first();
            $progress = $totalCount > 0 ? ($completedCount / $totalCount) * 100 : 0;
        }

        return [
            'activeHeader' => $activeHeader,
            'currentEntry' => $currentEntry,
            'progress' => $progress,
            'completedCount' => $completedCount,
            'totalCount' => $totalCount,
            'partnerTypes' => PartnerType::cases(),
            'available_partners' => User::where('contact_person', 'like', "%{$this->p_name}%")
                ->limit(5)
                ->get(),
            'history' => WorksheetHeader::withCount('entries')->where('user_id', $userId)->latest()->get(),
            'selected_worksheet' => $this->viewing_header_id ? WorksheetHeader::with('entries')->find($this->viewing_header_id) : null,
        ];
    }

    public function selectPartner($id, $name, $phone, $whatsapp)
    {
        $this->p_id = $id;
        $this->p_name = $name;

        // Create a clean string for the editable contact details field
        $details = [];
        if ($phone) {
            $details[] = "Phone: $phone";
        }
        if ($whatsapp) {
            $details[] = "WA: $whatsapp";
        }

        $this->p_contact = implode(' | ', $details);
    }

    public function addPartnerToDraft()
    {
        $this->validate(['p_name' => 'required', 'p_contact' => 'required', 'p_type' => 'required']);
        $this->temp_partners[] = [
            'name' => $this->p_name,
            'contact' => $this->p_contact,
            'type' => $this->p_type,
        ];
        $this->reset(['p_name', 'p_contact', 'p_id', 'p_type']);
    }

    public function createWorksheet()
    {
        $this->validate(['worksheet_name' => 'required', 'temp_partners' => 'required|array|min:1']);
        $header = WorksheetHeader::create(['user_id' => Auth::id(), 'name' => $this->worksheet_name]);
        foreach ($this->temp_partners as $idx => $p) {
            WorksheetEntry::create([
                'header_id' => $header->id,
                'partner_name' => $p['name'],
                'contact_details' => $p['contact'],
                'partner_type' => $p['type'],
                'sort_order' => $idx,
            ]);
        }
        $this->reset(['worksheet_name', 'temp_partners']);
    }

    public function startEntry($id)
    {
        WorksheetEntry::find($id)->update(['started_at' => now()]);
    }

    public function completeEntry($id)
    {
        $this->validate(
            [
                'activity' => 'required',
                'feedback' => 'required',
                'way_forward' => 'required',
                'reminder_at' => 'nullable|date|after:now',
                'private_notes' => 'nullable|string',
            ],
            [
                'reminder_at.after' => 'The follow-up reminder must be a future date and time.',
            ],
        );

        $entry = WorksheetEntry::find($id);
        $entry->update([
            'activity' => $this->activity,
            'feedback' => $this->feedback,
            'way_forward' => $this->way_forward,
            'private_notes' => $this->private_notes,
            'reminder_at' => $this->reminder_at ?: null,
            'completed_at' => now(),
        ]);

        $this->reset(['activity', 'feedback', 'way_forward', 'private_notes', 'reminder_at']);

        if (WorksheetEntry::where('header_id', $entry->header_id)->whereNull('completed_at')->count() === 0) {
            WorksheetHeader::find($entry->header_id)->update(['is_completed' => true]);
        }
    }

    public function viewWorksheet($id)
    {
        $this->viewing_header_id = $id;
    }
    public function closeView()
    {
        $this->viewing_header_id = null;
    }
}; ?>

<div class="p-6 max-w-7xl mx-auto space-y-10 min-h-screen bg-slate-50/50">

    @if (session('status'))
        <div
            class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl shadow-sm animate-in fade-in zoom-in duration-300">
            {{ session('status') }}
        </div>
    @endif

    @if (!$activeHeader && !$viewing_header_id)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
                    <h2 class="text-2xl font-black text-slate-800 mb-6 tracking-tight">Plan New Scouting Session</h2>

                    <div class="space-y-6">
                        <flux:input wire:model="worksheet_name" label="Session Name"
                            placeholder="e.g. Harare-Beira Lane Scouting" />

                        <div class="p-6 bg-slate-50 rounded-2xl border border-slate-200 border-dashed">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                                <div class="relative md:col-span-1">
                                    <flux:input wire:model.live="p_name" label="Partner Search"
                                        placeholder="Contact Person..." />
                                    @if ($p_name && !$p_id && count($available_partners) > 0)
                                        <div
                                            class="absolute z-30 w-full bg-white border shadow-2xl rounded-xl mt-2 overflow-hidden border-slate-200">
                                            @foreach ($available_partners as $u)
                                                <button type="button" {{-- FIX: Pass $u->contact_phone and $u->whatsapp --}}
                                                    wire:click="selectPartner({{ $u->id }}, '{{ $u->contact_person }}', '{{ $u->contact_phone }}', '{{ $u->whatsapp }}')"
                                                    class="w-full text-left p-3 hover:bg-blue-50 border-b last:border-0 transition-colors">
                                                    <p class="text-sm font-bold text-slate-900">{{ $u->contact_person }}
                                                    </p>
                                                    <p class="text-[10px] text-slate-500 uppercase">{{ $u->email }}
                                                    </p>
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                <flux:select wire:model="p_type" label="Partner Type">
                                    @foreach ($partnerTypes as $type)
                                        <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                    @endforeach
                                </flux:select>
                                <flux:input wire:model="p_contact" label="Contact Details (Editable)" />
                            </div>
                            <flux:button wire:click="addPartnerToDraft" variant="filled" class="mt-4 w-full"
                                icon="plus">
                                Add to Sequence
                            </flux:button>
                        </div>

                        @if (count($temp_partners) > 0)
                            <div
                                class="mt-4 border border-slate-200 rounded-2xl bg-white divide-y overflow-hidden shadow-sm">
                                <div
                                    class="px-4 py-2 bg-slate-50 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                    Planned Sequence</div>
                                @foreach ($temp_partners as $index => $tp)
                                    <div
                                        class="p-4 flex justify-between items-center bg-white group hover:bg-slate-50 transition-colors">
                                        <div class="flex items-center gap-4">
                                            <span
                                                class="h-6 w-6 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center text-[10px] font-bold">{{ $index + 1 }}</span>
                                            <div>
                                                <span class="font-bold text-slate-800">{{ $tp['name'] }}</span>
                                                <span
                                                    class="ml-2 px-2 py-0.5 rounded bg-blue-50 text-blue-600 text-[9px] font-black uppercase">{{ $tp['type'] }}</span>
                                            </div>
                                        </div>
                                        <span
                                            class="text-xs text-slate-400 font-mono">{{ Str::limit($tp['contact'], 40) }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <flux:button wire:click="createWorksheet" variant="primary"
                                class="w-full shadow-lg shadow-blue-100" icon="play">
                                Initialize & Start Worksheet
                            </flux:button>
                        @endif
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-2">Session History</h3>
                <div class="space-y-3">
                    @forelse($history as $h)
                        <button wire:click="viewWorksheet({{ $h->id }})"
                            class="w-full text-left p-4 bg-white border border-slate-200 rounded-2xl hover:border-blue-400 hover:shadow-md transition-all group">
                            <div class="flex justify-between items-start">
                                <p class="font-bold text-slate-800 group-hover:text-blue-600 transition-colors">
                                    {{ $h->name }}</p>
                                <flux:icon.chevron-right variant="micro"
                                    class="text-slate-300 group-hover:text-blue-400" />
                            </div>
                            <div class="flex justify-between items-center mt-3">
                                <span
                                    class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter">{{ $h->entries_count }}
                                    Partners</span>
                                <div class="text-right">
                                    <p class="text-[10px] text-slate-500 font-medium">
                                        {{ $h->created_at->format('d M Y') }}</p>
                                    <p class="text-[9px] text-slate-400 italic">{{ $h->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        </button>
                    @empty
                        <div class="p-10 border-2 border-dashed rounded-3xl text-center text-slate-300">
                            <flux:icon.archive-box variant="micro" class="mx-auto mb-2 opacity-20" />
                            <p class="text-xs font-medium">No archived sessions</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @elseif($activeHeader)
        <div
            class="bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden animate-in slide-in-from-bottom-6 duration-700">
            <div class="p-8 bg-slate-900 text-white">
                <div class="flex justify-between items-end mb-6">
                    <div>
                        <h1 class="text-3xl font-black tracking-tight">{{ $activeHeader->name }}</h1>
                        <p class="text-slate-400 text-sm mt-1">
                            <span class="text-blue-400 font-bold">{{ $completedCount }}</span> of {{ $totalCount }}
                            partners attended to
                        </p>
                    </div>
                    <div class="text-right">
                        <span class="text-4xl font-black text-blue-400 leading-none">{{ round($progress) }}%</span>
                        <p class="text-[10px] text-slate-500 uppercase font-black tracking-widest mt-1">Progress</p>
                    </div>
                </div>
                <div class="w-full bg-slate-800 h-2 rounded-full overflow-hidden">
                    <div class="bg-blue-500 h-full transition-all duration-1000 ease-out shadow-[0_0_15px_rgba(59,130,246,0.5)]"
                        style="width: {{ $progress }}%"></div>
                </div>
            </div>

            <div class="p-10">
                @if ($currentEntry)
                    <div class="grid grid-cols-1 lg:grid-cols-4 gap-12">

                        <div class="lg:col-span-2 space-y-8 border-r border-slate-100 pr-12">
                            <div class="flex items-start gap-6">
                                <div
                                    class="h-14 w-14 rounded-2xl bg-blue-600 text-white flex items-center justify-center font-black text-2xl shadow-xl shadow-blue-200 ring-4 ring-blue-50">
                                    {{ $completedCount + 1 }}
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h2 class="text-2xl font-black text-slate-900 leading-none">
                                            {{ $currentEntry->partner_name }}</h2>
                                        <span
                                            class="px-2 py-1 bg-slate-100 text-slate-500 text-[10px] font-black rounded uppercase">
                                            {{ $currentEntry->partner_type->label() }}
                                        </span>
                                    </div>
                                    <p class="text-slate-500 mt-2 flex items-center text-sm italic">
                                        <flux:icon.phone variant="micro" class="mr-2 opacity-40" />
                                        {{ $currentEntry->contact_details }}
                                    </p>
                                </div>
                            </div>

                            @if (!$currentEntry->started_at)
                                <div class="py-24 border-2 border-dashed border-slate-200 rounded-[2rem] text-center bg-slate-50/50 group hover:bg-white hover:border-blue-300 transition-all cursor-pointer"
                                    wire:click="startEntry({{ $currentEntry->id }})">
                                    <div
                                        class="h-16 w-16 bg-white rounded-full flex items-center justify-center mx-auto shadow-md mb-4 group-hover:scale-110 transition-transform">
                                        <flux:icon.play variant="micro" class="text-blue-600" />
                                    </div>
                                    <p class="font-bold text-slate-800">Start Interaction</p>
                                    <p class="text-xs text-slate-400 mt-1">Unlock logs and start the timer for this
                                        partner</p>
                                </div>
                            @else
                                <form wire:submit.prevent="completeEntry({{ $currentEntry->id }})"
                                    class="space-y-6 animate-in fade-in duration-500">
                                    <flux:textarea wire:model="activity" label="Action Taken" rows="auto"
                                        placeholder="Discussed transit times, rate requirements..." />

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <flux:textarea wire:model="feedback" label="Partner Feedback" rows="auto" />
                                        <flux:textarea wire:model="way_forward" label="Way Forward" rows="auto" />
                                    </div>

                                    <div class="p-6 bg-amber-50/50 rounded-3xl border border-amber-100 space-y-4">
                                        <div
                                            class="flex items-center gap-2 text-amber-800 font-black text-[10px] uppercase tracking-widest">
                                            <flux:icon.lock-closed variant="micro" />
                                            Confidential Notes & Scheduling
                                        </div>
                                        <flux:textarea wire:model="private_notes"
                                            placeholder="Internal intelligence (Desperation levels, back-haul needs, etc)..."
                                            rows="auto" />
                                        <flux:input type="datetime-local" wire:model="reminder_at"
                                            label="Set Follow-up Time" />
                                    </div>

                                    <flux:button type="submit" variant="primary"
                                        class="w-full py-4 text-lg font-bold shadow-xl shadow-blue-100"
                                        icon="check-circle">
                                        Finalize Partner #{{ $completedCount + 1 }}
                                    </flux:button>
                                </form>
                            @endif
                        </div>

                        <div class="lg:col-span-2 space-y-10">
                            <div>
                                <h3
                                    class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center">
                                    <span class="mr-4">Remaining Queue</span>
                                    <span class="h-px flex-grow bg-slate-100"></span>
                                </h3>
                                <div class="space-y-4">
                                    @php
                                        $upcoming = \App\Models\WorksheetEntry::where('header_id', $activeHeader->id)
                                            ->whereNull('completed_at')
                                            ->where('id', '!=', $currentEntry->id)
                                            ->orderBy('sort_order', 'asc')
                                            ->get();
                                    @endphp
                                    @forelse($upcoming as $u)
                                        <div
                                            class="flex items-center gap-4 opacity-40 grayscale hover:grayscale-0 transition-all">
                                            <div
                                                class="h-8 w-8 rounded-full border border-slate-200 flex items-center justify-center text-[10px] font-bold text-slate-400">
                                                {{ $u->sort_order + 1 }}</div>
                                            <div class="flex-grow">
                                                <p class="text-sm font-bold text-slate-700">{{ $u->partner_name }}</p>
                                                <p class="text-[10px] text-slate-400 uppercase">
                                                    {{ $u->partner_type->label() }}</p>
                                            </div>
                                            <flux:icon.lock-closed variant="micro" class="opacity-20" />
                                        </div>
                                    @empty
                                        <div
                                            class="p-6 bg-emerald-50 border border-emerald-100 rounded-3xl flex items-center gap-4">
                                            <div
                                                class="h-10 w-10 bg-emerald-500 text-white rounded-full flex items-center justify-center shadow-lg shadow-emerald-100">
                                                <flux:icon.check variant="micro" />
                                            </div>
                                            <p class="text-sm font-bold text-emerald-800">Final interaction of the
                                                session!</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            <div>
                                <h3
                                    class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center">
                                    <span class="mr-4">Completed Log</span>
                                    <span class="h-px flex-grow bg-slate-100"></span>
                                </h3>
                                <div class="space-y-3">
                                    @php
                                        $done = \App\Models\WorksheetEntry::where('header_id', $activeHeader->id)
                                            ->whereNotNull('completed_at')
                                            ->orderBy('completed_at', 'desc')
                                            ->limit(3)
                                            ->get();
                                    @endphp
                                    @foreach ($done as $d)
                                        <div
                                            class="p-4 bg-slate-50 border border-slate-100 rounded-2xl flex justify-between items-center">
                                            <div class="flex items-center gap-3">
                                                <flux:icon.check-circle variant="micro" class="text-emerald-500" />
                                                <span
                                                    class="text-xs font-bold text-slate-800">{{ $d->partner_name }}</span>
                                            </div>
                                            <span
                                                class="text-[10px] text-slate-400 font-mono">{{ $d->completed_at->format('H:i') }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if ($selected_worksheet)
        <div
            class="fixed inset-0 bg-slate-900/70 backdrop-blur-md z-50 flex items-center justify-center p-6 animate-in fade-in duration-300">
            <div
                class="bg-white w-full max-w-5xl max-h-[90vh] overflow-hidden rounded-[2.5rem] shadow-2xl flex flex-col">

                <div class="p-8 border-b bg-slate-50 flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-black text-slate-900 leading-none">{{ $selected_worksheet->name }}
                        </h2>
                        <div class="flex gap-4 mt-2">
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                Opened: {{ $selected_worksheet->created_at->format('d M Y, H:i') }}
                                <span
                                    class="text-slate-300 ml-1">({{ $selected_worksheet->created_at->diffForHumans() }})</span>
                            </div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                Finalized: {{ $selected_worksheet->updated_at->format('d M Y, H:i') }}
                                <span
                                    class="text-slate-300 ml-1">({{ $selected_worksheet->updated_at->diffForHumans() }})</span>
                            </div>
                        </div>
                    </div>
                    <flux:button wire:click="closeView" variant="ghost" icon="x-mark"
                        class="hover:bg-slate-200 rounded-full" />
                </div>

                <div class="p-10 overflow-y-auto space-y-12">
                    @foreach ($selected_worksheet->entries as $ent)
                        <div class="relative pl-12 border-l-2 border-slate-100">
                            <div
                                class="absolute -left-[11px] top-0 h-5 w-5 rounded-full bg-white border-2 border-blue-500 flex items-center justify-center text-[10px] font-black text-blue-600">
                                {{ $loop->iteration }}
                            </div>

                            <div class="flex justify-between items-start mb-6">
                                <div>
                                    <h3 class="text-xl font-bold text-slate-900">{{ $ent->partner_name }}</h3>
                                    <div class="flex items-center gap-3 mt-1">
                                        <span
                                            class="px-2 py-0.5 rounded bg-slate-100 text-slate-500 text-[9px] font-black uppercase">
                                            {{ $ent->partner_type->label() }}
                                        </span>
                                        <span class="text-[10px] text-slate-400 italic">
                                            Logged at {{ $ent->completed_at?->format('H:i') }}
                                            ({{ $ent->completed_at?->diffForHumans() }})
                                        </span>
                                    </div>
                                </div>

                                @if ($ent->reminder_at)
                                    <div
                                        class="px-4 py-2 bg-orange-50 border border-orange-100 rounded-2xl text-right">
                                        <div
                                            class="flex items-center justify-end gap-1 text-[9px] font-black text-orange-400 uppercase tracking-widest">
                                            <flux:icon.clock variant="micro" />
                                            Follow-up
                                        </div>
                                        <p class="text-xs font-bold text-orange-700">
                                            {{ $ent->reminder_at->format('d M Y, H:i') }}</p>
                                        <p class="text-[10px] text-orange-500 font-medium italic">
                                            {{ $ent->reminder_at->diffForHumans() }}</p>
                                    </div>
                                @endif
                            </div>

                            <div
                                class="grid grid-cols-1 md:grid-cols-3 gap-8 p-6 bg-slate-50/50 rounded-[2rem] border border-slate-100">
                                <div class="space-y-1">
                                    <h4 class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Activity
                                    </h4>
                                    <p class="text-sm text-slate-700 leading-relaxed">{{ $ent->activity }}</p>
                                </div>
                                <div class="space-y-1">
                                    <h4 class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Feedback
                                    </h4>
                                    <p class="text-sm text-slate-700 leading-relaxed">{{ $ent->feedback }}</p>
                                </div>
                                <div class="space-y-1">
                                    <h4 class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Way
                                        Forward</h4>
                                    <p class="text-sm text-blue-700 font-bold leading-relaxed">{{ $ent->way_forward }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="p-8 bg-slate-50 border-t flex justify-end">
                    <flux:button wire:click="closeView" variant="filled" class="px-10">Done Viewing Archive
                    </flux:button>
                </div>
            </div>
        </div>
    @endif
</div>
