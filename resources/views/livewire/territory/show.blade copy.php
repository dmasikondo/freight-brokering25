<?php

use Livewire\Volt\Component;
use App\Models\Territory;

new class extends Component {
    public Territory $territory;

    public function mount(Territory $territory)
    {
        // Eager load users with roles to prevent N+1 queries
        $this->territory = $territory->load(['users.roles', 'countries', 'provinces', 'zimbabweCities']);
    }
}; ?>

<div class="p-8">
    {{-- 1. HEADER SECTION with Navigation --}}
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-6 bg-zinc-50 dark:bg-zinc-950 p-6 rounded-[2.5rem] border border-zinc-100 dark:border-zinc-800">
        <div class="flex items-center gap-4">
            <div class="size-12 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-lg">
                <flux:icon.map class="size-6 text-white" />
            </div>
            <div>
                <flux:heading size="xl" class="tracking-tighter font-black uppercase text-zinc-900 dark:text-white leading-none">
                    {{ $territory->name }}
                </flux:heading>
                <div class="mt-2 flex items-center gap-2">
                    <flux:button href="{{ route('territories.index') }}" variant="ghost" size="sm" icon="list-bullet" wire:navigate>All Territories</flux:button>
                    <span class="text-zinc-300">|</span>
                    <flux:button href="{{ route('territories.create') }}" variant="ghost" size="sm" icon="plus" wire:navigate>Create New</flux:button>
                </div>
            </div>
        </div>
        
        <flux:button href="{{ route('territories.edit', $territory) }}" icon="pencil-square" variant="primary" class="!rounded-xl" wire:navigate>
            Edit Jurisdiction
        </flux:button>
    </div>

    {{-- 2. GEOGRAPHIC SCOPE (Countries, Provinces, Cities) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        {{-- Countries --}}
        <div class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-[2rem] shadow-sm">
            <p class="text-[10px] font-black uppercase tracking-widest text-amber-600 mb-3">Regional Countries</p>
            <div class="flex flex-wrap gap-2">
                @forelse($territory->countries as $country)
                    <flux:badge color="amber" variant="subtle" class="rounded-lg">{{ $country->name }}</flux:badge>
                @empty
                    <span class="text-xs text-zinc-400 italic">Global/Local scope</span>
                @endforelse
            </div>
        </div>

        {{-- Provinces --}}
        <div class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-[2rem] shadow-sm">
            <p class="text-[10px] font-black uppercase tracking-widest text-indigo-600 mb-3">Full Provinces</p>
            <div class="flex flex-wrap gap-2">
                @forelse($territory->provinces as $province)
                    <flux:badge color="indigo" variant="subtle" class="rounded-lg">{{ $province->name }}</flux:badge>
                @empty
                    <span class="text-xs text-zinc-400 italic">No full provinces</span>
                @endforelse
            </div>
        </div>

        {{-- Cities --}}
        <div class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-[2rem] shadow-sm">
            <p class="text-[10px] font-black uppercase tracking-widest text-emerald-600 mb-3">Specific Cities</p>
            <div class="flex flex-wrap gap-2">
                @forelse($territory->zimbabweCities as $city)
                    <flux:badge color="emerald" variant="subtle" class="rounded-lg">{{ $city->name }}</flux:badge>
                @empty
                    <span class="text-xs text-zinc-400 italic">No specific cities</span>
                @endforelse
            </div>
        </div>
    </div>

    <hr class="border-zinc-100 dark:border-zinc-800 mb-10" />

    {{-- 3. ASSIGNED PERSONNEL GRID --}}
    <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <flux:icon.users variant="mini" class="size-5 text-zinc-400" />
            <flux:heading size="lg">Logistics Personnel ({{ $territory->users->count() }})</flux:heading>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @forelse ($territory->users as $user)
            <div class="relative" x-data="{ showContact: false }">
                {{-- Contact & Role Tooltip --}}
                <div 
                    x-show="showContact" 
                    x-transition x-cloak
                    class="absolute z-50 bottom-full left-0 mb-3 w-72 p-5 bg-zinc-900 text-white rounded-[1.5rem] shadow-2xl border border-zinc-800 pointer-events-none"
                >
                    <div class="space-y-3">
                        <div>
                            <p class="text-[9px] font-black text-indigo-400 uppercase tracking-widest mb-1">Assigned Role</p>
                            <div class="flex flex-wrap gap-1">
                                @foreach($user->roles as $role)
                                    <span class="text-[10px] font-bold bg-white/10 px-2 py-0.5 rounded-md border border-white/5">{{ $role->name }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="pt-2 border-t border-zinc-800 space-y-2 text-[11px]">
                            <p><span class="text-zinc-500 font-bold uppercase text-[9px]">Contact:</span> {{ $user->contact_person }}</p>
                            <p><span class="text-zinc-500 font-bold uppercase text-[9px]">Phone:</span> {{ $user->contact_phone }}</p>
                            <p><span class="text-emerald-500 font-bold uppercase text-[9px]">WhatsApp:</span> {{ $user->whatsapp ?? $user->contact_phone }}</p>
                            <p><span class="text-zinc-500 font-bold uppercase text-[9px]">Email:</span> {{ $user->email }}</p>
                        </div>
                    </div>
                    <div class="absolute -bottom-1 left-8 size-2 bg-zinc-900 rotate-45 border-r border-b border-zinc-800"></div>
                </div>

                {{-- User Card --}}
                <a 
                    href="{{ route('users.show', $user->slug) }}" 
                    wire:navigate
                    @mouseenter="showContact = true"
                    @mouseleave="showContact = false"
                    class="group block p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-[2.5rem] hover:border-indigo-500 hover:shadow-xl transition-all duration-300"
                >
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <p class="text-[10px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest">{{ $user->organisation }}</p>
                            <flux:icon.chevron-right variant="mini" class="size-4 text-zinc-300 group-hover:text-indigo-500 transition-colors" />
                        </div>
                        
                        <div>
                            <p class="text-sm font-black text-zinc-800 dark:text-zinc-100 leading-tight">{{ $user->contact_person }}</p>
                            {{-- Visible Role Badge on Listing --}}
                            <div class="mt-2 flex flex-wrap gap-1">
                                @foreach($user->roles->take(1) as $role)
                                    <span class="text-[9px] font-black uppercase px-2 py-0.5 bg-zinc-100 dark:bg-zinc-800 text-zinc-500 rounded-md">
                                        {{ $role->name }}
                                    </span>
                                @endforeach
                                @if($user->roles->count() > 1)
                                    <span class="text-[9px] font-black text-zinc-400">+{{ $user->roles->count() - 1 }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-span-full py-16 flex flex-col items-center justify-center border-2 border-dashed border-zinc-200 dark:border-zinc-800 rounded-[3rem]">
                <flux:icon.user-plus class="size-12 text-zinc-200 mb-4" />
                <p class="text-zinc-400 font-medium">No personnel assigned to this jurisdiction.</p>
            </div>
        @endforelse
    </div>
</div>