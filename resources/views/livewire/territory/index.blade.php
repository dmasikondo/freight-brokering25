<?php

use Livewire\Volt\Component;
use App\Models\Territory;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

new class extends Component {
    use WithPagination;

    #[Url]
    public $search = '';

    #[Url]
    public $sort = 'latest';

    /**
     * Reset pagination when search or sort changes to ensure 
     * results are visible starting from page 1.
     */
    public function updatedSearch() { $this->resetPage(); }
    public function updatedSort() { $this->resetPage(); }

    /**
     * Delete a territory and provide feedback.
     */
    public function deleteTerritory($id)
    {
        $territory = Territory::findOrFail($id);
        $territory->delete();
        session()->flash('message', "Territory '{$territory->name}' successfully removed.");
    }

    /**
     * Fetch territories based on search and sort filters.
     */
    public function getTerritories()
    {
        $query = Territory::where('name', 'like', '%' . $this->search . '%')
            ->with(['users', 'countries', 'provinces', 'zimbabweCities']);

        $query = match ($this->sort) {
            'name_asc'  => $query->orderBy('name', 'asc'),
            'name_desc' => $query->orderBy('name', 'desc'),
            'oldest'    => $query->orderBy('created_at', 'asc'),
            default     => $query->latest(),
        };

        return $query->paginate(6);
    }

    public function mount()
    {
        $this->authorize('viewAny', Territory::class);
    }
}; ?>

<div class="space-y-6">
    {{-- 1. HEADER, SEARCH & SORT CONTROLS --}}
    <div class="bg-white dark:bg-zinc-900 p-8 rounded-[2.5rem] border border-zinc-100 dark:border-zinc-800 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <flux:heading size="xl" class="tracking-tighter font-black uppercase text-zinc-800 dark:text-white">
                    Territory Jurisdictions
                </flux:heading>
                <div class="mt-1 flex items-center gap-2 text-zinc-500">
                    <flux:icon.globe-alt variant="mini" class="size-4 text-lime-600" />
                    <flux:subheading class="font-medium">Managing operational zones across your logistics network</flux:subheading>
                </div>
            </div>

            <div class="flex items-center gap-3">
                {{-- Search Input --}}
                <flux:input 
                    wire:model.live.debounce.400ms="search" 
                    type="search" 
                    placeholder="Search jurisdictions..." 
                    icon="magnifying-glass"
                    class="!rounded-2xl min-w-[250px] shadow-sm"
                />

                {{-- Sort Select --}}
                <flux:select wire:model.live="sort" class="!rounded-2xl min-w-[160px] shadow-sm">
                    <option value="latest">Latest First</option>
                    <option value="oldest">Oldest First</option>
                    <option value="name_asc">Name (A-Z)</option>
                    <option value="name_desc">Name (Z-A)</option>
                </flux:select>

                @if (Route::has('territories.create'))
                    <flux:button icon="plus" variant="primary" href="{{ route('territories.create') }}" wire:navigate class="!rounded-2xl shadow-lg shadow-lime-500/20">
                        New
                    </flux:button>
                @endif
            </div>
        </div>
    </div>

    {{-- 2. LOADING SKELETON (Visible during Livewire requests) --}}
    <div wire:loading.flex class="flex-col gap-4">
        @for ($i = 0; $i < 3; $i++)
            <div class="h-64 w-full bg-zinc-100 dark:bg-zinc-800 animate-pulse rounded-[2.5rem] border border-zinc-200 dark:border-zinc-700"></div>
        @endfor
    </div>

    {{-- 3. CONTENT AREA (Hidden during Livewire requests) --}}
    <div wire:loading.remove>
        @php $territories = $this->getTerritories() @endphp

        @if (session()->has('message'))
            <flux:callout icon="check" color="green" class="mb-6 !rounded-2xl font-bold uppercase text-[10px] tracking-widest">
                {{ session('message') }}
            </flux:callout>
        @endif

        @if ($territories->isEmpty())
            <div class="flex flex-col items-center justify-center py-24 bg-zinc-50 dark:bg-zinc-950 rounded-[3rem] border-2 border-dashed border-zinc-200 dark:border-zinc-800 text-center">
                <flux:icon.map-pin class="size-12 text-zinc-300 mb-4" />
                <flux:heading>No territories found</flux:heading>
                <flux:subheading>Try adjusting your filters or create a new zone.</flux:subheading>
            </div>
        @else
            <div class="grid grid-cols-1 gap-4">
                @foreach ($territories as $territory)
                    <div class="group relative bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-8 rounded-[2.5rem] transition-all hover:border-lime-500/40 hover:shadow-xl hover:shadow-zinc-500/5" wire:key="t-{{ $territory->id }}">
                        
                        {{-- Dropdown Actions --}}
                        <div class="absolute top-8 right-8 z-10">
                            <flux:dropdown position="bottom" align="end">
                                <flux:button variant="ghost" icon="ellipsis-horizontal" size="sm" class="rounded-xl" />
                                <flux:menu>
                                    <flux:menu.item href="{{ route('territories.edit', $territory->id) }}" icon="pencil-square">Edit Territory</flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item icon="trash" variant="danger" 
                                        wire:click="deleteTerritory('{{ $territory->id }}')"
                                        wire:confirm.prompt="Permanently remove {{ strtoupper($territory->name) }}?|REMOVE">
                                        Delete
                                    </flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </div>

                        <div class="flex flex-col lg:flex-row lg:items-start gap-8">
                            {{-- COLUMN 1: IDENTITY & CTA --}}
                            <div class="lg:w-1/4 space-y-4">
                                <div>
                                    <h2 class="text-3xl font-black tracking-tighter text-zinc-900 dark:text-white leading-none italic">
                                        {{ $territory->name }}
                                    </h2>
                                    <p class="text-[10px] font-black text-zinc-400 uppercase tracking-[0.2em] mt-2">
                                        {{ $territory->created_at->format('M d, Y') }}
                                    </p>
                                </div>

                                <a href="{{ route('territories.show', $territory->id) }}" wire:navigate 
                                    class="inline-flex items-center gap-3 px-5 py-2.5 bg-lime-50 dark:bg-lime-900/20 text-lime-700 dark:text-lime-400 text-[11px] font-black uppercase tracking-widest rounded-2xl hover:bg-lime-600 hover:text-white transition-all group/btn shadow-sm">
                                    Intelligence Report
                                    <flux:icon.arrow-right variant="mini" class="size-3.5 group-hover/btn:translate-x-1 transition-transform" />
                                </a>
                            </div>

                            {{-- COLUMN 2: GEOGRAPHIC SCOPE --}}
                            <div class="lg:w-2/4 space-y-5">
                                <div class="flex flex-wrap gap-2">
                                    @foreach($territory->countries as $country)
                                        <flux:badge color="lime" variant="pill" size="sm" class="font-black uppercase tracking-tighter italic">
                                            {{ $country->name }}
                                        </flux:badge>
                                    @endforeach
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    {{-- Provinces --}}
                                    <div class="flex items-start gap-3">
                                        <flux:icon.map class="size-4 mt-1 text-zinc-400" />
                                        <div>
                                            <p class="text-[10px] font-black uppercase tracking-widest text-zinc-400">Provinces</p>
                                            <p class="text-xs text-zinc-700 dark:text-zinc-300 font-medium leading-relaxed">
                                                {{ $territory->provinces->isNotEmpty() ? $territory->provinces->pluck('name')->implode(', ') : 'Global scope' }}
                                            </p>
                                        </div>
                                    </div>

                                    {{-- Cities --}}
                                    <div class="flex items-start gap-3">
                                        <flux:icon.building-office-2 class="size-4 mt-1 text-zinc-400" />
                                        <div>
                                            <p class="text-[10px] font-black uppercase tracking-widest text-zinc-400">Towns/Cities</p>
                                            <p class="text-xs text-zinc-700 dark:text-zinc-300 font-medium leading-relaxed">
                                                {{ $territory->zimbabweCities->isNotEmpty() ? $territory->zimbabweCities->pluck('name')->implode(', ') : 'Regional scope' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- COLUMN 3: STAFF ASSIGNMENT --}}
                            <div class="lg:w-1/4 flex flex-col items-end border-t lg:border-t-0 lg:border-l border-zinc-100 dark:border-zinc-800 pt-6 lg:pt-0 lg:pl-10">
                                <div class="flex -space-x-3 mb-4">
                                    @foreach ($territory->users->take(4) as $user)
                                        <div class="size-11 rounded-full border-4 border-white dark:border-zinc-900 bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center overflow-hidden shadow-sm" title="{{ $user->contact_person }}">
                                            <span class="text-[11px] font-black text-zinc-500 uppercase">{{ substr($user->contact_person, 0, 1) }}</span>
                                        </div>
                                    @endforeach
                                    @if($territory->users->count() > 4)
                                        <div class="size-11 rounded-full border-4 border-white dark:border-zinc-900 bg-lime-500 flex items-center justify-center shadow-sm">
                                            <span class="text-[10px] font-black text-white">+{{ $territory->users->count() - 4 }}</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="text-right">
                                    @php $userCount = $territory->users->count(); @endphp
                                    <p class="text-xs font-black text-zinc-800 dark:text-zinc-200 uppercase tracking-tighter leading-none">
                                        {{ $userCount }} {{ Str::plural('Personnel', $userCount) }}
                                    </p>
                                    
                                    @if($userCount === 1)
                                        <p class="text-[10px] text-lime-600 dark:text-lime-400 mt-2 font-bold uppercase italic">
                                            {{ $territory->users->first()->contact_person }}
                                        </p>
                                    @elseif($userCount > 1)
                                        <p class="text-[10px] text-zinc-400 mt-2 font-medium">
                                            including <span class="text-lime-600 dark:text-lime-400 font-bold uppercase italic">{{ $territory->users->first()->contact_person }}</span>
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- 4. PAGINATION FOOTER --}}
            <div class="mt-10 px-4">
                {{ $territories->links() }}
            </div>
        @endif
    </div>
</div>