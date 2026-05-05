<?php

use Livewire\Volt\Component;
use App\Models\Freight;
use App\Enums\FreightStatus;

new class extends Component {
    public Freight $freight;
    public $selectedFreightStatus;

    public function mount(Freight $freight)
    {
        $this->freight = $freight->load(['shipper', 'creator', 'activityLogs', 'contacts']);
    }

    public function updateFreightStatus($status)
    {
        $this->authorize('updateStatus', $this->freight);
        $this->freight->update(['status' => $status]);
        $this->selectedFreightStatus = $status;
        session()->flash('status', 'Freight status updated successfully.');
    }
}; ?>

<div class="p-6 lg:p-12 max-w-7xl mx-auto">
    @if (session()->has('status'))
        <div
            class="p-4 text-sm text-emerald-800 rounded-2xl bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/50">
            {{ session('status') }}
        </div>
    @endif
    @if ($freight->status == \App\Enums\FreightStatus::SUBMITTED)
        <div
            class="my-2 flex items-center gap-3 p-4 border border-cyan-200 bg-cyan-50 dark:bg-cyan-950/20 rounded-2xl text-cyan-900 dark:text-cyan-200 shadow-sm">

            <flux:icon.information-circle variant="solid" class="size-5 text-cyan-600" />
            <p class="text-sm font-medium">
                This freight listing is currently <strong>pending review</strong>. It will be visible to users once
                published.
            </p>
        </div>
    @endif
    <div class="flex flex-wrap items-center gap-4 my-4">
        <div class="h-6 w-px bg-zinc-200 dark:bg-zinc-700 hidden sm:block"></div>

        <div class="flex items-center gap-2">
            @can('update', $freight)
                <flux:button icon="pencil-square" variant="filled" size="sm"
                    href="{{ route('freights.edit', $freight->uuid) }}">
                    Edit Details
                </flux:button>
            @endcan
            @can('updateStatus', $freight)
                <flux:select wire:model.live="selectedFreightStatus" wire:change="updateFreightStatus($event.target.value)"
                    size="sm" class="w-40">
                    <x-slot name="label" class="text-[10px] uppercase font-bold text-zinc-400">Freight Status</x-slot>
                    @foreach (FreightStatus::cases() as $status)
                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                    @endforeach
                </flux:select>
            @endcan
        </div>
    </div>
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <flux:heading size="xl">{{ $freight->name }}</flux:heading>
                <flux:badge :color="$freight->status->color()" variant="pill">
                    {{ $freight->status->label() }}
                </flux:badge>
                @if ($freight->shipment_status && $freight->shipment_status->value !== 'inapplicable')
                    <flux:badge color="blue" variant="outline">{{ $freight->shipment_status->value }}</flux:badge>
                @endif
            </div>
            <flux:subheading>Added {{ $freight->created_at->diffForHumans() }}</flux:subheading>
        </div>

        <div class="flex gap-2">
            <flux:button icon="printer" variant="ghost">Print</flux:button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Left Column: Route & Cargo --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Route Card --}}
            <section
                class="bg-white dark:bg-zinc-900 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
                <div class="flex items-center gap-2 mb-6 text-cyan-600">
                    <flux:icon.map-pin variant="mini" />
                    <span class="font-bold uppercase tracking-widest text-xs">Transit Route</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 relative">
                    <div class="space-y-1">
                        <flux:text size="sm" class="uppercase font-semibold text-zinc-400">From</flux:text>
                        <flux:heading size="lg">{{ $freight->cityfrom }}, {{ $freight->countryfrom }}
                        </flux:heading>
                        <flux:text class="text-zinc-500">{{ $freight->pickup_address }}</flux:text>
                        <flux:text size="sm" class="mt-2 block">
                            <flux:icon.calendar variant="micro" class="mr-1 inline text-zinc-400" />
                            Starts: {{ $freight->datefrom->format('d M Y, H:i') }}
                            <span class="text-zinc-400">({{ $freight->datefrom->diffForHumans() }})</span>
                        </flux:text>
                    </div>

                    <div class="space-y-1">
                        <flux:text size="sm" class="uppercase font-semibold text-zinc-400">To</flux:text>
                        <flux:heading size="lg">{{ $freight->cityto }}, {{ $freight->countryto }}</flux:heading>
                        <flux:text class="text-zinc-500">{{ $freight->delivery_address }}</flux:text>
                        <flux:text size="sm" class="mt-2 block">
                            <flux:icon.calendar variant="micro" class="mr-1 inline text-zinc-400" />
                            Ends: {{ $freight->dateto->format('d M Y, H:i') }}
                            <span class="text-zinc-400">({{ $freight->dateto->diffForHumans() }})</span>
                        </flux:text>
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t border-dashed border-zinc-200 dark:border-zinc-800 flex justify-between">
                    <flux:text>Estimated Distance: <strong>{{ $freight->distance ?? 'Not calculated' }} km</strong>
                    </flux:text>
                    <flux:text>Travel Time:
                        <strong>{{ $freight->datefrom->diffForHumans($freight->dateto, true) }}</strong>
                    </flux:text>
                </div>
            </section>

            {{-- Cargo Details --}}
            <section class="bg-white dark:bg-zinc-900 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800">
                <div class="flex items-center gap-2 mb-6 text-cyan-600">
                    <flux:icon.cube variant="mini" />
                    <span class="font-bold uppercase tracking-widest text-xs">Cargo Details</span>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                    <div>
                        <flux:label>Weight</flux:label>
                        <div class="font-semibold text-zinc-900 dark:text-white">
                            {{ $freight->weight }} {{ $freight->capacity_unit }}
                        </div>
                    </div>

                    <div>
                        <flux:label>Vehicle Type</flux:label>
                        <div class="font-semibold text-zinc-900 dark:text-white">
                            {{ $freight->vehicle_type ?? 'Any' }}
                        </div>
                    </div>

                    <div>
                        <flux:label>Hazardous</flux:label>
                        <div class="mt-1">
                            <flux:badge :color="$freight->is_hazardous ? 'red' : 'zinc'" size="sm">
                                {{ $freight->is_hazardous ? 'Yes' : 'No' }}
                            </flux:badge>
                        </div>
                    </div>

                </div>

                <div class="mt-8">
                    <flux:label>Description</flux:label>
                    <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed mt-1">
                        {{ $freight->description }}
                    </p>
                </div>
            </section>
            @can('updateStatus', $freight)
                <div class="flex items-center justify-between px-2">
                    <flux:heading size="md">Operational Contacts</flux:heading>
                    <flux:text size="xs" class="text-zinc-400 uppercase tracking-widest">
                        {{ $freight->contacts->count() }} Records</flux:text>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse($freight->contacts as $contact)
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
                            <flux:text class="italic text-zinc-400">No personnel linked to this freight</flux:text>
                        </div>
                    @endforelse
                </div>
            @endcan
        </div>

        {{-- Right Column: Stakeholders & Pricing --}}
        <div class="space-y-6">
            {{-- Budget Card --}}
            <div class="bg-cyan-600 rounded-xl p-6 text-white shadow-lg shadow-cyan-200 dark:shadow-none">
                <flux:text class="text-cyan-100 uppercase text-xs font-bold tracking-widest">
                    {{ $freight->payment_option->label() }}
                </flux:text>
                <div class="text-3xl font-bold mt-1">
                    US${{ number_format((float) $freight->carriage_rate, 2) }}
                    <span class="text-lg font-normal text-cyan-100">
                        {{ $freight->payment_option->value == 'rate_of_carriage' ? ' $/km' : 'total' }}</span>
                </div>
            </div>

            {{-- Creator Card --}}
            @can('updateStatus', $freight)
                <section class="bg-white dark:bg-zinc-900 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800">
                    <flux:text class="uppercase text-xs font-bold text-zinc-400 mb-4 block">Created By</flux:text>
                    <div class="flex items-center gap-3 mb-4">
                        <flux:avatar initials="{{ substr($freight->creator->contact_person, 0, 1) }}" />
                        <div>
                            <a href="{{ route('users.show', $freight->creator->slug) }}"
                                class="font-bold hover:underline block text-zinc-900 dark:text-white">
                                {{ $freight->creator->contact_person }}
                            </a>
                            <flux:text size="sm">Internal Staff</flux:text>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <flux:button variant="ghost" size="sm" class="w-full justify-start" icon="envelope"
                            :href="'mailto:'.$freight->creator->email">
                            {{ $freight->creator->email }}
                        </flux:button>
                        <flux:button variant="ghost" size="sm" class="w-full justify-start" icon="phone"
                            :href="'tel:'.$freight->creator->contact_phone">
                            {{ $freight->creator->contact_phone }} ({{ $freight->creator->phone_type ?? 'Mobile' }})
                        </flux:button>
                    </div>
                </section>
            @endcan

            {{-- Shipper Card --}}
            @can('viewSomeShipperDetails', \App\Models\Freight::class)
                @if ($freight->shipper)
                    <section
                        class="bg-zinc-50 dark:bg-zinc-800/50 p-6 rounded-xl border border-zinc-200 dark:border-zinc-700">
                        <flux:text class="uppercase text-xs font-bold text-zinc-400 mb-4 block">Shipper / Client
                        </flux:text>

                        @can('viewAllShipperDetails', $freight)
                            {{-- 1. Full Shipper Details (Visible to Staff in Scope or the Owner) --}}
                            <div class="space-y-4">
                                <div>
                                    <a href="{{ route('users.show', $freight->shipper->slug) }}" class="group block">
                                        <flux:heading size="sm" class="group-hover:text-cyan-600 transition-colors">
                                            {{ $freight->shipper->organisation }}
                                        </flux:heading>
                                    </a>
                                    <flux:text size="xs">Contact: {{ $freight->shipper->contact_person }}</flux:text>
                                </div>

                                <div class="flex flex-col gap-2">
                                    <flux:link :href="'mailto:'.$freight->shipper->email" icon="envelope" size="sm"
                                        class="text-zinc-600">
                                        {{ $freight->shipper->email }}
                                    </flux:link>

                                    <flux:link :href="'tel:'.$freight->shipper->contact_phone" icon="phone" size="sm"
                                        class="text-zinc-600">
                                        {{ $freight->shipper->contact_phone }}
                                    </flux:link>

                                    @if ($freight->shipper->whatsapp)
                                        <flux:link
                                            :href="'https://wa.me/' . preg_replace('/[^0-9]/', '', $freight->shipper->whatsapp)"
                                            target="_blank" icon="chat-bubble-left-right" size="sm"
                                            class="text-green-600 dark:text-green-500">
                                            WhatsApp
                                        </flux:link>
                                    @endif
                                </div>
                            </div>
                        @else
                            {{-- 2. Restricted View (Visible to everyone else) --}}
                            <div
                                class="p-4 bg-zinc-100 dark:bg-zinc-800/50 rounded-lg border border-dashed border-zinc-300 dark:border-zinc-700">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-white dark:bg-zinc-700 rounded-full shadow-sm">
                                        <flux:icon.lock-closed variant="micro" class="text-zinc-400" />
                                    </div>
                                    <div>
                                        <flux:text size="xs" class="uppercase font-bold tracking-tight text-zinc-400">
                                            Shipper ID</flux:text>
                                        <flux:heading size="sm">
                                            #{{ $freight->shipper?->identification_number }}</flux:heading>
                                    </div>
                                </div>
                                <flux:text size="xs" class="mt-3 italic">Contact information is restricted to authorised
                                    logistics associates.</flux:text>
                            </div>
                        @endcan
                    </section>
                @endif
            @endcan

            {{-- Activity Log / Timeline --}}
            @can('viewFreightActivityLog')
                <section class="mt-12">
                    <div class="flex items-center gap-2 mb-6">
                        <flux:icon.clock variant="mini" class="text-zinc-400" />
                        <flux:heading size="lg">Activity Log</flux:heading>
                    </div>

                    <div
                        class="relative space-y-8 before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-zinc-200 before:to-transparent dark:before:via-zinc-800">

                        <div
                            class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                            {{-- The Dot --}}
                            <div
                                class="flex items-center justify-center w-10 h-10 rounded-full border border-white dark:border-zinc-900 bg-zinc-100 dark:bg-zinc-800 text-zinc-500 shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2">
                                <flux:icon.plus variant="micro" />
                            </div>
                            {{-- Content Card --}}
                            <div
                                class="w-[calc(100%-4rem)] md:w-[45%] p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-sm">
                                <div class="flex items-center justify-between space-x-2 mb-1">
                                    <div class="font-bold text-zinc-900 dark:text-white">Freight Created</div>
                                    <time
                                        class="text-xs font-medium text-cyan-600">{{ $freight->created_at->format('d M, H:i') }}</time>
                                </div>
                                <div class="text-zinc-500 text-sm">
                                    Entry created by <span
                                        class="font-medium text-zinc-700 dark:text-zinc-300">{{ $freight->creator->contact_person }}</span>
                                </div>
                            </div>
                        </div>

                        @if ($freight->status->value !== 'draft')
                            <div
                                class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group">
                                <div
                                    class="flex items-center justify-center w-10 h-10 rounded-full border border-white dark:border-zinc-900 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2">
                                    <flux:icon.check variant="micro" />
                                </div>
                                <div
                                    class="w-[calc(100%-4rem)] md:w-[45%] p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-sm">
                                    <div class="flex items-center justify-between space-x-2 mb-1">
                                        <div class="font-bold text-zinc-900 dark:text-white">Status Updated</div>
                                        <time
                                            class="text-xs font-medium text-cyan-600">{{ $freight->updated_at->format('d M, H:i') }}</time>
                                    </div>
                                    <div class="text-zinc-500 text-sm">
                                        Marked as <flux:badge size="sm" :color="$freight->status->color()">
                                            {{ $freight->status->label() }}</flux:badge>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($freight->shipment_status && $freight->shipment_status->value !== 'inapplicable')
                            <div
                                class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group">
                                <div
                                    class="flex items-center justify-center w-10 h-10 rounded-full border border-white dark:border-zinc-900 bg-blue-100 dark:bg-blue-900/30 text-blue-600 shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2">
                                    <flux:icon.truck variant="micro" />
                                </div>
                                <div
                                    class="w-[calc(100%-4rem)] md:w-[45%] p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-sm">
                                    <div class="flex items-center justify-between space-x-2 mb-1">
                                        <div class="font-bold text-zinc-900 dark:text-white">Logistics Update</div>
                                        <time class="text-xs font-medium text-cyan-600">Active</time>
                                    </div>
                                    <div class="text-zinc-500 text-sm">
                                        Current Shipment State: <span
                                            class="capitalize font-semibold text-blue-600">{{ $freight->shipment_status->value }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>
                </section>
            @endcan
        </div>
    </div>
</div>
