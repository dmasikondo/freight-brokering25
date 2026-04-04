<?php

use App\Models\WorksheetHeader;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    use WithPagination;

    // Filter States
    public $search = '';
    public $author_search = '';
    public $territory_id = '';
    public $status = 'all';
    public $date_from;
    public $date_to;
    public $perPage = 10;
    public $is_global = false;

    // View State
    public $viewingWorksheet = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => 'all'],
        'perPage' => ['except' => 10],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function viewArchive($id)
    {
        $this->viewingWorksheet = WorksheetHeader::with([
            'entries' => function ($q) {
                $q->orderBy('sort_order', 'asc');
            },
        ])->findOrFail($id);
    }

    public function closeArchive()
    {
        $this->viewingWorksheet = null;
    }

    public function with()
    {
        $query = WorksheetHeader::query()
            ->with(['user', 'user.territories'])
            ->withCount('entries');

        if (!$this->is_global) {
            $query->where('user_id', Auth::id());
        }

        // Filter: Name
        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        // Filter: Author Name (via Join or WhereHas)
        if ($this->author_search) {
            $query->whereHas('user', function ($q) {
                $q->whereAny(['contact_person', 'email', 'organisation'], 'like', '%' . $this->author_search . '%');
            });
        }

        // Filter: Status
        if ($this->status === 'completed') {
            $query->where('is_completed', true);
        } elseif ($this->status === 'active') {
            $query->where('is_completed', false);
        }

        // Filter: Date Range
        if ($this->date_from) {
            $query->whereDate('created_at', '>=', $this->date_from);
        }
        if ($this->date_to) {
            $query->whereDate('created_at', '<=', $this->date_to);
        }

        //Filter by Territory
        if ($this->territory_id) {
            $query->whereHas('user.territories', function ($q) {
                $q->where('territories.id', $this->territory_id);
            });
        }

        return [
            'worksheets' => $query->latest()->paginate($this->perPage),
            'all_territories' => \App\Models\Territory::orderBy('name')->get(),
        ];
    }
}; ?>

<div class="p-8 max-w-7xl mx-auto space-y-6 min-h-screen bg-slate-50/50">

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">Scouting Archive</h1>
            <p class="text-slate-500 text-sm">Review historical partner interactions and logistics leads.</p>
        </div>
        <flux:button icon="plus" href="{{ route('worksheets.create') }}" wire:navigate
            class="bg-[#bef264] hover:bg-[#a3e635] text-slate-900 font-bold border-none shadow-lg shadow-lime-100">
            New Session
        </flux:button>
    </div>

    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
            <flux:input label="Session Name" wire:model.live.debounce.300ms="search" icon="magnifying-glass"
                placeholder="Search..." />

            <flux:input label="Author" wire:model.live.debounce.300ms="author_search" icon="user"
                placeholder="Team member..." />

            <flux:select wire:model.live="status" label="Status">
                <option value="all">All Statuses</option>
                <option value="completed">Completed</option>
                <option value="active">Active</option>
            </flux:select>

            <flux:input type="date" wire:model.live="date_from" label="From" />
            <flux:input type="date" wire:model.live="date_to" label="To" />

            <flux:select label="Territory" wire:model.live="territory_id" placeholder="All Regions">
                <x-slot name="icon"><flux:icon.map-pin variant="micro" /></x-slot>
                <option value="">All Regions</option>
                @foreach ($all_territories as $t)
                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                @endforeach
            </flux:select>
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-slate-100">
            <div class="flex items-center gap-6">
                <div class="flex items-center gap-2">
                    <flux:checkbox wire:model.live="is_global" label="Global View" />
                    <flux:tooltip content="View worksheets from all team members">
                        <flux:icon.information-circle variant="micro" class="text-slate-400" />
                    </flux:tooltip>
                </div>

                <div class="h-4 w-px bg-slate-200"></div>

                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Show</span>
                    <select wire:model.live="perPage"
                        class="text-xs font-bold border-slate-200 rounded-xl bg-white py-1">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>

            <flux:button variant="ghost" size="sm"
                wire:click="$set('search', ''); $set('author_search', ''); $set('status', 'all'); $set('date_from', null); $set('date_to', null); $set('is_global', false);">
                Clear All Filters
            </flux:button>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4">
        @forelse($worksheets as $ws)
            <div class="bg-white border border-slate-200 rounded-3xl p-6 hover:border-blue-300 transition-all">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div class="flex items-start gap-4">
                        <div class="h-10 w-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400">
                            <flux:icon.archive-box variant="micro" />
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">{{ $ws->name }}</h2>
                            <div class="flex items-center gap-3 mt-1">
                                <span
                                    class="px-2 py-0.5 rounded text-[9px] font-black uppercase {{ $ws->is_completed ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }}">
                                    {{ $ws->is_completed ? 'Completed' : 'Partial' }}
                                </span>
                                <span class="text-xs text-slate-400">{{ $ws->entries_count }} Partners</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-8 border-l border-slate-100 pl-8 hidden lg:flex">
                        <div class="text-right">
                            <p class="text-[10px] font-black text-slate-400 uppercase">Created</p>
                            <p class="text-xs font-bold text-slate-700">{{ $ws->created_at->format('d M Y') }}</p>
                            <p class="text-[9px] text-slate-400 italic">{{ $ws->created_at->diffForHumans() }}</p>
                        </div>
                    </div>

                    <flux:button variant="filled" wire:click="viewArchive({{ $ws->id }})" icon="eye">
                        View Logs
                    </flux:button>
                </div>
            </div>
        @empty
            <div class="py-20 text-center bg-white rounded-3xl border-2 border-dashed border-slate-200">
                <p class="text-slate-400 font-medium">No records found matching your filters.</p>
            </div>
        @endforelse

        <div class="py-4">
            {{ $worksheets->links() }}
        </div>
    </div>

    @if ($viewingWorksheet)
        <div
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex justify-end animate-in slide-in-from-right duration-500">
            <div class="w-full max-w-3xl bg-white shadow-2xl flex flex-col h-full">

                <div class="p-8 border-b bg-slate-50 flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-black text-slate-900 tracking-tight">{{ $viewingWorksheet->name }}
                        </h2>
                        <div class="flex flex-col gap-1 mt-2">
                            <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest">
                                Opened: <span
                                    class="text-slate-600">{{ $viewingWorksheet->created_at->format('d M Y, H:i') }}</span>
                                <span
                                    class="text-slate-300 ml-1">({{ $viewingWorksheet->created_at->diffForHumans() }})</span>
                            </div>
                            <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest">
                                Finalized: <span
                                    class="text-slate-600">{{ $viewingWorksheet->updated_at->format('d M Y, H:i') }}</span>
                                <span
                                    class="text-slate-300 ml-1">({{ $viewingWorksheet->updated_at->diffForHumans() }})</span>
                            </div>
                        </div>
                    </div>
                    <flux:button wire:click="closeArchive" variant="ghost" icon="x-mark" class="rounded-full" />
                </div>

                <div class="flex-grow overflow-y-auto p-8 space-y-10">
                    @foreach ($viewingWorksheet->entries as $ent)
                        <div class="relative pl-8 border-l-2 border-slate-100">
                            <div
                                class="absolute -left-[9px] top-0 h-4 w-4 rounded-full bg-white border-2 border-blue-500 flex items-center justify-center text-[8px] font-black text-blue-600">
                                {{ $loop->iteration }}
                            </div>

                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="font-black text-slate-800 text-lg leading-none">{{ $ent->partner_name }}
                                    </h3>
                                    <div class="flex items-center gap-2 mt-2">
                                        <span
                                            class="text-[9px] bg-blue-50 text-blue-600 px-2 py-0.5 rounded font-black uppercase">
                                            {{ $ent->partner_type->label() }}
                                        </span>
                                        <span class="text-[10px] text-slate-400 italic">
                                            Logged: {{ $ent->completed_at?->format('H:i') }}
                                            ({{ $ent->completed_at?->diffForHumans() }})
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="grid grid-cols-1 md:grid-cols-3 gap-6 p-5 bg-slate-50/50 rounded-2xl border border-slate-100">
                                <div class="space-y-1">
                                    <label
                                        class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Activity</label>
                                    <p class="text-xs text-slate-600 leading-relaxed">{{ $ent->activity }}</p>
                                </div>
                                <div class="space-y-1">
                                    <label
                                        class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Feedback</label>
                                    <p class="text-xs text-slate-600 leading-relaxed">{{ $ent->feedback }}</p>
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Way
                                        Forward</label>
                                    <p class="text-xs text-blue-700 font-bold leading-relaxed">{{ $ent->way_forward }}
                                    </p>
                                </div>
                            </div>

                            @if ($viewingWorksheet->user_id === auth()->id() && $ent->private_notes)
                                <div class="mt-3 p-3 bg-white border border-slate-200 rounded-xl shadow-sm">
                                    <label
                                        class="flex items-center gap-1.5 text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1">
                                        <flux:icon.lock-closed variant="micro" class="text-slate-300" />
                                        Internal Intel
                                    </label>
                                    <p class="text-[11px] text-slate-500 italic">"{{ $ent->private_notes }}"</p>
                                </div>
                            @endif

                            @if ($ent->reminder_at)
                                <div
                                    class="mt-3 p-3 bg-orange-50 border border-orange-100 rounded-xl flex justify-between items-center">
                                    <div class="flex items-center gap-2">
                                        <flux:icon.clock variant="micro" class="text-orange-400" />
                                        <span class="text-[9px] font-black text-orange-600 uppercase italic">Follow-up
                                            Due</span>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[10px] font-bold text-orange-700">
                                            {{ $ent->reminder_at->format('d M Y, H:i') }}</p>
                                        <p class="text-[9px] text-orange-500 font-medium">
                                            {{ $ent->reminder_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="p-6 border-t bg-slate-50 text-right">
                    <flux:button wire:click="closeArchive" variant="filled" class="px-8">Close Viewer</flux:button>
                </div>
            </div>
        </div>
    @endif
</div>
