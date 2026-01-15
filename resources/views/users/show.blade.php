<x-layouts.app :title="$user->contact_person . ' - Profile'">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 md:p-8" x-data="{
        activeTab: 'activity',
        isCarrier: {{ $isCarrier ? 'true' : 'false' }},
        isShipper: {{ $isShipper ? 'true' : 'false' }}
    }">

        {{-- TOP STATUS BAR: Onboarding Integrity --}}
        @if ($isCarrier)
            <div
                class="w-full bg-white dark:bg-zinc-900 p-6 rounded-[2rem] border border-zinc-200 dark:border-zinc-800 flex flex-col md:flex-row items-center justify-between gap-6 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="size-12 bg-indigo-50 dark:bg-indigo-950/30 rounded-2xl flex items-center justify-center">
                        <flux:icon.clipboard-document-check class="size-6 text-indigo-600" />
                    </div>
                    <div>
                        <h4 class="text-xs font-black uppercase tracking-widest text-zinc-900 dark:text-white">Profile
                            Integrity</h4>
                        <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-tighter">Verification status for
                            logistics clearance</p>
                    </div>
                </div>
                <div class="flex-1 max-w-2xl w-full">
                    <livewire:carrier.profile-completion-check :user="$user" />
                </div>
                <div
                    class="px-6 py-2 rounded-xl bg-zinc-50 dark:bg-zinc-800 border border-zinc-100 dark:border-zinc-700 text-center">
                    <span
                        class="text-[10px] font-black uppercase {{ $approvalStatus['is_valid'] ? 'text-emerald-500' : 'text-amber-500' }}">
                        {{ $approvalStatus['is_valid'] ? 'Authorization Ready' : 'Incomplete' }}
                    </span>
                </div>
            </div>
        @endif

        <div
            class="bg-white dark:bg-zinc-900 rounded-[2.5rem] shadow-2xl overflow-hidden border border-zinc-200 dark:border-zinc-800 flex flex-col lg:flex-row min-h-[850px]">

            {{-- SIDEBAR: Identity & Forensic Registration --}}
            <div
                class="lg:w-96 bg-zinc-50 dark:bg-zinc-950 p-10 border-r border-zinc-100 dark:border-zinc-800 flex flex-col items-center">
                <div
                    class="w-48 h-48 rounded-[3.5rem] {{ $theme['bg'] }} {{ $theme['text'] }} flex items-center justify-center shadow-2xl border-4 border-white dark:border-zinc-800">
                    <flux:icon :name="$theme['icon']" class="size-28 opacity-90" />
                </div>

                <div class="mt-8 space-y-4 w-full text-center">
                    <div>
                        <h1 class="text-3xl font-black text-zinc-900 dark:text-white tracking-tight leading-none">
                            {{ $user->contact_person }}</h1>
                        {{-- IDENTIFICATION NUMBER BADGE: Only visible if approved and exists in DB --}}
                        @if ($user->isApproved() && $user->getRawOriginal('identification_number'))
                            <div
                                class="mt-3 inline-flex items-center gap-2 px-3 py-1 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm">
                                <flux:icon.finger-print variant="mini" class="size-3 text-indigo-500" />
                                <span
                                    class="text-[10px] font-mono font-black text-zinc-600 dark:text-zinc-400 tracking-tighter">
                                    {{ $user->getRawOriginal('identification_number') }}
                                </span>
                            </div>
                        @endif
                        <p class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">
                            {{ $user->organisation }}</p>
                    </div>

                    <div class="flex flex-wrap justify-center gap-2">
                        @foreach ($user->roles as $role)
                            <div class="flex flex-col items-center">
                                <span
                                    class="px-3 py-1 rounded-lg bg-zinc-900 text-white text-[9px] font-black uppercase tracking-widest shadow-md">{{ $role->name }}</span>
                                @if ($role->pivot?->classification)
                                    <span
                                        class="mt-1 text-[8px] font-black uppercase {{ $role->pivot->classification === 'real_owner' ? 'text-amber-600' : 'text-sky-600' }}">
                                        ● {{ str_replace('_', ' ', $role->pivot->classification) }}
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-8 pt-6 border-t border-zinc-200 dark:border-zinc-800 w-full text-left space-y-4">
                        <div class="flex items-center gap-3">
                            <flux:icon.calendar class="size-4 text-zinc-400" />
                            <div>
                                <p class="text-[8px] font-black text-zinc-400 uppercase leading-none mb-1">Registered On
                                </p>
                                <p class="text-[11px] font-bold">{{ $user->created_at->format('d M Y, H:i') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <flux:icon.user-plus class="size-4 text-zinc-400" />
                            <div>
                                <p class="text-[8px] font-black text-zinc-400 uppercase leading-none mb-1">Onboarded By
                                </p>
                                <p class="text-[11px] font-bold">
                                    @if ($user->createdBy)
                                        <a href="{{ route('users.show', $user->createdBy) }}"
                                            class="hover:text-indigo-600 underline decoration-zinc-200">{{ $user->createdBy->contact_person }}</a>
                                    @else
                                        Self-Registration
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-auto pt-10 w-full space-y-4">
                    @if ($user->needsApproval())
                        {{-- Validation Error Feedback --}}
                        @if ($errors->any())
                            <div
                                class="p-4 mb-4 rounded-2xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800">
                                <ul class="list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li
                                            class="text-[10px] font-bold text-rose-600 dark:text-rose-400 uppercase tracking-tighter">
                                            {{ $error }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <form action="{{ route('users.approve', $user) }}" method="POST">
                            @csrf @method('PATCH')
                            <flux:button type="submit" variant="primary" color="emerald"
                                class="w-full !rounded-[1.5rem] !py-4 font-black uppercase text-xs">Authorize Node
                            </flux:button>
                        </form>
                    @endif
                    @can('suspend', $user)
                        <flux:modal.trigger name="suspend_user_modal">
                            <flux:button variant="primary" color="rose"
                                class="w-full !rounded-[1.5rem] !py-4 font-black uppercase text-xs">Suspend Account
                            </flux:button>
                        </flux:modal.trigger>
                    @endcan
                </div>
            </div>

            {{-- CONTENT AREA --}}
            <div class="flex-1 p-8 lg:p-12 flex flex-col">
                <div
                    class="flex flex-wrap items-center gap-3 mb-12 bg-zinc-100 dark:bg-zinc-800 p-1.5 rounded-[2rem] w-fit border border-zinc-200 dark:border-zinc-700">
                    <button @click="activeTab = 'activity'"
                        :class="activeTab === 'activity' ? 'bg-white shadow-lg text-indigo-600' : 'text-zinc-500'"
                        class="px-6 py-2.5 rounded-[1.6rem] text-xs font-black transition-all">Activity Hub</button>
                    @if ($isShipper)
                        <button @click="activeTab = 'freights'"
                            :class="activeTab === 'freights' ? 'bg-white shadow-lg text-indigo-600' : 'text-zinc-500'"
                            class="px-6 py-2.5 rounded-[1.6rem] text-xs font-black transition-all">Freight
                            Ledger</button>
                    @endif
                    @if ($isCarrier)
                        <button @click="activeTab = 'logistics'"
                            :class="activeTab === 'logistics' ? 'bg-white shadow-lg text-indigo-600' : 'text-zinc-500'"
                            class="px-6 py-2.5 rounded-[1.6rem] text-xs font-black transition-all">Logistics
                            Network</button>
                    @endif
                    <button @click="activeTab = 'contact'"
                        :class="activeTab === 'contact' ? 'bg-white shadow-lg text-indigo-600' : 'text-zinc-500'"
                        class="px-6 py-2.5 rounded-[1.6rem] text-xs font-black transition-all">Metadata</button>
                    <button @click="activeTab = 'audit'"
                        :class="activeTab === 'audit' ? 'bg-white shadow-lg text-indigo-600' : 'text-zinc-500'"
                        class="px-6 py-2.5 rounded-[1.6rem] text-xs font-black transition-all">Audit Trail</button>
                </div>

                {{-- TAB: ACTIVITY HUB --}}
                <div x-show="activeTab === 'activity'" x-transition class="space-y-12">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @if ($isShipper)
                            <div class="p-8 bg-indigo-600 text-white rounded-[2.5rem] shadow-xl">
                                <p class="text-[10px] font-black uppercase tracking-widest opacity-70">Freight Volume
                                </p>
                                <p class="text-4xl font-black mt-2">{{ $user->freights()->count() }}</p>
                            </div>
                        @endif

                        @if ($isCarrier)
                            <div class="p-8 bg-emerald-600 text-white rounded-[2.5rem] shadow-xl">
                                <p class="text-[10px] font-black uppercase tracking-widest opacity-70">Active Lanes</p>
                                <p class="text-4xl font-black mt-2">{{ $user->lanes()->count() }}</p>
                            </div>
                        @endif

                        {{-- ENHANCED VERIFICATION CARD --}}
                        <div
                            class="p-8 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-[2.5rem] flex flex-col justify-between">
                            <div>
                                <p class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Verification
                                    Status</p>
                                <p
                                    class="text-2xl font-black mt-2 {{ $user->isApproved() ? 'text-emerald-500' : 'text-amber-500' }}">
                                    {{ $user->isApproved() ? 'Authorized' : 'Pending Review' }}
                                </p>
                            </div>

                            @if ($user->isApproved() && $user->approvedBy)
                                <div class="mt-4 pt-4 border-t border-zinc-100 dark:border-zinc-700">
                                    <p class="text-[9px] font-black text-zinc-400 uppercase tracking-tighter">Authorized
                                        By</p>
                                    <div class="flex items-center justify-between mt-1">
                                        <a href="{{ route('users.show', $user->approvedBy) }}"
                                            class="text-xs font-bold text-indigo-600 hover:underline">
                                            {{ $user->approvedBy->contact_person }}
                                        </a>
                                        <span class="text-[10px] font-medium text-zinc-400">
                                            {{ $user->approved_at ? $user->approved_at->diffForHumans() : 'Date missing' }}
                                        </span>
                                    </div>
                                </div>
                            @elseif(!$user->isApproved())
                                <div class="mt-4 flex items-center gap-2">
                                    <div class="size-2 bg-amber-500 rounded-full animate-pulse"></div>
                                    <p class="text-[10px] font-bold text-amber-600 uppercase">Waiting for Clearance</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- TAB: LOGISTICS NETWORK --}}
                @if ($isCarrier)
                    <div x-show="activeTab === 'logistics'" x-transition class="space-y-10">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <livewire:carrier.director.status-info :user="$user" />
                            <livewire:carrier.fleet.status-info :user="$user" />
                            <livewire:carrier.traderef.status-info :user="$user" />
                        </div>

                        <div
                            class="bg-zinc-50 dark:bg-zinc-950 p-8 rounded-[2.5rem] border border-zinc-100 dark:border-zinc-800 space-y-6">
                            <h3 class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Compliance
                                Documents</h3>
                            <div class="grid grid-cols-1 gap-6">
                                <livewire:carrier.document-upload :user="$user" />
                                <livewire:carrier.recent-file-uploads :user="$user" />
                            </div>
                        </div>
                    </div>
                @endif

                {{-- TAB: METADATA --}}
                <div x-show="activeTab === 'contact'" x-transition class="grid grid-cols-1 xl:grid-cols-2 gap-8">
                    <div
                        class="bg-zinc-50 dark:bg-zinc-950 p-8 rounded-[2.5rem] border border-zinc-100 dark:border-zinc-800 space-y-6">
                        <h3 class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Connectivity</h3>
                        <div class="space-y-4">
                            <div class="flex items-center gap-4">
                                <flux:icon.envelope class="size-5 text-indigo-500" />
                                <div>
                                    <p class="text-[8px] font-black text-zinc-400 uppercase">Email</p>
                                    <p class="text-sm font-bold">{{ $user->email }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <flux:icon.phone class="size-5 text-emerald-500" />
                                <div>
                                    <p class="text-[8px] font-black text-zinc-400 uppercase">Phone</p>
                                    <p class="text-sm font-bold">{{ $user->contact_phone }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <flux:icon.chat-bubble-left-right class="size-5 text-green-500" />
                                <div>
                                    <p class="text-[8px] font-black text-zinc-400 uppercase">WhatsApp</p>
                                    <p class="text-sm font-bold">{{ $user->whatsapp_number ?? $user->contact_phone }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-zinc-50 dark:bg-zinc-950 p-8 rounded-[2.5rem] border border-zinc-100 dark:border-zinc-800 space-y-6">
                        <h3 class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Physical Address
                        </h3>
                        @forelse($user->buslocation as $location)
                            <div class="flex gap-4">
                                <flux:icon.map-pin class="size-5 text-rose-500" />
                                <div>
                                    <p class="text-sm font-bold leading-tight">{{ $location->address }}</p>
                                    <p class="text-[10px] font-black text-zinc-400 uppercase">{{ $location->city }},
                                        {{ $location->country }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs italic text-zinc-400">No address recorded.</p>
                        @endforelse
                    </div>
                </div>

                {{-- TAB: AUDIT TRAIL --}}
                <div x-show="activeTab === 'audit'" x-transition class="space-y-6">
                    @forelse($activityLogs as $log)
                        <div
                            class="p-6 bg-zinc-50 dark:bg-zinc-950 rounded-[2.5rem] border border-zinc-100 dark:border-zinc-800 shadow-sm transition-all hover:shadow-md">

                            {{-- HEADER: Event Type, Time, and Actor --}}
                            <div class="flex justify-between items-start">
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="px-2 py-0.5 rounded-md bg-zinc-900 text-white text-[9px] font-black uppercase tracking-tighter">
                                            {{ $log->event }}
                                        </span>
                                        <span class="text-[10px] font-bold text-zinc-400">
                                            {{ $log->created_at->diffForHumans() }}
                                        </span>
                                    </div>

                                    <p class="text-xs font-bold text-zinc-900 dark:text-white">
                                        @if ($log->actor)
                                            <a href="{{ route('users.show', $log->actor) }}"
                                                class="text-indigo-600 hover:underline">
                                                {{ $log->actor->contact_person }}
                                            </a>
                                        @else
                                            <span class="text-zinc-500 italic font-medium">System Automated</span>
                                        @endif

                                        <span class="text-zinc-400 font-medium ml-1">
                                            modified
                                            @if (class_basename($log->auditable_type) === 'Contact')
                                                <span
                                                    class="text-zinc-900 dark:text-white font-black uppercase text-[10px]">
                                                    {{-- Distinguish between Director and TradeRef via the auditable relationship --}}
                                                    {{ optional($log->auditable)->type === 'director' ? 'Director Profile' : 'Trade Reference' }}
                                                </span>
                                            @else
                                                the <span
                                                    class="text-zinc-600 dark:text-zinc-300">{{ strtolower(class_basename($log->auditable_type)) }}</span>
                                                record
                                            @endif
                                        </span>
                                    </p>
                                </div>
                                <div class="flex flex-col items-end gap-1">
                                    <flux:icon.finger-print class="size-5 text-zinc-200" />
                                    <span class="text-[9px] font-mono text-zinc-400">{{ $log->ip_address }}</span>
                                </div>
                            </div>

                            {{-- BODY: Field Comparison logic for  custom $changes[$key] structure --}}
                            @if (is_array($log->payload) && count($log->payload) > 0)
                                <div class="mt-5 space-y-2">
                                    @foreach ($log->payload as $field => $values)
                                        <div
                                            class="grid grid-cols-1 md:grid-cols-3 gap-2 md:gap-4 items-center p-3 bg-white dark:bg-zinc-900 rounded-xl border border-zinc-100 dark:border-zinc-800">

                                            {{-- Field Name: Cleaned up --}}
                                            <div
                                                class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">
                                                {{ str_replace('_', ' ', $field) }}
                                            </div>

                                            {{-- Old Value: Strikethrough Red --}}
                                            <div
                                                class="text-[11px] font-medium text-rose-500 line-through truncate px-2 py-1 bg-rose-50/50 dark:bg-rose-950/20 rounded border border-rose-100/50">
                                                {{ is_array($values['old'] ?? '') ? 'Data Object' : $values['old'] ?? '—' }}
                                            </div>

                                            {{-- New Value: Bold Emerald --}}
                                            <div
                                                class="text-[11px] font-bold text-emerald-600 truncate px-2 py-1 bg-emerald-50/50 dark:bg-emerald-950/20 rounded border border-emerald-100/50">
                                                {{ is_array($values['new'] ?? '') ? 'Data Object' : $values['new'] ?? '—' }}
                                            </div>

                                        </div>
                                    @endforeach
                                </div>
                            @else
                                {{-- Fallback for entries with no payload (e.g. simple delete or custom events) --}}
                                <div
                                    class="mt-4 p-4 rounded-xl border border-dashed border-zinc-200 dark:border-zinc-800 text-center">
                                    <p class="text-[10px] italic text-zinc-400 uppercase font-black">Record update
                                        detected with no attribute data</p>
                                </div>
                            @endif

                        </div>
                    @empty
                        {{-- Empty State: No logs found --}}
                        <div
                            class="flex flex-col items-center justify-center py-20 bg-zinc-50 dark:bg-zinc-950 rounded-[2.5rem] border-2 border-dashed border-zinc-100 dark:border-zinc-900">
                            <flux:icon.finger-print class="size-12 text-zinc-200 mb-4" />
                            <h3 class="text-xs font-black text-zinc-400 uppercase tracking-widest">No Forensic History
                            </h3>
                            <p class="text-[10px] text-zinc-400 mt-1">This node has no recorded administrative
                                modifications.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    @include('includes.suspend-user-modal')
</x-layouts.app>
