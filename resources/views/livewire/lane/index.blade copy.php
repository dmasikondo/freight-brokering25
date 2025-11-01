<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use App\Models\Lane;

new class extends Component {
    #[Locked]
    public $freightId;

    #[Computed]
    public function getLanes()
    {
        return Lane::orderBy('updated_at')
            ->with(['contacts', 'createdBy'])
            ->get();
    }

    public function deleteLane($laneId)
    {
        $this->laneId = $laneId;
        $lane = Lane::findOrFail($this->laneId);
        $lane->delete();
        session()->flash('message', 'The VEHICLE was successfully deleted');
    }
}; ?>

<div class="p-4 sm:p-8 max-w-7xl mx-auto">
    <h1 class="text-3xl font-bold mb-2">Available Vehicles Needing Loads</h1>
    <p class="text-gray-400 mb-8">Browse the list of available carriage capacity for immediate or future bookings.</p>
    @if (session()->has('message'))
        <div class="my-2 toast toast-top toast-center">
            <flux:callout icon="check" color='green'>
                <flux:callout.heading>Vehicle Upload!</flux:callout.heading>
                <flux:callout.text color='green'>
                    {{ session('message') }}
                </flux:callout.text>
            </flux:callout>
        </div>
    @endif
    @if ($this->getLanes->isEmpty())
        <flux:callout icon="face-frown">
            <flux:callout.heading>No Vehicles</flux:callout.heading>
            <flux:callout.text>
                No Vehicles created yet!
            </flux:callout.text>
        </flux:callout>
    @else
        <!-- Vehicle Listing Grid -->
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($this->getLanes as $lane)
                <!-- Vehicle Card  -->
                <div class="relative card  p-6 rounded-xl shadow-lg border border-gray-700 text-gray-500">
                    <x-card.feature-card :feature="[
                        'color' => $lane->status->color(),
                        'title' => $lane->trailer,
                        'icon' => 'truck',
                        'description' => '',
                    ]">
                        @auth()
                            <div class="mr-2 absolute top-0 right-0">
                                <flux:dropdown position="right" align="end">
                                    <flux:button icon:trailing="ellipsis-horizontal" />

                                    <flux:navmenu>
                                        <flux:navmenu.item href="{{ route('lanes.edit', ['lane' => $lane->id]) }}"
                                            icon="pencil-square">Edit Vehicle</flux:navmenu.item>
                                        <flux:navmenu.item icon="trash" variant="danger"
                                            wire:click="deleteLane('{{ $lane->id }}')"
                                            wire:confirm.prompt="Are you sure you want to delete the lane: {{ strtoupper($lane->name) }}? \n\nType REMOVE to confirm your  action|REMOVE">
                                            Remove Vehicle
                                        </flux:navmenu.item>
                                    </flux:navmenu>
                                </flux:dropdown>
                            </div>
                        @endauth

                        <div class="my-4  mb-4 pb-2 ">
                            <flux:badge size='sm' :color="$lane->status->color()">{{ $lane->status->label() }}
                            </flux:badge>



                            {{-- <span class="text-2xl">🚛</span>  --}}
                            {{-- <p class="text-2xl font-semibold text-blue-400 mr-2">
                                {{ $lane->trailer }}
                            </p> --}}

                        </div>
                        <div class="my-2">
                            <flux:separator text="{{ $lane->capacity }}" />
                        </div>
                        <div class="my-2 w-full bg-gray-100 bg-gradient-to-bl from-gray-300 rounded-b-md">
                            <x-graphic :name="$lane->trailer->iconName()" class="w-24 h-18" />
                        </div>

                        <div class="space-y-3 text-sm">
                            <div class="flex items-start gap-2">
                                <span class="text-green-400 mt-0.5">📍</span>
                                <div>
                                    <p class="font-bold">Route:</p>
                                    {{-- <p class="">{{ "{$lane->countryfrom}, {$lane->cityfrom}" }} ➡️
                                        {{ "{$lane->countryto}, {$lane->cityto}" }}</p> --}}
                                    <p class="">
                                        @if ($lane->countryfrom && $lane->cityfrom && $lane->countryto && $lane->cityto)
                                            {{ "{$lane->countryfrom}, {$lane->cityfrom}" }} ➡️
                                            {{ "{$lane->countryto}, {$lane->cityto}" }}
                                        @else
                                            {{ "{$lane->location}, {$lane->destination}" }}
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-2">
                                <span class="text-yellow-400 mt-0.5">📦</span>
                                <div>
                                    <p class="font-bold">Available Capacity:</p>
                                    <p class="">{{ $lane->capacity }} tonnes</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-2">
                                <span class="text-indigo-400 mt-0.5">📅</span>
                                <div>
                                    <p class="font-bold">Available From:</p>
                                    <p class="">{{ date('d M, Y', strtotime($lane->updated_at)) }}
                                        <span class="text-xs"> ({{ $lane->updated_at->diffForHumans() }}
                                            )</span>
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-2">
                                <span class="text-cyan-400 mt-0.5">💲</span>
                                <div>
                                    <p class="font-bold">Requested Rate:</p>
                                    <p class="">{{ $lane->rate }}</p>
                                </div>
                            </div>
                            @auth()
                                <!-- Creator/Company Details -->
                                <div class="pt-3 mt-4 border-t border-gray-600 space-y-3">
                                    <div class="flex items-start gap-2">
                                        <span class="text-pink-400 mt-0.5">🏢</span>
                                        <div>
                                            <p class="font-bold">Posted By:</p>
                                            <p class="">{{ $lane->createdBy?->contact_person }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Contact Person Details -->
                                <div class="pt-3 space-y-3">
                                    <div class="flex items-start gap-2">
                                        <span class="text-orange-400 mt-0.5">👤</span>
                                        <div>
                                            <p class="font-bold">Contact Person:</p>
                                            <p class="">{{ $lane->contacts->first()?->full_name }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <span class="text-teal-400 mt-0.5">📞</span>
                                        <div>
                                            <p class="font-bold">Phone:</p>
                                            <p class="">{{ $lane->contacts->first()?->phone_number }}</p>
                                        </div>
                                        <div>
                                            <p class="font-bold">Whatsapp:</p>
                                            <p class="">{{ $lane->contacts->first()?->whatsapp }}</p>
                                        </div>
                                    </div>
                                    <div class="my-2">
                                        <span class="text-teal-400 mt-0.5">&#128233;</span>
                                        <span>{{ $lane->contacts->first()?->email }}</span>
                                    </div>
                                </div>
                            @endauth

                        </div>

                        <flux:button type="submit" variant="primary" class="w-full">
                            {{ __(' View Details & Contact') }}
                        </flux:button>
                    </x-card.feature-card>

                </div>
            @endforeach

        </div>


    @endif
    <!-- End Vehicle Listing Grid -->

    <div class="mt-10 text-center text-gray-500">
        <p>End of current vehicle listings. Check back later for updates.</p>
    </div>
</div>
