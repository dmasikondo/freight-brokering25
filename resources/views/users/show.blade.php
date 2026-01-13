<x-layouts.app :title="$user->contact_person . ' - Profile'">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 md:p-8" x-data="{
        activeTab: 'activity',
        isShipper: {{ $user->hasRole('shipper') ? 'true' : 'false' }},
        isCarrier: {{ $user->hasRole('carrier') ? 'true' : 'false' }},
    }">

        <div
            class="bg-white dark:bg-zinc-900 rounded-[2.5rem] shadow-2xl overflow-hidden border border-zinc-200 dark:border-zinc-800 flex flex-col lg:flex-row min-h-[750px]">

            <div
                class="lg:w-96 bg-zinc-50 dark:bg-zinc-950 p-10 border-r border-zinc-100 dark:border-zinc-800 flex flex-col items-center text-center">

                <div class="relative group">
                    <div
                        class="w-48 h-48 rounded-[3.5rem] {{ $theme['bg'] ?? 'bg-zinc-200' }} {{ $theme['text'] ?? 'text-zinc-500' }} flex items-center justify-center shadow-2xl transition-all duration-700 group-hover:scale-105 group-hover:-rotate-3 overflow-hidden border-4 border-white dark:border-zinc-800">
                        <flux:icon :name="$theme['icon'] ?? 'user'" class="size-28 opacity-90" />
                    </div>
                </div>

                <div class="mt-8 space-y-3 w-full">
                    <h1 class="text-3xl font-black text-zinc-900 dark:text-white tracking-tight leading-none">
                        {{ $user->contact_person }}
                    </h1>

                    {{-- Roles and Classifications --}}
                    <div class="flex flex-wrap justify-center gap-2">
                        @foreach ($user->roles as $role)
                            <div class="flex flex-col items-center">
                                <span
                                    class="px-3 py-1 rounded-lg bg-zinc-900 text-white text-[9px] font-black uppercase tracking-widest shadow-md">
                                    {{ $role->name }}
                                </span>
                                @if (in_array(strtolower($role->name), ['carrier', 'shipper']) && $role->pivot->classification)
                                    <span
                                        class="mt-1 text-[8px] font-bold uppercase tracking-tighter {{ $role->pivot->classification === 'real_owner' ? 'text-amber-600' : 'text-sky-600' }}">
                                        {{ str_replace('_', ' ', $role->pivot->classification) }}
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- Suspension Banner --}}
                    @if ($user->isSuspended())
                        <div
                            class="mt-4 w-full p-3 rounded-2xl bg-rose-50 dark:bg-rose-950/30 border border-rose-100 dark:border-rose-900 flex items-center justify-center gap-2">
                            <flux:icon.no-symbol variant="mini" class="size-4 text-rose-600" />
                            <span class="text-[10px] font-black text-rose-600 uppercase tracking-widest">Account
                                Suspended</span>
                        </div>
                    @endif

                    {{-- Approval Status & ID --}}
                    @if ($user->isApproved())
                        <div class="mt-4 flex flex-col items-center gap-2">
                            <div
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900">
                                <flux:icon.check-badge variant="mini" class="size-3.5 text-emerald-500" />
                                <span
                                    class="text-[9px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest">Verified
                                    Account</span>
                            </div>

                            <div class="mt-4 w-full p-4 bg-zinc-900 rounded-2xl shadow-xl border border-zinc-700">
                                <span class="text-[8px] font-black text-zinc-500 uppercase tracking-[0.3em]">Official
                                    Identity</span>
                                <div class="font-mono text-sm text-white mt-1 tracking-[0.2em]">
                                    {{ $user->identification_number }}
                                </div>
                            </div>

                            @if ($user->approvedBy)
                                <div class="mt-2 flex flex-col items-center">
                                    <span class="text-[8px] font-bold text-zinc-400 uppercase">Verified By</span>
                                    <flux:link href="{{ route('users.show', $user->approvedBy) }}" wire:navigate
                                        class="text-[11px] font-black text-zinc-700 dark:text-zinc-300 hover:text-emerald-500">
                                        {{ $user->approvedBy->contact_person }}
                                    </flux:link>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Quick Actions --}}
                <div class="mt-auto pt-10 w-full space-y-4">
                    {{-- Quick Actions Sidebar Area --}}
                    <div class="mt-auto pt-10 w-full space-y-4">
                        @if ($user->needsApproval())

                            {{-- Check specifically for the 'approval' key from your Action --}}
                            @if ($errors->has('approval'))
                                <div
                                    class="mb-6 p-4 text-left bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900 rounded-2xl shadow-sm">
                                    <div class="flex items-center gap-2 mb-3">
                                        <flux:icon.exclamation-circle variant="mini" class="text-rose-600 size-4" />
                                        <span
                                            class="text-[10px] font-black text-rose-600 uppercase tracking-widest">Requirements
                                            Missing</span>
                                    </div>

                                    <ul class="space-y-2">
                                        @foreach ($errors->get('approval') as $message)
                                            {{-- Handle case where $message might be an array or string --}}
                                            @if (is_array($message))
                                                @foreach ($message as $subMessage)
                                                    <li
                                                        class="text-[10px] font-bold text-rose-700 dark:text-rose-400 flex items-start gap-2 leading-tight">
                                                        <span
                                                            class="mt-1 size-1.5 bg-rose-500 rounded-full shrink-0"></span>
                                                        {{ $subMessage }}
                                                    </li>
                                                @endforeach
                                            @else
                                                <li
                                                    class="text-[10px] font-bold text-rose-700 dark:text-rose-400 flex items-start gap-2 leading-tight">
                                                    <span
                                                        class="mt-1 size-1.5 bg-rose-500 rounded-full shrink-0"></span>
                                                    {{ $message }}
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form action="{{ route('users.approve', $user) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <flux:button type="submit" variant="primary" color="emerald"
                                    class="w-full !rounded-[1.5rem] !py-4 font-black uppercase text-xs shadow-lg">
                                    Approve Profile
                                </flux:button>
                            </form>
                        @endif
                    </div>

                    @can('suspend', $user)
                        @if ($user->isSuspended())
                            <form action="{{ route('users.unsuspend', $user) }}" method="POST">
                                @csrf
                                <flux:button type="submit" variant="primary" color="emerald"
                                    class="w-full !rounded-[1.5rem] !py-4 font-black uppercase text-xs shadow-lg">
                                    Lift Suspension
                                </flux:button>
                            </form>
                        @else
                            <flux:modal.trigger name="suspend_user_modal">
                                <flux:button variant="primary" color="rose"
                                    class="w-full !rounded-[1.5rem] !py-4 font-black uppercase text-xs shadow-lg">
                                    Suspend Account
                                </flux:button>
                            </flux:modal.trigger>
                        @endif
                    @endcan

                    @can('update', $user)
                        <flux:button variant="primary" color="indigo" class="w-full !rounded-[1.5rem] !py-4 font-black">
                            Modify Identity Hub</flux:button>
                    @endcan
                </div>
            </div>

            <div class="flex-1 p-8 lg:p-12 flex flex-col">
                <div
                    class="flex items-center gap-3 mb-12 bg-zinc-100 dark:bg-zinc-800 p-1.5 rounded-[2rem] w-fit border border-zinc-200 dark:border-zinc-700 overflow-x-auto">
                    <button @click="activeTab = 'activity'"
                        :class="activeTab === 'activity' ? 'bg-white dark:bg-zinc-700 text-indigo-600 shadow-lg' :
                            'text-zinc-500'"
                        class="px-8 py-3 rounded-[1.6rem] text-sm font-black transition-all">Activity Hub</button>
                    <button @click="activeTab = 'contact'"
                        :class="activeTab === 'contact' ? 'bg-white dark:bg-zinc-700 text-indigo-600 shadow-lg' :
                            'text-zinc-500'"
                        class="px-8 py-3 rounded-[1.6rem] text-sm font-black transition-all">Profile Metadata</button>
                    <button @click="activeTab = 'audit'"
                        :class="activeTab === 'audit' ? 'bg-white dark:bg-zinc-700 text-indigo-600 shadow-lg' :
                            'text-zinc-500'"
                        class="px-8 py-3 rounded-[1.6rem] text-sm font-black transition-all">Audit Ledger</button>

                    @if ($user->isSuspended() || $user->suspension_reason)
                        <button @click="activeTab = 'suspension'"
                            :class="activeTab === 'suspension' ? 'bg-rose-600 text-white shadow-lg' : 'text-rose-500'"
                            class="px-8 py-3 rounded-[1.6rem] text-sm font-black transition-all">Suspension
                            details</button>
                    @endif
                </div>

                {{-- Activity Hub Tab --}}
                <div x-show="activeTab === 'activity'" x-transition class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div
                            class="p-8 bg-zinc-50 dark:bg-zinc-950 rounded-[2.5rem] border border-zinc-100 dark:border-zinc-800">
                            <h3 class="text-lg font-black mb-4">Registry Overview</h3>
                            <p class="text-sm text-zinc-500">Member since {{ $user->created_at->format('M Y') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Profile Metadata Tab --}}
                <div x-show="activeTab === 'contact'" x-transition class="grid grid-cols-1 xl:grid-cols-2 gap-10">
                    <div
                        class="bg-zinc-50 dark:bg-zinc-950 p-12 rounded-[3.5rem] border border-zinc-100 dark:border-zinc-800 space-y-10">
                        <h3 class="text-[10px] font-black text-zinc-400 uppercase tracking-[0.4em]">Node Connectivity
                        </h3>
                        <div class="space-y-8">
                            <a href="mailto:{{ $user->email }}" class="flex items-start gap-8 group">
                                <div
                                    class="size-14 bg-white dark:bg-zinc-800 rounded-3xl flex items-center justify-center shadow-sm border border-zinc-100 dark:border-zinc-700 group-hover:border-indigo-500 transition-all">
                                    <flux:icon.envelope class="size-7 text-indigo-500" />
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-indigo-500 uppercase tracking-widest">Protocol
                                        Email</p>
                                    <p
                                        class="text-xl font-bold text-zinc-900 dark:text-white mt-1 leading-none group-hover:text-indigo-600">
                                        {{ $user->email }}</p>
                                </div>
                            </a>

                            <a href="tel:{{ $user->contact_phone }}" class="flex items-start gap-8 group">
                                <div
                                    class="size-14 bg-white dark:bg-zinc-800 rounded-3xl flex items-center justify-center shadow-sm border border-zinc-100 dark:border-zinc-700 group-hover:border-indigo-500 transition-all">
                                    <flux:icon.phone class="size-7 text-indigo-500" />
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-indigo-500 uppercase tracking-widest">
                                        {{ $user->phone_type ?? 'Terminal Link' }}</p>
                                    <p
                                        class="text-xl font-bold text-zinc-900 dark:text-white mt-1 leading-none group-hover:text-indigo-600">
                                        {{ $user->contact_phone }}</p>
                                </div>
                            </a>

                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $user->contact_phone) }}"
                                target="_blank" class="flex items-start gap-8 group">
                                <div
                                    class="size-14 bg-white dark:bg-zinc-800 rounded-3xl flex items-center justify-center shadow-sm border border-zinc-100 dark:border-zinc-700 group-hover:border-emerald-500 transition-all">
                                    <flux:icon.chat-bubble-left-right class="size-7 text-emerald-500" />
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-emerald-500 uppercase tracking-widest">
                                        WhatsApp Direct</p>
                                    <p
                                        class="text-xl font-bold text-zinc-900 dark:text-white mt-1 leading-none group-hover:text-emerald-600">
                                        Open Chat</p>
                                </div>
                            </a>
                        </div>
                    </div>

                    <div
                        class="bg-zinc-50 dark:bg-zinc-950 p-12 rounded-[3.5rem] border border-zinc-100 dark:border-zinc-800">
                        <h3 class="text-[10px] font-black text-zinc-400 uppercase tracking-[0.4em] mb-12">Verified
                            Presence</h3>
                        <div class="flex items-start gap-8">
                            <div
                                class="size-14 bg-white dark:bg-zinc-800 rounded-3xl flex items-center justify-center shadow-sm border border-zinc-100 dark:border-zinc-700">
                                <flux:icon.map-pin class="size-7 text-rose-500" />
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-rose-500 uppercase tracking-widest">Headquarters
                                </p>
                                <p class="text-lg font-black text-zinc-900 dark:text-white leading-snug">
                                    {{ $user->buslocation->first()?->address ?? 'Location not localized' }}
                                </p>
                                <p class="text-sm text-zinc-500 mt-2 font-bold uppercase tracking-tighter">
                                    {{ $user->buslocation->first()?->city }},
                                    {{ $user->buslocation->first()?->country }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Audit Ledger Tab --}}
                <div x-show="activeTab === 'audit'" x-transition class="animate-fade-in space-y-10">
                    <div class="relative border-l-2 border-zinc-100 dark:border-zinc-800 ml-4 space-y-10 pb-10">
                        @forelse($activityLogs as $log)
                            <div class="relative pl-12">
                                <div
                                    class="absolute -left-[11px] top-1 size-5 rounded-full bg-white dark:bg-zinc-900 border-4 border-indigo-500">
                                </div>
                                <div
                                    class="bg-zinc-50 dark:bg-zinc-950 p-8 rounded-[2.5rem] border border-zinc-100 dark:border-zinc-800 shadow-sm">
                                    <div class="flex items-center justify-between mb-6">
                                        <div class="flex items-center gap-4">
                                            <span
                                                class="px-3 py-1 bg-indigo-100 text-indigo-600 text-[9px] font-black rounded-full uppercase tracking-widest">{{ $log->event }}</span>
                                            <span
                                                class="text-[10px] font-bold text-zinc-400 tracking-tighter">{{ $log->created_at->format('M d, Y @ H:i') }}</span>
                                        </div>
                                        <flux:link href="{{ $log->actor ? route('users.show', $log->actor) : '#' }}"
                                            wire:navigate class="text-right group/actor">
                                            <p class="text-[9px] font-black text-zinc-400 uppercase leading-none">By:
                                                {{ $log->actor->contact_person ?? 'System' }}</p>
                                        </flux:link>
                                    </div>
                                    <div class="grid grid-cols-1 gap-3">
                                        @foreach ($log->payload as $field => $changes)
                                            <div
                                                class="flex items-center justify-between p-4 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-100 dark:border-zinc-800">
                                                <span
                                                    class="text-[10px] font-mono font-black text-zinc-500 uppercase">{{ str_replace('_', ' ', $field) }}</span>
                                                <div class="flex items-center gap-4">
                                                    <span
                                                        class="text-[11px] font-bold text-zinc-400 line-through">{{ $changes['old'] ?? '...' }}</span>
                                                    <flux:icon.arrow-long-right class="size-4 text-zinc-200" />
                                                    <span
                                                        class="text-[11px] font-black text-emerald-600">{{ $changes['new'] }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="py-20 text-center opacity-40">
                                <p class="text-xs font-black uppercase tracking-widest">No audit history found</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Suspension Details Tab --}}
                <div x-show="activeTab === 'suspension'" x-transition>
                    <div
                        class="bg-rose-50 dark:bg-rose-950/20 p-12 rounded-[3.5rem] border border-rose-100 dark:border-rose-900/50">
                        <div class="flex items-center gap-6 mb-8">
                            <div class="size-16 bg-rose-600 rounded-[2rem] flex items-center justify-center shadow-lg">
                                <flux:icon.no-symbol class="size-8 text-white" />
                            </div>
                            <div>
                                <h3 class="text-2xl font-black text-rose-600 tracking-tight">Access Restriction</h3>
                                <p class="text-[10px] font-bold text-rose-500 uppercase tracking-[0.2em]">Enforcement
                                    Protocol Log</p>
                            </div>
                        </div>

                        <div class="space-y-8">
                            <div
                                class="bg-white dark:bg-zinc-900 p-8 rounded-[2rem] border border-rose-200 dark:border-rose-800 shadow-sm">
                                <p class="text-[10px] font-black text-zinc-400 uppercase tracking-widest mb-4">Official
                                    Reason</p>
                                <p class="text-lg font-bold text-zinc-900 dark:text-white italic">
                                    "{{ $user->suspension_reason ?? 'No formal reason provided.' }}"</p>
                            </div>

                            @if ($user->suspendedBy)
                                <div
                                    class="flex items-center gap-4 p-6 bg-zinc-900 rounded-2xl border border-zinc-800 shadow-xl">
                                    <div
                                        class="size-12 rounded-xl bg-zinc-800 flex items-center justify-center border border-zinc-700">
                                        <flux:icon.shield-exclamation class="size-6 text-rose-500" />
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-black text-zinc-500 uppercase tracking-widest">
                                            Issuing Officer</p>
                                        <flux:link href="{{ route('users.show', $user->suspendedBy) }}"
                                            class="text-sm font-black text-white hover:text-rose-400">
                                            {{ $user->suspendedBy->contact_person }}</flux:link>
                                    </div>
                                    <div class="ml-auto text-right">
                                        <p class="text-[9px] font-black text-zinc-500 uppercase tracking-widest">
                                            Timestamp</p>
                                        <p class="text-[10px] font-bold text-white uppercase tracking-tighter">
                                            {{ $user->suspended_at?->format('d M Y @ H:i') ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Suspension Modal --}}
    @include('includes.suspend-user-modal')
</x-layouts.app>
