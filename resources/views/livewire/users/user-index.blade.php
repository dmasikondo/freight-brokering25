<div class="space-y-6">
    <!-- Action Header -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8">
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight flex items-center gap-4">
                    <flux:icon.user-group class="size-10 text-indigo-600" />
                    Member Intelligence Hub
                </h1>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1 ml-14">
                    Managing {{ $this->users->total() }} Scoped Jurisdictional Profiles
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-4">
                <flux:button href="{{ route('users.create') }}" variant="primary" color="indigo" icon="user-plus"
                    wire:navigate class="px-6">Register New Member</flux:button>
            </div>
        </div>

        <!-- Filter Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2  gap-6 mt-10 pt-8 border-t border-slate-100">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search by name, org, email, or ID..."
                icon="magnifying-glass" clearable class="shadow-sm" />

            <flux:select wire:model.live="filterRole" placeholder="Filter Role" icon="user-circle">
                <flux:select.option value="">All Viewable Roles</flux:select.option>
                @foreach ($this->viewableRoles as $role)
                    <flux:select.option value="{{ $role }}">{{ ucwords($role) }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="statusFilter" icon="shield-check" label="Carrier Compliance">
                <flux:select.option value="all">Any Compliance</flux:select.option>
                <flux:select.option value="fully_registered">Fully Compliant</flux:select.option>
                <flux:select.option value="partial_scoped">Incomplete Registration</flux:select.option>
                <flux:select.option value="unmapped_global">Global (Unmapped)</flux:select.option>
            </flux:select>

            <flux:select wire:model.live="sortBy" icon="bars-arrow-down" label="Sorting">
                <flux:select.option value="latest">Recently Joined</flux:select.option>
                <flux:select.option value="name_asc">Name (A-Z)</flux:select.option>
                <flux:select.option value="name_desc">Name (Z-A)</flux:select.option>
                <flux:select.option value="org_asc">Organisation (A-Z)</flux:select.option>
                <flux:select.option value="org_desc">Organisation (Z-A)</flux:select.option>
            </flux:select>

            <flux:select wire:model.live="complianceStatus" icon="finger-print" label="Account Status">
                <flux:select.option value="all">All Statuses</flux:select.option>
                <flux:select.option value="suspended">Suspended Accounts</flux:select.option>
                <flux:select.option value="email_unverified">Email Unverified</flux:select.option>
                <flux:select.option value="email_verified">Email Verified</flux:select.option>
                <flux:select.option value="pending_approval">Pending Approval (S/C)</flux:select.option>
                <flux:select.option value="approved">Approved (S/C)</flux:select.option>
            </flux:select>
        </div>
    </div>

    <!-- Results List -->
    <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-sm overflow-hidden min-h-[500px]">
        <div wire:loading.remove wire:target="search, filterRole, statusFilter, sortBy"
            class="divide-y divide-slate-100">
            @forelse($this->users as $user)
                <div
                    class="group px-10 py-8 hover:bg-slate-50 transition-all flex flex-col xl:flex-row xl:items-center justify-between gap-8">
                    <div class="flex items-center gap-8 min-w-0 flex-1">
                        <!-- Role Icon -->
                        <div class="relative shrink-0">
                            @php
                                $roleName = $user->roles->first()?->name;
                                $classification = $user->roles->first()?->pivot?->classification;
                                $theme = match ($roleName) {
                                    'shipper' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600', 'icon' => 'cube'],
                                    'carrier' => [
                                        'bg' => 'bg-emerald-100',
                                        'text' => 'text-emerald-600',
                                        'icon' => 'truck',
                                    ],
                                    'marketing logistics associate' => [
                                        'bg' => 'bg-purple-100',
                                        'text' => 'text-purple-600',
                                        'icon' => 'megaphone',
                                    ],
                                    'procurement logistics associate' => [
                                        'bg' => 'bg-orange-100',
                                        'text' => 'text-orange-600',
                                        'icon' => 'clipboard-document-list',
                                    ],
                                    'operations logistics associate' => [
                                        'bg' => 'bg-sky-100',
                                        'text' => 'text-sky-600',
                                        'icon' => 'cursor-arrow-ripple',
                                    ],
                                    'logistics operations executive' => [
                                        'bg' => 'bg-teal-100',
                                        'text' => 'text-teal-600',
                                        'icon' => 'archive-box',
                                    ],
                                    'admin' => [
                                        'bg' => 'green-teal-100',
                                        'text' => 'green-teal-600',
                                        'icon' => 'shield-check',
                                    ],
                                    default => ['bg' => 'bg-slate-100', 'text' => 'text-slate-600', 'icon' => 'user'],
                                };
                            @endphp
                            <div
                                class="w-16 h-16 {{ $theme['bg'] }} {{ $theme['text'] }} rounded-[1.5rem] flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-300">
                                <flux:icon :name="$theme['icon']" class="w-8 h-8" />
                            </div>
                        </div>

                        <!-- User Info -->
                        <div class="min-w-0 space-y-1">
                            <div class="flex items-center gap-4 flex-wrap">
                                <flux:link href="{{ route('users.show', $user) }}" wire:navigate
                                    class="text-xl font-black text-slate-900 tracking-tight group-hover:text-indigo-600 transition-colors">
                                    {!! $this->highlight($user->contact_person, $this->search) !!}
                                </flux:link>
                                @if ($user->organisation)
                                    <span class="text-sm font-bold text-slate-400">@ {!! $this->highlight($user->organisation, $this->search) !!}</span>
                                @endif
                                @if (auth()->id() === $user->id)
                                    <span
                                        class="bg-indigo-600 text-white text-[9px] font-black px-2.5 py-1 rounded-full uppercase tracking-widest shadow-lg shadow-indigo-200">Me</span>
                                @endif
                            </div>

                            <div
                                class="flex flex-wrap items-center gap-x-6 gap-y-2 text-xs font-bold text-slate-500 uppercase tracking-tighter">
                                <span class="flex items-center gap-2 group/email">
                                    <flux:icon.envelope
                                        class="size-4 text-slate-300 group-hover/email:text-indigo-400 transition-colors" />
                                    {!! $this->highlight($user->email, $this->search) !!}
                                </span>
                                <span
                                    class="text-indigo-600 bg-indigo-50 px-3 py-1 rounded-lg border border-indigo-100 flex items-center gap-2">
                                    {{ ucwords(str_replace('_', ' ', $roleName ?? 'Member')) }}
                                    @if (in_array($roleName, ['shipper', 'carrier']))
                                        <flux:icon.
                                            :name="$classification === 'real_owner' ? 'shield-check' : 'user-group'"
                                            class="size-3.5 {{ $classification === 'real_owner' ? 'text-amber-500' : 'text-blue-500' }}" />
                                        <span
                                            class="text-[9px]">{{ $classification === 'real_owner' ? 'Real Owner' : 'Broker / Agent' }}</span>
                                    @endif
                                </span>
                                @if ($user->identification_number)
                                    <span
                                        class="bg-slate-900 text-white px-3 py-1 rounded-lg flex items-center gap-2 shadow-lg shadow-slate-200">
                                        <flux:icon.finger-print class="size-3.5 text-slate-400" />
                                        ID: {!! $this->highlight($user->identification_number, $this->search) !!}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Meta & Actions -->
                    <div class="flex flex-col sm:flex-row items-center justify-between xl:justify-end gap-12 shrink-0">
                        <div class="text-right">
                            @if ($user->createdBy)
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">
                                    Registered By</p>
                                <flux:link href="{{ route('users.show', $user->createdBy) }}" wire:navigate
                                    class="flex items-center justify-end gap-2.5 text-sm font-black text-slate-800 hover:text-indigo-600 transition-colors group/creator">
                                    <div
                                        class="w-8 h-8 bg-slate-100 rounded-lg flex items-center justify-center group-hover/creator:bg-indigo-50 transition-colors">
                                        <flux:icon.user-plus
                                            class="size-4 text-slate-400 group-hover/creator:text-indigo-500" />
                                    </div>
                                    {{ $user->createdBy->contact_person }}
                                </flux:link>
                            @else
                                <div class="flex flex-col items-end">
                                    <p
                                        class="text-[10px] font-black text-emerald-500 uppercase tracking-widest mb-1.5 flex items-center gap-1.5">
                                        <flux:icon.bolt class="size-3.5" /> Direct Entry
                                    </p>
                                    <p
                                        class="text-sm font-bold text-slate-700 italic bg-emerald-50 px-3 py-1 rounded-lg border border-emerald-100">
                                        Self Registered</p>
                                </div>
                            @endif
                            <p class="text-[10px] text-slate-300 font-bold mt-2 uppercase tracking-wide">Joined
                                {{ $user->created_at->format('M Y') }}</p>
                        </div>

                        <div class="flex items-center gap-3">
                            <flux:dropdown>
                                <flux:button variant="subtle" icon="ellipsis-horizontal" size="sm"
                                    class="!bg-slate-100 !border-slate-200 !rounded-xl" />
                                <flux:menu>
                                    <flux:menu.item icon="pencil-square" wire:click="userEdit('{{ $user->slug }}')">
                                        Edit Member</flux:menu.item>
                                    <flux:menu.item icon="key" wire:click="userActivation('{{ $user->slug }}')">
                                        {{ $user->must_reset ? 'Activate' : 'Suspend' }}
                                    </flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item variant="danger" icon="trash">Archive User</flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </div>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center py-32 space-y-6">
                    <div
                        class="w-28 h-28 bg-slate-50 rounded-[2.5rem] flex items-center justify-center text-slate-200 border-2 border-dashed border-slate-100 rotate-12">
                        <flux:icon.user-group class="size-14" />
                    </div>
                    <div class="text-center">
                        <p class="text-xl font-black text-slate-900">Zero matches found</p>
                        <p class="text-sm text-slate-400 mt-1 max-w-xs mx-auto">Try refining your search terms or
                            expanding your jurisdictional filters.</p>
                    </div>
                    <flux:button variant="subtle" wire:click="$set('search', '')" class="font-black">Clear Filters
                    </flux:button>
                </div>
            @endforelse
        </div>

        <!-- Skeleton Template -->
        <div wire:loading wire:target="search, filterRole, statusFilter, sortBy" class="divide-y divide-slate-50">
            @for ($i = 0; $i < 6; $i++)
                <div class="px-10 py-8 animate-pulse flex items-center justify-between">
                    <div class="flex items-center gap-8">
                        <div class="w-16 h-16 bg-slate-100 rounded-[1.5rem]"></div>
                        <div class="space-y-4">
                            <div class="h-5 bg-slate-100 rounded w-64"></div>
                            <div class="h-3 bg-slate-50 rounded w-80"></div>
                        </div>
                    </div>
                    <div class="h-12 w-40 bg-slate-50 rounded-2xl"></div>
                </div>
            @endfor
        </div>

        <!-- Infinite Scroll Trigger -->
        @if ($this->users->hasMorePages())
            <div x-intersect="$wire.loadMore()"
                class="p-16 flex flex-col items-center justify-center bg-slate-50/30 gap-6 border-t border-slate-100">
                <flux:button variant="subtle" wire:click="loadMore" wire:loading.attr="disabled"
                    class="!rounded-full !px-8 !py-3 !font-black !text-indigo-600">
                    <span wire:loading.remove wire:target="loadMore">Scroll for More Members</span>
                    <span wire:loading wire:target="loadMore" class="flex items-center gap-3">
                        <flux:icon.arrow-path class="size-5 animate-spin" />
                        Fetching Directory...
                    </span>
                </flux:button>
            </div>
        @endif
    </div>
</div>
