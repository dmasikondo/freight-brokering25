<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Territory;

new class extends Component {
    public User $user;
    public Territory $territory;
    public bool $isAssigned = false;

    public function mount()
    {
        $this->isAssigned = $this->user->userTerritoryAssignmentStatus($this->territory->name);
    }

    public function toggle()
    {
        if ($this->isAssigned) {
            $this->user->territories()->detach($this->territory->id);
            $this->isAssigned = false;
        } else {
            $this->user->territories()->attach($this->territory->id, [
                'assigned_by_user_id' => auth()->id(),
            ]);
            $this->isAssigned = true;
        }

        $this->dispatch('audit-updated');
    }
}; ?>

<div class="relative w-full" x-data="{ showTooltip: false }">
    {{-- THE HOVER TOOLTIP --}}
    <div 
        x-show="showTooltip" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-cloak
        {{-- pointer-events-none is CRITICAL so the button underneath still clicks --}}
        class="absolute z-50 bottom-full left-0 mb-4 w-72 p-5 bg-zinc-900 dark:bg-black rounded-3xl shadow-2xl border border-zinc-800 pointer-events-none"
    >
        <div class="space-y-4">
            {{-- 1. PROVINCES (The broad scope) --}}
            @if($territory->provinces->isNotEmpty())
                <div>
                    <p class="text-[8px] font-black text-indigo-400 uppercase tracking-widest mb-2">Provinces</p>
                    <div class="flex flex-wrap gap-1">
                        @foreach($territory->provinces as $province)
                            <span class="text-[10px] bg-white/10 text-white px-2 py-0.5 rounded-md border border-white/5">{{ $province->name }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- 2. CITIES (The granular scope) --}}
            @if($territory->zimbabweCities->isNotEmpty())
                <div class="{{ $territory->provinces->isNotEmpty() ? 'pt-3 border-t border-zinc-800' : '' }}">
                    <p class="text-[8px] font-black text-emerald-400 uppercase tracking-widest mb-2">Major Cities</p>
                    <div class="flex flex-wrap gap-1">
                        @foreach($territory->zimbabweCities as $city)
                            <span class="text-[10px] bg-zinc-800 text-zinc-300 px-2 py-0.5 rounded-md">{{ $city->name }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- 3. COUNTRIES (The international scope) --}}
            @if($territory->countries->isNotEmpty())
                <div class="pt-3 border-t border-zinc-800">
                    <p class="text-[8px] font-black text-amber-400 uppercase tracking-widest mb-1">Regional Countries</p>
                    <p class="text-[10px] text-zinc-400 italic">
                        {{ $territory->countries->pluck('name')->join(', ') }}
                    </p>
                </div>
            @endif

            {{-- FALLBACK --}}
            @if($territory->provinces->isEmpty() && $territory->zimbabweCities->isEmpty() && $territory->countries->isEmpty())
                <p class="text-[10px] italic text-zinc-600">No jurisdiction data defined.</p>
            @endif
        </div>

        {{-- Tooltip Pointer --}}
        <div class="absolute -bottom-1 left-8 size-2 bg-zinc-900 dark:bg-black rotate-45 border-r border-b border-zinc-800"></div>
    </div>

    {{-- THE TOGGLE BUTTON --}}
    <button 
        type="button"
        wire:click="toggle"
        @mouseenter="showTooltip = true"
        @mouseleave="showTooltip = false"
        @class([
            'group flex items-center justify-between w-full p-5 rounded-[2rem] border transition-all duration-300',
            'bg-emerald-50 border-emerald-200 dark:bg-emerald-950/20 dark:border-emerald-800/50' => $isAssigned,
            'bg-white border-zinc-200 dark:bg-zinc-900 dark:border-zinc-800 hover:border-indigo-400 shadow-sm hover:shadow-md' => !$isAssigned
        ])
    >
        <div class="flex flex-col text-left">
            <span @class([
                'text-[12px] font-black uppercase tracking-tight',
                'text-emerald-700 dark:text-emerald-400' => $isAssigned,
                'text-zinc-600 dark:text-zinc-300' => !$isAssigned
            ])>
                {{ $territory->name }}
            </span>
            
            {{-- Visual Summary Subtitle --}}
            <div class="mt-2 flex items-center gap-2">
                <span class="text-[9px] font-bold text-zinc-400 uppercase">
                    {{ $territory->provinces->count() }} Prov. | {{ $territory->zimbabweCities->count() }} Cities
                </span>
                @if($territory->countries->count() > 0)
                    <span class="size-1 bg-amber-500 rounded-full"></span>
                    <span class="text-[9px] font-black text-amber-600 uppercase">Cross-Border</span>
                @endif
            </div>
        </div>

        {{-- Icon Indicator --}}
        <div @class([
            'size-8 rounded-2xl flex items-center justify-center transition-all',
            'bg-emerald-500 text-white' => $isAssigned,
            'bg-zinc-100 dark:bg-zinc-800 text-zinc-400 group-hover:bg-indigo-500 group-hover:text-white' => !$isAssigned
        ])>
            @if($isAssigned)
                <flux:icon.check variant="mini" class="size-4" />
            @else
                <flux:icon.plus variant="mini" class="size-4" />
            @endif
        </div>
    </button>
</div>