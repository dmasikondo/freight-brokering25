<x-layouts.app :title="$user->contact_person . ' - Profile'">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 md:p-8" 
        x-data="{ 
            activeTab: 'activity',
            isShipper: {{ $isShipper ? 'true' : 'false' }},
            isCarrier: {{ $isCarrier ? 'true' : 'false' }},
            isLeadRole: {{ $isLeadRole ? 'true' : 'false' }},
            isAdmin: {{ $isAdmin ? 'true' : 'false' }}
        }">
        
        <!-- Profile Intelligence Hub -->
        <div class="bg-white dark:bg-zinc-900 rounded-[2.5rem] shadow-2xl overflow-hidden border border-zinc-200 dark:border-zinc-800 flex flex-col lg:flex-row min-h-[750px]">
            
            <!-- Sidebar: Identity & Provenance (Themed by Controller) -->
            <div class="lg:w-96 bg-zinc-50 dark:bg-zinc-950 p-10 border-r border-zinc-100 dark:border-zinc-800 flex flex-col items-center text-center">
                <div class="relative group">
                    <div class="w-48 h-48 rounded-[3.5rem] {{ $theme['bg'] }} {{ $theme['text'] }} flex items-center justify-center shadow-2xl transition-all duration-700 group-hover:scale-105 group-hover:-rotate-3 overflow-hidden border-4 border-white dark:border-zinc-800">
                        <flux:icon :name="$theme['icon']" class="size-28 opacity-90" />
                    </div>
                    
                    @if($user->roles->first()?->pivot?->classification === 'real_owner')
                        <div class="absolute -bottom-2 -right-2 bg-white dark:bg-zinc-900 p-2.5 rounded-2xl shadow-xl border border-zinc-100 dark:border-zinc-800">
                            <flux:icon.shield-check class="size-7 text-amber-500" />
                        </div>
                    @endif
                </div>

                <div class="mt-8 space-y-3">
                    <div class="flex items-center justify-center gap-3">
                        <h1 class="text-3xl font-black text-zinc-900 dark:text-white tracking-tight leading-none">
                            {{ $user->contact_person }}
                        </h1>
                        @if(auth()->id() === $user->id)
                            <span class="bg-indigo-600 text-white text-[9px] font-black px-2.5 py-1 rounded-full uppercase tracking-widest shadow-lg shadow-indigo-200">Me</span>
                        @endif
                    </div>
                    
                    @if($user->organisation)
                        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                            <flux:icon.building-office-2 class="size-3.5 text-zinc-400" />
                            <span class="text-[10px] font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-widest">{{ $user->organisation }}</span>
                        </div>
                    @endif

                    <div class="mt-4 flex flex-col items-center gap-2">
                        <div class="font-mono text-sm bg-zinc-900 text-white px-6 py-2.5 rounded-2xl shadow-xl border border-zinc-700 tracking-[0.2em]">
                            {{ $user->identification_number }}
                        </div>
                    </div>
                </div>

                <!-- Registration Provenance -->
                <div class="mt-10 w-full p-5 rounded-[2rem] bg-zinc-100/50 dark:bg-zinc-900/50 border border-dashed border-zinc-200 dark:border-zinc-800">
                    @if($user->createdBy)
                        <div class="flex flex-col items-center">
                            <span class="text-[9px] font-black text-zinc-400 uppercase tracking-[0.3em] mb-4">Registration Lifecycle</span>
                            <flux:link href="{{ route('users.show', $user->createdBy) }}" wire:navigate class="flex items-center gap-3 group/creator">
                                <div class="size-10 rounded-xl bg-white dark:bg-zinc-800 shadow-sm flex items-center justify-center border border-zinc-100 dark:border-zinc-700 group-hover/creator:border-indigo-300 transition-all">
                                    <flux:icon.user-plus class="size-5 text-zinc-400" />
                                </div>
                                <div class="text-left">
                                    <p class="text-sm font-black text-zinc-800 dark:text-white leading-tight truncate max-w-[140px]">{{ $user->createdBy->contact_person }}</p>
                                    <p class="text-[9px] font-bold text-zinc-400 uppercase tracking-tighter">Verified Creator</p>
                                </div>
                            </flux:link>
                        </div>
                    @else
                        <div class="flex flex-col items-center gap-2">
                            <div class="size-12 rounded-full bg-emerald-50 dark:bg-emerald-950 flex items-center justify-center border border-emerald-100 dark:emerald-900">
                                <flux:icon.bolt class="size-6 text-emerald-600" />
                            </div>
                            <div class="text-center">
                                <p class="text-xs font-black text-emerald-600 uppercase tracking-widest">Self-Registered</p>
                                <p class="text-[9px] text-zinc-400 font-bold mt-1 uppercase">Direct Enrollment</p>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="mt-auto pt-8 w-full">
                    @can('update', $user)
                        <flux:button variant="primary" color="indigo" class="w-full !rounded-[1.5rem] !py-4 font-black shadow-lg shadow-indigo-100 transition-all hover:scale-[1.02]">
                            Modify Identity Hub
                        </flux:button>
                    @endcan
                </div>
            </div>

            <!-- Content Area -->
            <div class="flex-1 p-8 lg:p-12 flex flex-col">
                <div class="flex items-center gap-3 mb-12 bg-zinc-100 dark:bg-zinc-800 p-1.5 rounded-[2rem] w-fit border border-zinc-200 dark:border-zinc-700 overflow-x-auto custom-scrollbar">
                    <button @click="activeTab = 'activity'" :class="activeTab === 'activity' ? 'bg-white dark:bg-zinc-700 text-indigo-600 shadow-lg' : 'text-zinc-500 hover:text-zinc-700'" class="px-8 py-3 rounded-[1.6rem] text-sm font-black transition-all whitespace-nowrap">Activity Hub</button>
                    <button @click="activeTab = 'contact'" :class="activeTab === 'contact' ? 'bg-white dark:bg-zinc-700 text-indigo-600 shadow-lg' : 'text-zinc-500 hover:text-zinc-700'" class="px-8 py-3 rounded-[1.6rem] text-sm font-black transition-all whitespace-nowrap">Profile Metadata</button>
                    <template x-if="isLeadRole">
                        <button @click="activeTab = 'territory'" :class="activeTab === 'territory' ? 'bg-white dark:bg-zinc-700 text-indigo-600 shadow-lg' : 'text-zinc-500 hover:text-zinc-700'" class="px-8 py-3 rounded-[1.6rem] text-sm font-black transition-all whitespace-nowrap">Territories</button>
                    </template>
                    <template x-if="isAdmin">
                        <button @click="activeTab = 'audit'" :class="activeTab === 'audit' ? 'bg-white dark:bg-zinc-700 text-indigo-600 shadow-lg' : 'text-zinc-500 hover:text-zinc-700'" class="px-8 py-3 rounded-[1.6rem] text-sm font-black transition-all whitespace-nowrap">Audit Ledger</button>
                    </template>
                </div>

                <!-- Tab: Activity Hub -->
                <div x-show="activeTab === 'activity'" class="space-y-12 animate-fade-in" x-transition>
                    <template x-if="isShipper">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                            <div class="lg:col-span-8 space-y-10">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    <livewire:shipper.shipment-status :user="$user" />
                                    <livewire:shipper.freight-status :user="$user" />
                                </div>

                                <div class="bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 rounded-[2.5rem] p-10 shadow-sm">
                                    <div class="flex items-center justify-between mb-8">
                                        <div class="flex items-center gap-4">
                                            <div class="size-12 bg-indigo-50 dark:bg-indigo-950 rounded-2xl flex items-center justify-center"><flux:icon.archive-box class="size-6 text-indigo-600" /></div>
                                            <h3 class="text-xl font-black text-zinc-900 dark:text-white">Freight Registry</h3>
                                        </div>
                                        <flux:button href="{{ route('freights.create') }}" variant="subtle" size="sm" icon="plus" class="!rounded-xl font-black text-[10px]">New Order</flux:button>
                                    </div>

                                    <div class="grid grid-cols-1 gap-4">
                                        @forelse($user->freights->take(5) as $freight)
                                            <flux:link href="#" class="group flex items-center justify-between p-5 bg-zinc-50 dark:bg-zinc-950 border border-zinc-100 dark:border-zinc-800 rounded-[2rem] hover:border-indigo-200">
                                                <div class="flex items-center gap-5">
                                                    <div class="size-12 rounded-[1.2rem] bg-white dark:bg-zinc-800 flex items-center justify-center group-hover:bg-indigo-50 border border-zinc-100 dark:border-zinc-800 shadow-sm">
                                                        <flux:icon.truck class="size-6 text-zinc-300 group-hover:text-indigo-500" />
                                                    </div>
                                                    <p class="text-sm font-black text-zinc-800 dark:text-white truncate">REF: {{ $freight->ref_number ?? '#' . $freight->id }}</p>
                                                </div>
                                                <span class="px-3 py-1 bg-zinc-100 text-zinc-600 text-[9px] font-black rounded-full uppercase tracking-widest border">{{ $freight->status->label() }}</span>
                                            </flux:link>
                                        @empty
                                            <div class="py-20 text-center text-xs text-zinc-400 font-black uppercase">Registry Empty</div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                            <div class="lg:col-span-4 space-y-6">
                                <div class="bg-indigo-600 rounded-[2.5rem] p-10 text-white shadow-2xl relative overflow-hidden">
                                    <flux:icon.cursor-arrow-ripple class="absolute -bottom-10 -right-10 size-40 opacity-10" />
                                    <h3 class="text-xs font-black uppercase tracking-[0.3em] opacity-80 mb-8">Rapid Execution</h3>
                                    <div class="space-y-4 relative z-10">
                                        <flux:button href="{{ route('freights.create') }}" variant="primary" color="white" icon="plus" class="w-full !text-indigo-600 !font-black !rounded-2xl !py-4 shadow-xl">Post Shipment</flux:button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Tab: Profile Metadata (Restored) -->
                <div x-show="activeTab === 'contact'" class="animate-fade-in grid grid-cols-1 xl:grid-cols-2 gap-10" x-transition>
                    <div class="bg-zinc-50 dark:bg-zinc-950 p-12 rounded-[3.5rem] border border-zinc-100 dark:border-zinc-800 space-y-12">
                        <h3 class="text-[10px] font-black text-zinc-400 uppercase tracking-[0.4em]">Node Connectivity</h3>
                        <div class="space-y-10">
                            <div class="flex items-start gap-8 group">
                                <div class="size-14 bg-white dark:bg-zinc-800 rounded-3xl flex items-center justify-center shadow-sm border border-zinc-100 dark:border-zinc-700">
                                    <flux:icon.envelope class="size-7 text-indigo-500" />
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-indigo-500 uppercase tracking-widest">Protocol Email</p>
                                    <p class="text-xl font-bold text-zinc-900 dark:text-white mt-1 leading-none">{{ $user->email }}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-8 group">
                                <div class="size-14 bg-white dark:bg-zinc-800 rounded-3xl flex items-center justify-center shadow-sm border border-zinc-100 dark:border-zinc-700">
                                    <flux:icon.phone class="size-7 text-indigo-500" />
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-indigo-500 uppercase tracking-widest">{{ $user->phone_type ?? 'Terminal Link' }}</p>
                                    <p class="text-xl font-bold text-zinc-900 dark:text-white mt-1 leading-none">{{ $user->contact_phone }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-zinc-50 dark:bg-zinc-950 p-12 rounded-[3.5rem] border border-zinc-100 dark:border-zinc-800">
                        <h3 class="text-[10px] font-black text-zinc-400 uppercase tracking-[0.4em] mb-12">Verified Presence</h3>
                        <div class="space-y-12">
                            <div class="flex items-start gap-8 group">
                                <div class="size-14 bg-white dark:bg-zinc-800 rounded-3xl flex items-center justify-center shadow-sm border border-zinc-100 dark:border-zinc-700">
                                    <flux:icon.map-pin class="size-7 text-rose-500" />
                                </div>
                                <div class="space-y-4">
                                    <p class="text-[10px] font-black text-rose-500 uppercase tracking-widest">Physical Headquarters</p>
                                    <p class="text-lg font-black text-zinc-900 dark:text-white leading-snug">
                                        {{ $user->buslocation->first()?->address ?? 'Address not localized' }}
                                    </p>
                                    <div class="flex flex-wrap gap-2">
                                        <span class="px-4 py-1.5 bg-zinc-200 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 rounded-xl text-[9px] font-black uppercase tracking-widest">{{ $user->buslocation->first()?->city }}</span>
                                        <span class="px-4 py-1.5 bg-zinc-200 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 rounded-xl text-[9px] font-black uppercase tracking-widest">{{ $user->buslocation->first()?->country }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: Territories (Restored) -->
                <div x-show="activeTab === 'territory'" class="animate-fade-in" x-transition>
                    <div class="bg-zinc-50 dark:bg-zinc-950 rounded-[4rem] border border-zinc-100 dark:border-zinc-800 p-14">
                        <div class="flex items-center gap-6 mb-10">
                            <div class="size-14 bg-sky-100 dark:bg-sky-950 rounded-2xl flex items-center justify-center">
                                <flux:icon.globe-alt class="size-8 text-sky-600" />
                            </div>
                            <div>
                                <h3 class="text-xl font-black text-zinc-900 dark:text-white">Geospatial Scope</h3>
                                <p class="text-[10px] text-zinc-400 font-bold uppercase tracking-widest">Authorized Regional Domain</p>
                            </div>
                        </div>
                        @livewire('territory.user-territory', ['createdUser' => $user->slug])
                    </div>
                </div>

                <!-- Tab: Audit Ledger -->
                <div x-show="activeTab === 'audit'" class="animate-fade-in space-y-10" x-transition>
                    <div class="relative border-l-2 border-zinc-100 dark:border-zinc-800 ml-4 space-y-10 pb-10">
                        @forelse($activityLogs as $log)
                            <div class="relative pl-12">
                                <div class="absolute -left-[11px] top-1 size-5 rounded-full bg-white dark:bg-zinc-900 border-4 border-indigo-500"></div>
                                <div class="bg-zinc-50 dark:bg-zinc-950 p-8 rounded-[2.5rem] border border-zinc-100 dark:border-zinc-800 shadow-sm">
                                    <div class="flex items-center justify-between mb-6">
                                        <div class="flex items-center gap-4">
                                            <span class="px-3 py-1 bg-indigo-100 text-indigo-600 text-[9px] font-black rounded-full uppercase tracking-widest">{{ $log->event }}</span>
                                            <span class="text-[9px] font-bold text-zinc-400">
                                                {{ class_basename($log->auditable_type) }}
                                                @if($log->auditable_type === 'App\Models\BusLocation')
                                                    (Geo State)
                                                @endif
                                            </span>
                                            <span class="text-[10px] font-bold text-zinc-400 tracking-tighter">{{ $log->created_at->format('M d, Y @ H:i') }}</span>
                                        </div>
                                        <flux:link href="{{ $log->actor ? route('users.show', $log->actor) : '#' }}" wire:navigate class="text-right group/actor">
                                            <p class="text-[9px] font-black text-zinc-400 uppercase leading-none group-hover/actor:text-indigo-500 transition-colors">Perpetrated By</p>
                                            <p class="text-xs font-black text-zinc-800 dark:text-white group-hover/actor:text-indigo-600 transition-colors">{{ $log->actor->contact_person ?? 'System Engine' }}</p>
                                        </flux:link>
                                    </div>
                                    <div class="grid grid-cols-1 gap-3">
                                        @foreach($log->payload as $field => $changes)
                                            <div class="flex items-center justify-between p-4 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-100 dark:border-zinc-800">
                                                <div class="flex items-center gap-3">
                                                    <flux:icon.variable class="size-3.5 text-zinc-300" />
                                                    <span class="text-[10px] font-mono font-black text-zinc-500 uppercase">{{ str_replace('_', ' ', $field) }}</span>
                                                </div>
                                                <div class="flex items-center gap-6">
                                                    <span class="text-[11px] font-bold text-zinc-400 line-through">{{ $changes['old'] ?? 'EMPTY' }}</span>
                                                    <flux:icon.arrow-long-right class="size-4 text-zinc-200" />
                                                    <span class="text-[11px] font-black text-emerald-600">{{ $changes['new'] }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="py-20 text-center opacity-40"><p class="text-xs font-black uppercase tracking-widest">Temporal Ledger Empty</p></div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>