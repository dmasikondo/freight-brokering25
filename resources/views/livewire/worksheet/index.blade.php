<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\WorksheetHeader;
use App\Models\Territory;
use App\Models\User;
//use Flux\Flux;

new class extends Component {
    use WithPagination;

    // Filter States
    public $search = '';
    public $author_search = '';
    public $territory_id = '';
    public $status = 'all';
    public $date_from;
    public $date_to;
    public $reminder_filter = 'all';
    public $perPage = 10;
    public $is_global = false;
    public $staff_search;

    // View State
    public $viewingWorksheet = null;
    public $sharingWorksheet = null;
    public $selectedStaff = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => 'all'],
        'perPage' => ['except' => 10],
    ];

    public function openShareModal($id)
    {
        // Load the worksheet with its current shared users
        $this->sharingWorksheet = WorksheetHeader::with('sharedWith')->findOrFail($id);

        // Sync the checkbox state with existing shares
        $this->selectedStaff = $this->sharingWorksheet->sharedWith->pluck('id')->toArray();

        $this->modal('share-modal')->show();
    }

public function toggleShare($userId)
{
    if (in_array($userId, $this->selectedStaff)) {
        $this->sharingWorksheet->sharedWith()->detach($userId);
        $this->selectedStaff = array_diff($this->selectedStaff, [$userId]);
        $message = 'Access withdrawn.';
    } else {
        $this->sharingWorksheet->sharedWith()->attach($userId);
        $this->selectedStaff[] = $userId;
        $message = 'Access granted.';
    }
    
    // Dispatch a simple browser event
    $this->dispatch('notify', message: $message);
}

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
        $worksheet = WorksheetHeader::findOrFail($id);

        // AUTHORIZATION: Check the 'view' policy for this specific worksheet
        if (!auth()->user()->can('view', $worksheet)) {
            abort(403, 'You do not have permission to view this specific worksheet.');
        }

        $this->viewingWorksheet = $worksheet->load(['entries' => fn($q) => $q->orderBy('sort_order', 'asc')]);
    }

    public function closeArchive()
    {
        $this->viewingWorksheet = null;
    }

    

    public function with()
    {
        $user = auth()->user();
        $query = WorksheetHeader::query()
            ->with(['user.territories', 'sharedWith'])
            ->withCount('entries');

        if (!$this->is_global && !$user->hasAnyRole(['admin', 'superadmin'])) {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)->orWhereHas('sharedWith', fn($sq) => $sq->where('user_id', $user->id));
            });
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

        // Filter: Reminder Status
        if ($this->reminder_filter === 'overdue') {
            $query->whereHas('entries', function ($q) {
                $q->whereNotNull('reminder_at')->where('reminder_at', '<', now());
            });
        } elseif ($this->reminder_filter === 'forthcoming') {
            $query->whereHas('entries', function ($q) {
                $q->whereNotNull('reminder_at')->where('reminder_at', '>', now());
            });
        }

        return [
            'worksheets' => $query->latest()->paginate($this->perPage),
            'all_territories' => Territory::orderBy('name')->get(),
            // Get all backend staff except the owner of the current worksheet
            'available_staff' => User::where('id', '!=', auth()->id())
                ->whereHas('roles', function ($q) {
                    $q->whereIn('name', ['superadmin', 'admin', 'marketing logistics associate', 'procurement logistics associate', 'operations logistics associate', 'logistics operations executive']);
                })
                ->when($this->staff_search, function ($q) {
                    $q->whereAny(['users.contact_person', 'users.email'], 'like', "%{$this->staff_search}%");
                })
                ->get(),
        ];
    }

    public function mount()
    {
        // AUTHORIZATION: Ensure only backend staff can even land here
        if (!auth()->user()->can('viewAny', WorksheetHeader::class)) {
            abort(403, 'Unauthorized access to worksheet archives.');
        }
    }
}; ?>

<div class="p-8 max-w-7xl mx-auto space-y-6 min-h-screen bg-slate-50/50">
    <div x-data="{ show: false, message: '' }"
     x-on:notify.window="show = true; message = $event.detail.message; setTimeout(() => show = false, 3000)"
     x-show="show"
     x-transition.out.opacity.duration.1000ms
     class="fixed bottom-5 right-5 z-[100] bg-slate-900 text-white px-6 py-3 rounded-2xl shadow-2xl flex items-center gap-3 border border-slate-700"
     style="display: none;">
    <flux:icon.check-circle class="text-emerald-400 h-5 w-5" />
    <span class="text-xs font-bold" x-text="message"></span>
</div>

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

            <flux:select label="Reminders" wire:model.live="reminder_filter">
                <x-slot name="icon">
                    <flux:icon.bell variant="micro" />
                </x-slot>
                <option value="all">All Reminders</option>
                <option value="overdue">Overdue (Past)</option>
                <option value="forthcoming">Forthcoming (Future)</option>
            </flux:select>
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-slate-100">
            <div class="flex items-center gap-6">
                @can('viewGlobal', App\Models\WorksheetHeader::class)
                    <div class="flex items-center gap-2">
                        <flux:checkbox wire:model.live="is_global" label="Global View" />
                        <flux:tooltip content="View worksheets from all team members">
                            <flux:icon.information-circle variant="micro" class="text-slate-400" />
                        </flux:tooltip>
                    </div>

                    <div class="h-4 w-px bg-slate-200"></div>
                @endcan

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
    {{-- 1. Main Card Container: Visual highlight for any shared content --}}
    <div class="relative bg-white border-y md:border-x {{ $ws->sharedWith->isNotEmpty() ? 'border-l-4 border-l-lime-500 bg-lime-50/5' : 'border-slate-200' }} md:rounded-3xl p-6 mb-4 shadow-sm transition-all hover:shadow-md">
        
        {{-- 2. "Shared With Me" Banner: Only shows if you are NOT the owner but are a collaborator --}}
        @if($ws->user_id !== auth()->id() && $ws->sharedWith->contains(auth()->id()))
            <div class="absolute -top-3 left-6 px-3 py-1 bg-lime-600 text-white text-[10px] font-black uppercase tracking-widest rounded-full shadow-lg flex items-center gap-2 border border-lime-400">
                <flux:icon.users variant="micro" />
                Collaborator Access
            </div>
        @endif

        <div class="flex flex-col md:flex-row justify-between items-start gap-4">
            <div class="flex-grow">
                {{-- 3. Title & Header Info --}}
                <div class="flex items-center gap-3">
                    <h2 class="text-lg font-black text-slate-900 tracking-tight">{{ $ws->name }}</h2>
                    @if($ws->is_completed)
                        <flux:icon.lock-closed variant="micro" class="text-slate-400" tooltip="This session is finalized" />
                    @endif
                </div>
                
                <div class="flex flex-wrap items-center gap-3 mt-2">
                    {{-- Status Badge --}}
                    <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase {{ $ws->is_completed ? 'bg-slate-100 text-slate-500' : 'bg-emerald-50 text-emerald-600' }}">
                        {{ $ws->is_completed ? 'Completed' : 'Active Session' }}
                    </span>

                    {{-- Ownership Context --}}
                    <span class="flex items-center gap-1 text-xs text-slate-500 font-medium">
                        <span class="text-slate-400 font-normal italic text-[10px]">Initiated by</span>
                        <flux:icon.user variant="micro" class="text-slate-300" />
                        <span class="{{ $ws->user_id === auth()->id() ? 'text-lime-600 font-bold' : '' }}">
                            {{ $ws->user_id === auth()->id() ? 'You' : $ws->user->contact_person }}
                        </span>
                    </span>

                    {{-- Overdue Reminder Check --}}
                    @php
                        $hasOverdue = $ws->entries()->whereNotNull('reminder_at')->where('reminder_at', '<', now())->exists();
                    @endphp
                    @if($hasOverdue && !$ws->is_completed)
                        <span class="flex items-center gap-1 px-2 py-0.5 rounded bg-red-50 text-red-600 text-[9px] font-black uppercase animate-pulse">
                            <flux:icon.exclamation-triangle variant="micro" />
                            Overdue Action
                        </span>
                    @endif
                </div>

                {{-- 4. Team List: Displays colleagues shared on this worksheet (Excluding current user) --}}
                @php
                    $otherCollaborators = $ws->sharedWith->filter(fn($u) => $u->id !== auth()->id());
                @endphp

                @if($otherCollaborators->isNotEmpty() || ($ws->user_id === auth()->id() && $ws->sharedWith->isNotEmpty()))
                    <div class="mt-4 pt-4 border-t border-slate-100 flex flex-wrap items-center gap-2">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mr-1">Sharing Team:</span>
                        
                        @foreach($ws->sharedWith as $staff)
                            @if($staff->id !== auth()->id()) {{-- Hide yourself from the list to reduce noise --}}
                                <a href="{{ route('users.show', $staff->slug) }}" 
                                   class="flex items-center gap-2 px-2 py-1 rounded-xl bg-white border border-slate-200 hover:border-lime-400 transition-all group"
                                   wire:navigate>
                                    <div class="h-4 w-4 rounded-full bg-slate-100 flex items-center justify-center text-[8px] font-black text-slate-500 group-hover:bg-lime-600 group-hover:text-white">
                                        {{ strtoupper(substr($staff->contact_person, 0, 1)) }}
                                    </div>
                                    <div class="flex flex-col leading-none pr-1">
                                        <span class="text-[10px] font-black text-slate-700 group-hover:text-lime-700">{{ $staff->contact_person }}</span>
                                        <span class="text-[7px] text-slate-400 uppercase font-black tracking-tighter">{{ $staff->roles->first()?->name }}</span>
                                    </div>
                                </a>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- 5. Context-Aware Action Buttons --}}
            <div class="flex items-center gap-2 w-full md:w-auto justify-end">
                {{-- Manage Sharing (Only for Owner) --}}
                @if($ws->user_id === auth()->id())
                    <flux:button variant="ghost" size="sm" icon="share" wire:click="openShareModal({{ $ws->id }})" tooltip="Manage Sharing" />
                @endif
                
                {{-- Dynamic Entry Button --}}
                @if(!$ws->is_completed)
                    @can('update', $ws)
                        <flux:button variant="filled" size="sm" icon="pencil-square" color="lime"
                            href="{{ route('worksheets.create', ['id' => $ws->id]) }}"
                            >
                            {{ $ws->user_id === auth()->id() ? 'Manage' : 'Contribute' }}
                        </flux:button>
                        @endcan
                        <flux:button variant="ghost" size="sm" icon="eye" wire:click="viewArchive({{ $ws->id }})">
                            View Only
                        </flux:button>                   
                    
                @else
                    <flux:button variant="ghost" size="sm" icon="archive-box" wire:click="viewArchive({{ $ws->id }})">
                        View Archive
                    </flux:button>
                @endif
            </div>
        </div>

        {{-- Footer Meta --}}
        <div class="mt-4 flex items-center gap-4 text-[10px] text-slate-400 font-medium">
            <span class="flex items-center gap-1">
                <flux:icon.calendar variant="micro" class="text-slate-300" />
                {{ $ws->created_at->format('d M Y') }} ({{ $ws->created_at->diffForHumans() }})
            </span>
            <span class="h-1 w-1 rounded-full bg-slate-200"></span>
            <span>{{ $ws->entries_count }} Logged Entities</span>
        </div>
    </div>

@empty
    {{-- 6. Empty State --}}
    <div class="flex flex-col items-center justify-center py-20 bg-slate-50 rounded-3xl border-2 border-dashed border-slate-200">
        <flux:icon.document-magnifying-glass class="h-12 w-12 text-slate-300 mb-4" />
        <h3 class="text-lg font-bold text-slate-900">No Worksheets Found</h3>
        <p class="text-slate-500 text-sm">Adjust your filters or start a new scouting session.</p>
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
                        <div class="flex items-center gap-2">
    <span class="text-xs text-slate-500">Initiated by:</span>
    <a href="{{ route('users.show', $ws->slug) }}" wire:navigate class="text-sm font-medium text-lime-600 hover:text-lime-700 hover:underline">
        {{ $ws->user->contact_person }}
    </a>
</div>
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
                                class="absolute -left-[9px] top-0 h-4 w-4 rounded-full bg-white border-2 border-lime-500 flex items-center justify-center text-[8px] font-black text-lime-600">
                                {{ $loop->iteration }}
                            </div>

                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="font-black text-slate-800 text-lg leading-none">
                                        {{ $ent->partner_name }}
                                    </h3>
                                    <div class="flex items-center gap-2 mt-2">
                                        <span
                                            class="text-[9px] bg-lime-50 text-lime-600 px-2 py-0.5 rounded font-black uppercase">
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
                                    <p class="text-xs text-lime-700 font-bold leading-relaxed">{{ $ent->way_forward }}
                                    </p>

                                    @if ($ent->last_edited_by_id && $ent->last_edited_by_id !== $viewingWorksheet->user_id)
                                        <p class="text-[9px] text-slate-400 italic mt-1">
                                            Last updated by <span
                                                class="font-bold">{{ $ent->lastEditor->contact_person }}</span>
                                        </p>
                                    @endif
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


    <flux:modal name="share-modal" class="md:w-[500px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Manage Sharing</flux:heading>
                <flux:subheading>Select colleagues to collaborate on <strong>{{ $sharingWorksheet?->name }}</strong>
                </flux:subheading>
            </div>

            {{-- Staff Search --}}
            <flux:input wire:model.live.debounce.300ms="staff_search" placeholder="Search staff by name..."
                icon="magnifying-glass" />

<div class="max-h-[350px] overflow-y-auto space-y-3 pr-2 custom-scrollbar">
    @foreach($available_staff as $staff)
        <div class="flex items-center justify-between p-4 rounded-2xl border border-slate-100 hover:bg-slate-50 transition-all group">
            <div class="flex items-center gap-4">
                {{-- Avatar Circle --}}
                <div class="h-10 w-10 rounded-full bg-lime-100 flex items-center justify-center text-sm font-black text-lime-700">
                    {{ strtoupper(substr($staff->contact_person, 0, 1)) }}
                </div>
                
                <div class="flex flex-col">
                    {{-- Contact Person Name --}}
                    <span class="text-sm font-black text-slate-900 leading-none">
                        {{ $staff->contact_person }}
                    </span>
                    
                    {{-- Email Address --}}
                    <span class="text-[11px] text-slate-500 mt-1 font-medium italic">
                        {{ $staff->email }}
                    </span>

                    {{-- Role Badge --}}
                    <span class="text-[9px] text-lime-500 mt-1 uppercase font-black tracking-wider bg-lime-50 px-1.5 py-0.5 rounded w-fit">
                        {{ $staff->roles->first()?->name ?? 'Staff' }}
                    </span>
                </div>
            </div>

            {{-- The Toggle --}}
            <flux:checkbox 
                wire:key="staff-{{ $staff->id }}-{{ count($selectedStaff) }}"
                :checked="in_array($staff->id, $selectedStaff)"
                wire:click="toggleShare({{ $staff->id }})" 
            />
        </div>
    @endforeach
</div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Finished</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>
