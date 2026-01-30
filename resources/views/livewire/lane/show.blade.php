<?php

use Livewire\Volt\Component;
use App\Models\Lane;
use App\Enums\LaneStatus;
use App\Enums\VehiclePositionStatus;
use App\Services\LaneService;
use Livewire\Attributes\Lazy;

new class extends Component {
    public Lane $lane;

    // Properties for inline updates
    public $selectedLaneStatus;
    public $selectedPositionStatus;

    public function mount(Lane $lane)
    {
        $this->authorize('view', $lane);

        $this->lane = $lane->load(['contacts', 'carrier', 'createdBy', 'activityLogs.actor']);

        $this->selectedLaneStatus = $this->lane->status->value ?? $this->lane->status;
        $this->selectedPositionStatus = $this->lane->vehicle_status->value ?? $this->lane->vehicle_status;
    }

    public function updateLaneStatus($status)
    {
        $this->authorize('updateStatus', $this->lane);

        $this->lane->update(['status' => $status]);
        $this->selectedLaneStatus = $status;
        session()->flash('status', 'Lane status updated successfully.');
    }

    public function updatePositionStatus($status)
    {
        $this->authorize('updateStatus', $this->lane);

        if (($this->lane->status->value ?? $this->lane->status) !== LaneStatus::PUBLISHED->value) {
            return;
        }

        $this->lane->update(['vehicle_status' => $status]);
        $this->selectedPositionStatus = $status;
        session()->flash('status', 'Vehicle position updated successfully.');
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div class="p-6 max-w-5xl mx-auto animate-pulse">
            <div class="h-64 w-full bg-zinc-100 dark:bg-zinc-800/50 rounded-3xl mb-8"></div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="md:col-span-2 space-y-6">
                    <div class="h-10 w-3/4 bg-zinc-200 dark:bg-zinc-800 rounded"></div>
                    <div class="h-4 w-full bg-zinc-100 dark:bg-zinc-800 rounded"></div>
                    <div class="h-4 w-5/6 bg-zinc-100 dark:bg-zinc-800 rounded"></div>
                </div>

                <div class="h-48 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl p-6">
                    <div class="h-4 w-full bg-zinc-100 dark:bg-zinc-800 rounded mb-4"></div>
                    <div class="h-10 w-full bg-cyan-100 dark:bg-cyan-900/30 rounded-xl"></div>
                </div>
            </div>
        </div>
        HTML;
    }
}; ?>

<div class="p-6 max-w-6xl mx-auto space-y-8">

    @if (session()->has('status'))
        <div
            class="p-4 text-sm text-emerald-800 rounded-2xl bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/50">
            {{ session('status') }}
        </div>
    @endif

    @if ($lane->status === \App\Enums\LaneStatus::SUBMITTED)
        <div
            class="flex items-center gap-3 p-4 border border-cyan-200 bg-cyan-50 dark:bg-cyan-950/20 rounded-2xl text-cyan-900 dark:text-cyan-200 shadow-sm">
            <flux:icon.information-circle variant="solid" class="size-5 text-cyan-600" />
            <p class="text-sm font-medium">This listing is currently <strong>pending review</strong>. It will be visible
                to users once published.</p>
        </div>
    @endif

    <div
        class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-[2rem] p-4 flex flex-wrap items-center justify-between gap-4 shadow-sm">
        <div class="flex flex-wrap items-center gap-4">
            @can('update', $lane)
                <flux:button icon="pencil-square" variant="filled" size="sm"
                    href="{{ route('lanes.edit', $lane->uuid) }}">
                    Edit Details
                </flux:button>
            @endcan

            @can('updateStatus', $lane)
                <div class="h-6 w-px bg-zinc-200 dark:bg-zinc-700 hidden sm:block"></div>

                <div class="flex items-center gap-2">
                    <flux:select wire:model.live="selectedLaneStatus" wire:change="updateLaneStatus($event.target.value)"
                        size="sm" class="w-40">
                        <x-slot name="label" class="text-[10px] uppercase font-bold text-zinc-400">Lane Status</x-slot>
                        @foreach (LaneStatus::cases() as $status)
                            <option value="{{ $status->value }}">{{ $status->label() }}</option>
                        @endforeach
                    </flux:select>
                </div>

                <div class="flex items-center gap-2">
                    <flux:select wire:model.live="selectedPositionStatus"
                        wire:change="updatePositionStatus($event.target.value)" size="sm" class="w-44"
                        :disabled="($lane->status->value ?? $lane->status) !== \App\Enums\LaneStatus::PUBLISHED->value">
                        <x-slot name="label" class="text-[10px] uppercase font-bold text-zinc-400">Vehicle
                            Position</x-slot>
                        @foreach (VehiclePositionStatus::cases() as $pos)
                            <option value="{{ $pos->value }}">{{ $pos->label() }}</option>
                        @endforeach
                    </flux:select>
                </div>
            </div>
        @endcan

        <div class="pr-2">
            <flux:badge :color="$lane->status->color()" size="sm" class="shadow-sm">
                {{ $lane->status->label() }}
            </flux:badge>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-8">

            <div
                class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-[2rem] p-8 shadow-sm relative overflow-hidden">
                <div class="relative z-10">
                    <div class="flex justify-between items-start mb-10">
                        <div class="flex items-center gap-6">
                            <div class="space-y-1">
                                <flux:label class="text-[10px] uppercase tracking-[0.15em] text-zinc-400 font-bold">
                                    Origin</flux:label>
                                <flux:heading size="xl" class="uppercase">
                                    {{ $lane->cityfrom ?: ($lane->location ?: 'Not Stated') }}
                                </flux:heading>
                                @if ($lane->countryfrom)
                                    <flux:text size="xs"
                                        class="uppercase font-semibold text-cyan-600 tracking-wider">
                                        {{ $lane->countryfrom }}
                                    </flux:text>
                                @endif
                            </div>

                            <div class="pt-6">
                                <flux:icon.arrow-right class="size-6 text-zinc-300" />
                            </div>

                            <div class="space-y-1">
                                <flux:label class="text-[10px] uppercase tracking-[0.15em] text-zinc-400 font-bold">
                                    Destination</flux:label>
                                <flux:heading size="xl" class="uppercase">
                                    {{ $lane->cityto ?: ($lane->destination ?: 'Not Stated') }}
                                </flux:heading>
                                @if ($lane->countryto)
                                    <flux:text size="xs"
                                        class="uppercase font-semibold text-cyan-600 tracking-wider">
                                        {{ $lane->countryto }}
                                    </flux:text>
                                @endif
                            </div>
                        </div>

                        @if (($lane->status->value ?? $lane->status) === \App\Enums\LaneStatus::PUBLISHED->value)
                            <div class="text-right hidden sm:block">
                                <flux:label class="text-[10px] uppercase font-bold text-cyan-600/70">Vehicle Journey
                                </flux:label>
                                <div
                                    class="flex items-center gap-2 font-black text-cyan-600 dark:text-cyan-400 uppercase tracking-tight">
                                    <flux:icon.truck variant="mini" />
                                    {{ $lane->vehicle_status->label() }}
                                </div>
                            </div>
                        @endif
                    </div>

                    <div
                        class="grid grid-cols-2 md:grid-cols-4 gap-y-8 gap-x-4 border-t border-zinc-100 dark:border-zinc-800 pt-8">
                        <div>
                            <flux:label class="text-[10px] uppercase font-bold text-zinc-400 mb-2 block">Trailer Type
                            </flux:label>
                            <div class="flex items-center gap-3">
                                <div
                                    class="p-1.5 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-100 dark:border-zinc-700">
                                    <x-graphic :name="$lane->trailer?->iconName()"
                                        class="size-8 text-zinc-600 dark:text-zinc-300 rotate-3" />
                                </div>
                                <flux:text class="font-bold text-sm leading-tight">
                                    {{ $lane->trailer?->label() ?? 'Standard' }}</flux:text>
                            </div>
                        </div>

                        <div>
                            <flux:label class="text-[10px] uppercase font-bold text-zinc-400 mb-2 block">Availability
                            </flux:label>
                            <flux:text class="font-bold block">
                                {{ \Carbon\Carbon::parse($lane->availability_date)->format('d M Y') }}</flux:text>
                            <flux:text size="xs" class="text-cyan-600 font-medium italic">
                                {{ \Carbon\Carbon::parse($lane->availability_date)->diffForHumans() }}
                            </flux:text>
                        </div>

                        <div>
                            <flux:label class="text-[10px] uppercase font-bold text-zinc-400 mb-2 block">Capacity
                            </flux:label>
                            <flux:text class="font-bold block">{{ number_format($lane->capacity) }} <span
                                    class="text-zinc-500 font-normal">{{ $lane->capacity_unit?->label() ?? 'Tonnes' }}</span>
                            </flux:text>
                        </div>

                        <div>
                            <flux:label class="text-[10px] uppercase font-bold text-zinc-400 mb-2 block">Rate Structure
                            </flux:label>
                            <flux:text class="font-bold block text-emerald-600">
                                ${{ number_format((float) $lane->rate, 2) }}</flux:text>
                            <flux:text size="xs" class="text-zinc-400 uppercase font-medium">
                                {{ str_replace('_', ' ', $lane->rate_type?->value ?? 'Flat Rate') }}</flux:text>
                        </div>
                    </div>
                </div>
            </div>
            @can('updateStatus', $lane)
                <div class="space-y-4">
                    <div class="flex items-center justify-between px-2">
                        <flux:heading size="md">Operational Contacts</flux:heading>
                        <flux:text size="xs" class="text-zinc-400 uppercase tracking-widest">
                            {{ $lane->contacts->count() }} Records</flux:text>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @forelse($lane->contacts as $contact)
                            <div x-data="{ open: false }"
                                class="group bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl p-6 hover:border-cyan-500/50 transition-all duration-300">
                                <div class="flex justify-between items-start">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="size-10 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-400 font-bold text-sm">
                                            {{ substr($contact->full_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <flux:text class="font-bold text-base block leading-tight">
                                                {{ $contact->full_name }}</flux:text>
                                            <flux:text size="xs" class="text-cyan-600 font-medium">
                                                {{ $contact->type ?? 'Contact' }}</flux:text>
                                        </div>
                                    </div>
                                    <button @click="open = !open"
                                        class="p-2 bg-zinc-50 dark:bg-zinc-800 rounded-xl group-hover:bg-cyan-50 dark:group-hover:bg-cyan-950/30 transition-colors">
                                        <flux:icon.chevron-down variant="micro" ::class="open ? 'rotate-180' : ''"
                                            class="size-4 text-zinc-400 transition-transform duration-300" />
                                    </button>
                                </div>

                                <div class="mt-6 grid grid-cols-1 gap-3">
                                    @if ($contact->phone_number)
                                        <a href="tel:{{ $contact->phone_number }}"
                                            class="flex items-center gap-3 p-2 rounded-xl hover:bg-zinc-50 dark:hover:bg-zinc-800 text-sm font-medium transition-colors">
                                            <flux:icon.phone variant="micro" class="size-4 text-zinc-400" />
                                            {{ $contact->phone_number }}
                                        </a>
                                    @endif
                                    @if ($contact->email)
                                        <a href="mailto:{{ $contact->email }}"
                                            class="flex items-center gap-3 p-2 rounded-xl hover:bg-zinc-50 dark:hover:bg-zinc-800 text-sm font-medium transition-colors">
                                            <flux:icon.envelope variant="micro" class="size-4 text-zinc-400" />
                                            {{ $contact->email }}
                                        </a>
                                    @endif
                                </div>

                                <div x-show="open" x-collapse x-cloak>
                                    <div class="mt-4 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                                        <flux:label class="text-[9px] uppercase font-bold text-zinc-400">Current Base /
                                            Address</flux:label>
                                        <flux:text size="xs"
                                            class="mt-2 block leading-relaxed text-zinc-600 dark:text-zinc-400">
                                            {{ $contact->address ?: 'No physical address provided.' }}<br>
                                            <span
                                                class="font-bold text-zinc-800 dark:text-zinc-200">{{ $contact->city }}{{ $contact->country ? ', ' . $contact->country : '' }}</span>
                                        </flux:text>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div
                                class="md:col-span-2 py-12 bg-zinc-50/50 dark:bg-zinc-800/30 border border-dashed border-zinc-200 dark:border-zinc-800 rounded-[2rem] text-center">
                                <flux:text class="italic text-zinc-400">No personnel linked to this trip.</flux:text>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endcan
        </div>

        <div class="space-y-6">
            @can('updateStatus', $lane)
                <div class="bg-zinc-900 dark:bg-white p-8 rounded-[2rem] text-white dark:text-zinc-900 shadow-xl">
                    <flux:label class="text-[10px] uppercase tracking-widest text-zinc-400">Carrier</flux:label>
                    @if ($lane->carrier)
                        <div class="mt-4">
                            <a href="{{ route('users.show', $lane->carrier->slug) }}"
                                class="font-bold text-xl hover:text-cyan-400 transition-colors">
                                {{ $lane->carrier->organisation }}
                            </a>
                            <flux:text size="sm" class="text-zinc-400 dark:text-zinc-500 mt-1 block">
                                {{ $lane->carrier->contact_person }}</flux:text>
                        </div>
                    @endif

                    <div class="mt-10 pt-8 border-t border-white/10 dark:border-zinc-100 grid grid-cols-1 gap-6">
                        <div>
                            <flux:label class="text-[9px] uppercase text-zinc-400">Registration</flux:label>
                            <flux:text class="font-mono font-bold text-lg tracking-tighter">
                                {{ $lane->regno ?? '--- ---' }}</flux:text>
                        </div>
                        <div>
                            <flux:label class="text-[9px] uppercase text-zinc-400">Internal Reference</flux:label>
                            <flux:text class="text-sm font-medium">#LN-{{ str_pad($lane->id, 5, '0', STR_PAD_LEFT) }}
                            </flux:text>
                        </div>
                    </div>
                </div>
            @endcan
            @can('viewActivityLog', $lane)
                <div
                    class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-[2rem] p-8 shadow-sm">
                    <flux:heading size="sm" class="mb-6 uppercase tracking-widest text-zinc-400 text-[10px]">Activity
                        Log</flux:heading>

                    <div
                        class="space-y-10 relative before:absolute before:inset-0 before:ml-[11px] before:w-0.5 before:bg-zinc-100 dark:before:bg-zinc-800">
                        @forelse($lane->activityLogs->sortByDesc('created_at') as $log)
                            <div class="relative pl-8">
                                {{-- Timeline Dot --}}
                                <div @class([
                                    'absolute left-0 top-1 size-[22px] rounded-full border-4 border-white dark:border-zinc-900 shadow-sm z-10',
                                    'bg-emerald-500' => $log->event === 'created',
                                    'bg-amber-500' => $log->event === 'updated',
                                    'bg-red-500' => $log->event === 'deleted',
                                ])></div>

                                <div class="flex flex-col gap-3">
                                    {{-- Header --}}
                                    <div>
                                        <flux:text class="font-black text-sm uppercase tracking-tight">
                                            {{ $log->event }}
                                            <span class="text-zinc-400 font-medium lowercase mx-1">by</span>
                                            <span
                                                class="text-zinc-900 dark:text-white">{{ $log->actor->contact_person ?? 'System' }}</span>
                                        </flux:text>
                                        <flux:text size="xs" class="text-zinc-400">
                                            {{ $log->created_at->format('M d, Y @ H:i') }}
                                        </flux:text>
                                    </div>

                                    {{-- Data Flesh: Iterating through Field-based Keys --}}
                                    @if (!empty($log->payload))
                                        <div
                                            class="bg-zinc-50 dark:bg-zinc-800/50 rounded-2xl border border-zinc-100 dark:border-zinc-800 overflow-hidden">
                                            <table class="w-full text-left border-collapse">
                                                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                                    @foreach ($log->payload as $field => $values)
                                                        @php
                                                            // Handle cases where some payload items might not follow the old/new pattern
                                                            $old = $values['old'] ?? '---';
                                                            $new = $values['new'] ?? '---';
                                                        @endphp

                                                        <tr>
                                                            <td
                                                                class="px-4 py-2 text-[10px] font-mono font-bold text-zinc-400 uppercase w-1/4">
                                                                {{ str_replace('_', ' ', $field) }}
                                                            </td>
                                                            <td class="px-4 py-2 w-1/3">
                                                                <span class="text-[10px] text-zinc-400 line-through">
                                                                    {{ is_array($old) ? 'Data' : $old }}
                                                                </span>
                                                            </td>
                                                            <td class="px-4 py-2">
                                                                <div class="flex items-center gap-2">
                                                                    <flux:icon.arrow-right variant="micro"
                                                                        class="size-2 text-zinc-300" />
                                                                    <span class="text-[10px] text-emerald-600 font-black">
                                                                        {{ is_array($new) ? 'Data' : $new }}
                                                                    </span>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <flux:text class="italic text-zinc-400 px-2">No activity history found for this lane.
                            </flux:text>
                        @endforelse
                    </div>
                </div>
            @endcan
        </div>
    </div>
</div>
