<x-layouts.app :title="$user->contact_person . ' - Profile'">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 md:p-8" x-data="{
        activeTab: 'activity',
        isCarrier: {{ $user->hasRole('carrier') ? 'true' : 'false' }},
    }">

        <div class="bg-white dark:bg-zinc-900 rounded-[2.5rem] shadow-2xl overflow-hidden border border-zinc-200 dark:border-zinc-800 flex flex-col lg:flex-row min-h-[850px]">

            {{-- SIDEBAR: Identity & Status --}}
            <div class="lg:w-96 bg-zinc-50 dark:bg-zinc-950 p-10 border-r border-zinc-100 dark:border-zinc-800 flex flex-col items-center text-center">
                <div class="relative group">
                    <div class="w-48 h-48 rounded-[3.5rem] {{ $theme['bg'] ?? 'bg-zinc-200' }} {{ $theme['text'] ?? 'text-zinc-500' }} flex items-center justify-center shadow-2xl transition-all duration-700 group-hover:scale-105 group-hover:-rotate-3 overflow-hidden border-4 border-white dark:border-zinc-800">
                        <flux:icon :name="$theme['icon'] ?? 'user'" class="size-28 opacity-90" />
                    </div>
                </div>

                <div class="mt-8 space-y-3 w-full">
                    <h1 class="text-3xl font-black text-zinc-900 dark:text-white tracking-tight leading-none">
                        {{ $user->contact_person }}
                    </h1>

                    {{-- Classification: Owner/Broker --}}
                    <div class="flex flex-wrap justify-center gap-2">
                        @foreach ($user->roles as $role)
                            <div class="flex flex-col items-center">
                                <span class="px-3 py-1 rounded-lg bg-zinc-900 text-white text-[9px] font-black uppercase tracking-widest shadow-md">
                                    {{ $role->name }}
                                </span>
                                @if (in_array(strtolower($role->name), ['carrier', 'shipper']) && $role->pivot->classification)
                                    <span class="mt-1 text-[8px] font-bold uppercase tracking-tighter {{ $role->pivot->classification === 'real_owner' ? 'text-amber-600' : 'text-sky-600' }}">
                                        {{ str_replace('_', ' ', $role->pivot->classification) }}
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- Suspension Identity --}}
                    @if($user->isSuspended())
                        <div class="mt-6 p-4 rounded-3xl bg-rose-50 dark:bg-rose-950/30 border border-rose-100 dark:border-rose-900/50">
                            <div class="flex items-center justify-center gap-2 mb-2">
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
                                </span>
                                <span class="text-[10px] font-black text-rose-600 uppercase tracking-widest">Node Suspended</span>
                            </div>
                            <p class="text-[9px] font-bold text-zinc-500 dark:text-zinc-400 leading-tight">
                                By: <span class="text-rose-600 font-black">{{ $user->updater?->contact_person ?? 'System' }}</span><br>
                                on {{ $user->updated_at->format('d M Y') }}
                            </p>
                        </div>
                    @endif

                    {{-- Registration Source --}}
                    <div class="mt-6 pt-6 border-t border-zinc-200 dark:border-zinc-800 w-full text-left space-y-4">
                        <div>
                            <p class="text-[8px] font-black text-zinc-400 uppercase tracking-[0.2em]">Registration Method</p>
                            <p class="text-[11px] font-bold text-zinc-700 dark:text-zinc-300">
                                @if($user->createdBy)
                                    Via Agent: <span class="text-indigo-500">{{ $user->createdBy->contact_person }}</span>
                                @else
                                    <span class="text-emerald-500 uppercase tracking-tighter">Self-Registered</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Sidebar Actions --}}
                <div class="mt-auto pt-10 w-full space-y-4">
                    @can('suspend', $user)
                        @if ($user->isSuspended())
                            <form action="{{ route('users.unsuspend', $user) }}" method="POST">
                                @csrf @method('PATCH')
                                <flux:button type="submit" variant="primary" color="indigo" class="w-full !rounded-[1.5rem] !py-4 font-black uppercase text-xs">Restore Node</flux:button>
                            </form>
                        @else
                            <flux:modal.trigger name="suspend_user_modal">
                                <flux:button variant="primary" color="rose" class="w-full !rounded-[1.5rem] !py-4 font-black uppercase text-xs">Suspend Account</flux:button>
                            </flux:modal.trigger>
                        @endif
                    @endcan
                </div>
            </div>

            {{-- MAIN CONTENT --}}
            <div class="flex-1 p-8 lg:p-12 flex flex-col">
                
                {{-- TAB NAV --}}
                <div class="flex items-center gap-3 mb-12 bg-zinc-100 dark:bg-zinc-800 p-1.5 rounded-[2rem] w-fit border border-zinc-200 dark:border-zinc-700 overflow-x-auto">
                    <button @click="activeTab = 'activity'" :class="activeTab === 'activity' ? 'bg-white dark:bg-zinc-700 text-indigo-600 shadow-lg' : 'text-zinc-500'" class="px-8 py-3 rounded-[1.6rem] text-sm font-black transition-all">Activity Hub</button>
                    <button @click="activeTab = 'contact'" :class="activeTab === 'contact' ? 'bg-white dark:bg-zinc-700 text-indigo-600 shadow-lg' : 'text-zinc-500'" class="px-8 py-3 rounded-[1.6rem] text-sm font-black transition-all">Profile Metadata</button>
                    
                    @if($user->hasRole('carrier'))
                        <button @click="activeTab = 'logistics'" :class="activeTab === 'logistics' ? 'bg-white dark:bg-zinc-700 text-indigo-600 shadow-lg' : 'text-zinc-500'" class="px-8 py-3 rounded-[1.6rem] text-sm font-black transition-all">Logistics Network</button>
                        <button @click="activeTab = 'regulatory'" :class="activeTab === 'regulatory' ? 'bg-white dark:bg-zinc-700 text-indigo-600 shadow-lg' : 'text-zinc-500'" class="px-8 py-3 rounded-[1.6rem] text-sm font-black transition-all">Regulatory Vault</button>
                    @endif
                </div>

                {{-- TAB: LOGISTICS NETWORK --}}
                @if($user->hasRole('carrier'))
                <div x-show="activeTab === 'logistics'" x-transition class="space-y-10">
                    
                    {{-- METRICS COMMAND CENTER --}}
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="bg-zinc-900 text-white p-6 rounded-[2rem] shadow-xl">
                            <p class="text-[9px] font-black uppercase tracking-widest text-zinc-400">Total Fleet Lanes</p>
                            <p class="text-3xl font-black mt-2">{{ $user->lanes->count() }}</p>
                        </div>
                        {{-- Enum LaneStatus Metrics --}}
                        <div class="bg-white dark:bg-zinc-800 p-6 rounded-[2rem] border border-zinc-100 dark:border-zinc-700">
                            <p class="text-[9px] font-black uppercase tracking-widest text-teal-500">Published</p>
                            <p class="text-2xl font-black mt-2">{{ $user->lanes->where('status', \App\Enums\LaneStatus::PUBLISHED)->count() }}</p>
                        </div>
                        {{-- Enum VehicleStatus Metrics --}}
                        <div class="bg-white dark:bg-zinc-800 p-6 rounded-[2rem] border border-zinc-100 dark:border-zinc-700">
                            <p class="text-[9px] font-black uppercase tracking-widest text-indigo-500">In-Transit</p>
                            <p class="text-2xl font-black mt-2">{{ $user->lanes->where('vehicle_status', \App\Enums\VehiclePositionStatus::INTRANSIT)->count() }}</p>
                        </div>
                        <div class="bg-white dark:bg-zinc-800 p-6 rounded-[2rem] border border-zinc-100 dark:border-zinc-700">
                            <p class="text-[9px] font-black uppercase tracking-widest text-emerald-500">Ready/Loading</p>
                            <p class="text-2xl font-black mt-2">
                                {{ $user->lanes->whereIn('vehicle_status', [\App\Enums\VehiclePositionStatus::NOT_CONTRACTED, \App\Enums\VehiclePositionStatus::LOADING])->count() }}
                            </p>
                        </div>
                    </div>

                    {{-- DETAILED LANE LIST --}}
                    <div class="space-y-4">
                        <h3 class="text-[10px] font-black text-zinc-400 uppercase tracking-[0.3em] ml-4">Route Registry</h3>
                        @forelse($user->lanes as $lane)
                            <div class="bg-white dark:bg-zinc-950 p-6 rounded-[2.5rem] border border-zinc-100 dark:border-zinc-800 flex flex-wrap lg:flex-nowrap items-center gap-8 group hover:shadow-xl transition-all duration-500">
                                
                                {{-- Route --}}
                                <div class="flex-1 min-w-[200px]">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-[10px] font-black text-zinc-400 uppercase tracking-tighter">{{ $lane->cityfrom }}</span>
                                        <flux:icon.arrow-right class="size-3 text-zinc-300" />
                                        <span class="text-[10px] font-black text-zinc-400 uppercase tracking-tighter">{{ $lane->cityto }}</span>
                                    </div>
                                    <p class="text-xl font-black tracking-tight group-hover:text-indigo-600 transition-colors">
                                        {{ $lane->countryfrom }} <span class="text-zinc-300">→</span> {{ $lane->countryto }}
                                    </p>
                                </div>

                                {{-- Asset Details --}}
                                <div class="px-8 border-x border-zinc-100 dark:border-zinc-800">
                                    <p class="text-[8px] font-black text-zinc-400 uppercase tracking-widest mb-2 text-center">Equipment</p>
                                    <div class="flex flex-col items-center">
                                        <span class="text-xs font-black uppercase tracking-tighter">{{ $lane->trailer->label() }}</span>
                                        <span class="text-lg font-black text-indigo-500 leading-none">{{ $lane->capacity }}<span class="text-[10px] ml-0.5">T</span></span>
                                    </div>
                                </div>

                                {{-- Availability Date --}}
                                <div class="min-w-[100px] text-center">
                                    <p class="text-[8px] font-black text-zinc-400 uppercase tracking-widest mb-2">Available On</p>
                                    <p class="text-sm font-black uppercase tracking-tighter">
                                        {{ $lane->availability_date->format('d M Y') }}
                                    </p>
                                </div>

                                {{-- Status Badges (Enum Based) --}}
                                <div class="flex flex-col gap-2 min-w-[120px]">
                                    {{-- Lane Publication Status --}}
                                    <div class="flex items-center gap-2 px-3 py-1 rounded-full bg-{{ $lane->status->color() }}-50 dark:bg-{{ $lane->status->color() }}-950/30 border border-{{ $lane->status->color() }}-100 dark:border-{{ $lane->status->color() }}-900">
                                        <div class="size-1.5 rounded-full bg-{{ $lane->status->color() }}-500"></div>
                                        <span class="text-[9px] font-black text-{{ $lane->status->color() }}-600 uppercase">{{ $lane->status->label() }}</span>
                                    </div>
                                    {{-- Vehicle Movement Status --}}
                                    <div class="px-3 py-1 rounded-full bg-zinc-100 dark:bg-zinc-800 text-center">
                                        <span class="text-[8px] font-bold text-zinc-500 uppercase">{{ $lane->vehicle_status->label() }}</span>
                                    </div>
                                </div>

                                <a href="{{ route('lanes.show', $lane) }}" class="p-4 bg-zinc-50 dark:bg-zinc-900 rounded-2xl hover:bg-indigo-600 hover:text-white transition-all">
                                    <flux:icon.chevron-right class="size-5" />
                                </a>
                            </div>
                        @empty
                            <div class="py-20 text-center opacity-40 uppercase font-black text-xs tracking-[0.5em]">No Lanes Postings Detected</div>
                        @endforelse
                    </div>
                </div>
                @endif

                {{-- TAB: PROFILE METADATA --}}
                <div x-show="activeTab === 'contact'" x-transition class="grid grid-cols-1 xl:grid-cols-2 gap-10">
                    {{-- Connectivity & Presence Logic Here --}}
                    <div class="bg-zinc-50 dark:bg-zinc-950 p-12 rounded-[3.5rem] border border-zinc-100 dark:border-zinc-800 space-y-10">
                        <h3 class="text-[10px] font-black text-zinc-400 uppercase tracking-[0.4em]">Node Connectivity</h3>
                        <div class="space-y-8">
                            <div class="flex items-center gap-6">
                                <div class="size-14 bg-white dark:bg-zinc-800 rounded-3xl flex items-center justify-center shadow-sm"><flux:icon.envelope class="size-6 text-indigo-500" /></div>
                                <div><p class="text-[10px] font-black text-indigo-500 uppercase">Email Protocol</p><p class="text-xl font-bold">{{ $user->email }}</p></div>
                            </div>
                            <div class="flex items-center gap-6">
                                <div class="size-14 bg-white dark:bg-zinc-800 rounded-3xl flex items-center justify-center shadow-sm"><flux:icon.phone class="size-6 text-emerald-500" /></div>
                                <div><p class="text-[10px] font-black text-emerald-500 uppercase">Terminal Link</p><p class="text-xl font-bold">{{ $user->contact_phone }}</p></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- OTHER TABS (Activity, Regulatory, Audit) ... --}}
            </div>
        </div>
    </div>
    @include('includes.suspend-user-modal')
</x-layouts.app>