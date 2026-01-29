<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use App\Models\User;
use App\Models\Lane;
use App\Enums\CapacityUnit;
use App\Enums\RateType;
use App\Enums\TrailerType; // Added Enum Import

new class extends Component {
    public $currentStep = 1;

    // Form Properties
    public $availability_date;
    public $origin_country;
    public $origin_city;
    public $destination_country;
    public $destination_city;
    public $trailer_type;

    // Updated for Dynamic Units
    public $available_capacity;
    public $capacity_unit = 'tonnes';
    public $rate;
    public $rate_type = 'flat_rate';

    public $fullName;
    public $email;
    public $phone;
    public $whatsapp;

    // Search and Selection
    public $search = '';
    public $selected_carrier_id;
    public $self = false;
    public $isDraft = false;
    public $zimbabweCities = [];

    #[Locked]
    public $laneId;

    #[Computed]
    public function authorizedCarriers()
    {
        $query = User::whereHas('roles', fn($q) => $q->where('roles.name', 'carrier'))->visibleTo(auth()->user());

        if (auth()->user()->hasRole('carrier')) {
            return $query->where('users.id', auth()->id())->get();
        }

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->whereAny(['organisation', 'contact_person', 'email'], 'like', '%' . $this->search . '%');
            });
        }

        return $query->orderBy('organisation')->limit(20)->get();
    }

    #[Computed]
    public function selectedCarrier()
    {
        return User::find($this->selected_carrier_id);
    }

    public function mount(Lane $lane)
    {
        $user = auth()->user();

        // 1. Authorization Logic
        if ($lane->exists) {
            // Checking Edit Rights
            if ($user->cannot('update', $lane)) {
                $message = 'You must be a <a href="' . route('register') . '" class="text-rose-600 font-bold underline hover:text-rose-500">registered carrier</a> to upload vehicles.';
                abort(403, $message);
            }
        } else {
            // Checking Creation Rights
            if ($user->cannot('create', Lane::class)) {
                abort(403, 'You must be a registered carrier to upload vehicles.');
            }
        }
        $this->zimbabweCities = \App\Models\ZimbabweCity::orderBy('name')->pluck('name', 'name')->toArray();
        $this->availability_date = date('Y-m-d');
        if ($lane->id) {
            $this->laneId = $lane->id;
            $this->selected_carrier_id = $lane->carrier_id;
            $this->availability_date = date('Y-m-d', strtotime($lane->availability_date));
            $this->origin_country = $lane->countryfrom;
            $this->origin_city = $lane->cityfrom;
            $this->destination_country = $lane->countryto;
            $this->destination_city = $lane->cityto;

            /** * UI UPDATE: We store the 'value' (slug) rather than the 'label'
             * to ensure the icon logic and button active states work correctly.
             */
            $this->trailer_type = $lane->trailer?->value;

            // Loading the new fields
            $this->available_capacity = $lane->capacity;
            $this->capacity_unit = $lane->capacity_unit->value;
            $this->rate = $lane->rate;
            $this->rate_type = $lane->rate_type->value;

            $contact = $lane->contacts->first();
            if ($contact) {
                $this->fullName = $contact->full_name;
                $this->email = $contact->email;
                $this->phone = $contact->phone_number;
                $this->whatsapp = $contact->whatsapp;
            }
        } elseif (auth()->user()->hasRole('carrier')) {
            $this->selected_carrier_id = auth()->id();
        }
    }

    public function uploadvehicle()
    {
        $this->validate();

        $data = [
            'carrier_id' => $this->selected_carrier_id,
            'creator_id' => auth()->id(),
            'countryfrom' => $this->origin_country,
            'cityfrom' => $this->origin_city,
            'countryto' => $this->destination_country,
            'cityto' => $this->destination_city,
            'availability_date' => $this->availability_date,

            'trailer' => $this->trailer_type,

            // Capacity Logic
            'capacity' => $this->available_capacity,
            'capacity_unit' => $this->capacity_unit,

            // Rate Logic
            'rate' => $this->rate,
            'rate_type' => $this->rate_type,

            'status' => $this->isDraft ? 'draft' : 'submitted',
        ];
        // MANUALLY ADD UUID FOR NEW RECORDS
        if (!$this->laneId) {
            $data['uuid'] = (string) \Illuminate\Support\Str::uuid();
        }

        $lane = Lane::updateOrCreate(['id' => $this->laneId], $data);
        $lane->contacts()->updateOrCreate(
            ['contactable_id' => $lane->id, 'contactable_type' => Lane::class],
            [
                'full_name' => $this->fullName,
                'email' => $this->email,
                'phone_number' => $this->phone,
                'whatsapp' => $this->whatsapp,
            ],
        );
        return $this->redirectRoute('lanes.show', ['lane' => $lane->uuid]);
    }

    public function selfContact()
    {
        if ($this->self && $this->selected_carrier_id) {
            $carrier = User::find($this->selected_carrier_id);
            $this->email = $carrier->email;
            $this->phone = $carrier->contact_phone;
            $this->whatsapp = $carrier->whatsapp;
            $this->fullName = $carrier->contact_person;
        }
    }

    protected function rules(): array
    {
        return [
            'selected_carrier_id' => ['required', 'exists:users,id'],
            'origin_country' => ['required'],
            'origin_city' => ['required'],
            'destination_country' => ['required'],
            'destination_city' => ['required'],
            'trailer_type' => ['required'],
            'available_capacity' => ['required', 'numeric'],
            'capacity_unit' => ['required', 'string'],
            'availability_date' => ['required', 'date'],
            'rate' => ['required', 'numeric'],
            'rate_type' => ['required', 'string'],
            'fullName' => ['required_if:currentStep,4'],
        ];
    }

    protected function messages()
    {
        return [
            'selected_carrier_id.required' => 'Please select a valid carrier from the search results.',
        ];
    }

    public function nextStep()
    {
        if ($this->currentStep == 1) {
            $this->validate([
                'selected_carrier_id' => ['required', 'exists:users,id'],
                'availability_date' => ['required', 'date', 'afterOrEqual:today'],
                'origin_country' => ['required'],
                'origin_city' => ['required'],
                'destination_country' => ['required'],
                'destination_city' => ['required'],
            ]);
        } elseif ($this->currentStep == 2) {
            $this->validate([
                'trailer_type' => ['required'],
                'available_capacity' => ['required', 'numeric', 'min:1'],
                'capacity_unit' => ['required'],
            ]);
        } elseif ($this->currentStep == 3) {
            $this->validate([
                'rate' => ['required', 'numeric'],
                'rate_type' => ['required'],
            ]);
        }

        if ($this->currentStep < 4) {
            $this->currentStep++;
        }
    }

    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }
}; ?>

<div x-data="{ currentStep: @entangle('currentStep') }" class="p-6 max-w-5xl mx-auto">

    <div class="flex justify-between mb-8">
        @foreach (['Journey', 'Specs', 'Pricing', 'Contact'] as $index => $step)
            <div class="flex flex-col items-center">
                <div
                    class="w-10 h-10 rounded-full flex items-center justify-center font-bold {{ $currentStep >= $index + 1 ? 'bg-cyan-600 text-white' : 'bg-zinc-200 text-zinc-500' }}">
                    {{ $index + 1 }}
                </div>
                <span class="text-xs mt-2 font-medium">{{ $step }}</span>
            </div>
        @endforeach
    </div>

    <div class="bg-white dark:bg-zinc-900 shadow-xl rounded-3xl p-8 border border-zinc-200 dark:border-zinc-800">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form wire:submit.prevent="uploadvehicle">

            <div x-show="currentStep == 1" class="space-y-6">
                @if (auth()->user()->hasRole('carrier'))
                    {{-- Carriers see their own name, no "Change" button --}}
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-white rounded-lg shadow-sm border border-zinc-200">
                            <flux:icon.user class="size-5 text-zinc-400" />
                        </div>
                        <div>
                            <p class="text-[10px] text-zinc-500 uppercase tracking-widest font-bold">Carrier Account</p>
                            <p class="font-bold text-zinc-800">{{ auth()->user()->organisation }}</p>
                        </div>
                    </div>
                @endif
                @if (auth()->user()->hasAnyRole(['admin', 'superadmin', 'procurement logistics associate', 'operations logistics associate']))
                    <div class="space-y-2">
                        <flux:label>Select The Carrier</flux:label>
                        @if ($selected_carrier_id && $this->selectedCarrier)
                            <div
                                class="flex items-center justify-between p-4 border-2 border-cyan-500 bg-cyan-50 dark:bg-cyan-950/20 rounded-2xl text-sm">
                                <div>
                                    <p class="font-bold text-cyan-700 dark:text-cyan-400">
                                        {{ $this->selectedCarrier->organisation }}</p>
                                    <p class="text-xs opacity-70">{{ $this->selectedCarrier->contact_person }} •
                                        {{ $this->selectedCarrier->email }}</p>
                                </div>
                                <flux:button variant="ghost" size="sm"
                                    wire:click="$set('selected_carrier_id', null)">Change</flux:button>
                            </div>
                        @else
                            <div class="relative">
                                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass"
                                    placeholder="Search carrier..." autocomplete="off" />
                                @if (!empty($search) && !$selected_carrier_id)
                                    <div
                                        class="absolute z-10 w-full mt-2 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl shadow-xl max-h-60 overflow-y-auto">
                                        @forelse($this->authorizedCarriers as $carrier)
                                            <button type="button"
                                                wire:click="$set('selected_carrier_id', {{ $carrier->id }}); $set('search', '')"
                                                class="w-full text-left p-3 hover:bg-zinc-100 dark:hover:bg-zinc-700 border-b border-zinc-100 dark:border-zinc-700 last:border-0">
                                                <p class="font-bold text-sm">
                                                    {{ $carrier->organisation ?? 'Individual' }}</p>
                                                <p class="text-xs text-zinc-500">{{ $carrier->contact_person }}</p>
                                            </button>
                                        @empty
                                            <div class="p-4 text-sm text-zinc-500 text-center">No results found</div>
                                        @endforelse
                                    </div>
                                @endif
                            </div>
                        @endif
                        <flux:error name="selected_carrier_id" />
                    </div>
                @endif

                <flux:input type="date" label="Availability Date" wire:model="availability_date" />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:fieldset class="border p-4 rounded-2xl text-sm">
                        <flux:legend>Origin</flux:legend>
                        <flux:select label="Country" wire:model.live="origin_country">
                            <flux:select.option value="zimbabwe">Zimbabwe</flux:select.option>
                            <flux:select.option value="south africa">South Africa</flux:select.option>
                        </flux:select>
                        <div class="mt-4">
                            @if ($origin_country == 'zimbabwe')
                                <flux:select label="City" wire:model="origin_city">
                                    @foreach ($zimbabweCities as $city)
                                        <flux:select.option value="{{ $city }}">{{ $city }}
                                        </flux:select.option>
                                    @endforeach
                                </flux:select>
                            @else
                                <flux:input label="City" wire:model="origin_city" />
                            @endif
                        </div>
                    </flux:fieldset>

                    <flux:fieldset class="border p-4 rounded-2xl text-sm">
                        <flux:legend>Destination</flux:legend>
                        <flux:select label="Country" wire:model.live="destination_country">
                            <flux:select.option value="zimbabwe">Zimbabwe</flux:select.option>
                            <flux:select.option value="south africa">South Africa</flux:select.option>
                        </flux:select>
                        <div class="mt-4">
                            @if ($destination_country == 'zimbabwe')
                                <flux:select label="City" wire:model="destination_city">
                                    @foreach ($zimbabweCities as $city)
                                        <flux:select.option value="{{ $city }}">{{ $city }}
                                        </flux:select.option>
                                    @endforeach
                                </flux:select>
                            @else
                                <flux:input label="City" wire:model="destination_city" />
                            @endif
                        </div>
                    </flux:fieldset>
                </div>
            </div>

            <div x-show="currentStep == 2" class="space-y-6" x-cloak>
                <flux:text class="font-bold text-lg">Trailer Specs</flux:text>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach (TrailerType::cases() as $trailer)
                        <button type="button" wire:click="$set('trailer_type', '{{ $trailer->value }}')"
                            class="p-4 border-2 rounded-2xl transition-all flex flex-col items-center justify-center gap-3 text-sm {{ $trailer_type == $trailer->value ? 'border-cyan-500 bg-cyan-50 dark:bg-cyan-950/20' : 'border-zinc-100 dark:border-zinc-800 hover:border-zinc-200 dark:hover:border-zinc-700' }}">

                            {{-- TRAILER ICON --}}
                            <div
                                class=" {{ $trailer_type == $trailer->value ? 'text-cyan-600 dark:text-cyan-400' : 'text-zinc-400' }}">
                                <x-graphic :name="$trailer->iconName()" class="size-24" />
                            </div>

                            <span class="font-medium">{{ $trailer->label() }}</span>
                        </button>
                    @endforeach
                </div>
                <flux:error name="trailer_type" />

                <div
                    class="p-6 bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700 rounded-3xl space-y-4">
                    <div class="flex items-center justify-between">
                        <flux:label>Measurement Unit</flux:label>
                        <flux:radio.group wire:model.live="capacity_unit" variant="segmented" size="sm">
                            <flux:radio value="tonnes" label="Tonnes" />
                            <flux:radio value="litres" label="Litres" />
                        </flux:radio.group>
                    </div>

                    <flux:input label="Total Capacity" type="number" wire:model="available_capacity"
                        :suffix="is_string($capacity_unit) ? ucfirst($capacity_unit) : $capacity_unit->label()"
                        placeholder="0.00" />

                    <div class="flex items-center gap-2 mt-2">
                        <span class="text-xs text-zinc-500">Selected Mode:</span>
                        <kbd
                            class="px-2 py-1 text-xs font-sans font-semibold text-zinc-800 bg-zinc-100 border border-zinc-300 rounded-md dark:bg-zinc-700 dark:text-zinc-100 dark:border-zinc-600">
                            In {{ is_string($capacity_unit) ? ucfirst($capacity_unit) : $capacity_unit->label() }}
                        </kbd>
                    </div>
                </div>
            </div>

            <div x-show="currentStep == 3" class="space-y-4" x-cloak>
                <flux:text class="font-bold text-lg">Pricing Structure</flux:text>

                <div
                    class="p-6 bg-cyan-50/30 dark:bg-cyan-950/10 border border-cyan-100 dark:border-cyan-900/50 rounded-3xl space-y-6">
                    <flux:radio.group wire:model.live="rate_type" variant="segmented" class="w-full">
                        <flux:radio value="per_km" label="Cost per KM" />
                        <flux:radio value="flat_rate" label="Full Amount" />
                    </flux:radio.group>

                    <div>
                        <flux:input label="Asking Rate" type="number" step="0.01" wire:model="rate"
                            icon="currency-dollar" :suffix="$rate_type === 'per_km' ? 'per km' : 'Total'" />

                        <div class="flex items-center gap-2 mt-3">
                            <span class="text-xs text-zinc-500 uppercase">Input Type:</span>
                            <kbd
                                class="px-2 py-1 text-xs font-semibold text-cyan-700 bg-white border border-cyan-200 rounded shadow-sm dark:bg-zinc-800 dark:text-cyan-400 dark:border-cyan-800">
                                {{ $rate_type === 'per_km' ? '$ per KM' : 'Full Trip Cost' }}
                            </kbd>
                        </div>
                    </div>
                </div>
            </div>

            <div x-show="currentStep == 4" class="space-y-6" x-cloak>
                <div class="bg-zinc-50 dark:bg-zinc-800/50 p-4 rounded-2xl flex justify-between items-center text-sm">
                    <div>
                        <p class="font-bold">Auto-fill Profile Contacts</p>
                        <p class="text-xs text-zinc-500">Pulls info from the selected carrier.</p>
                    </div>
                    <flux:switch wire:model.live="self" wire:click="selfContact" />
                </div>
                <flux:input label="Contact Name" wire:model="fullName" />
                <div class="grid grid-cols-2 gap-4">
                    <flux:input label="Phone" wire:model="phone" />
                    <flux:input label="WhatsApp" wire:model="whatsapp" />
                </div>
                <flux:input label="Email" wire:model="email" />
            </div>

            <div class="mt-12 flex justify-between border-t pt-6 border-zinc-100 dark:border-zinc-800">
                <flux:button variant="ghost" x-show="currentStep > 1" wire:click="previousStep">Back</flux:button>
                <div class="flex gap-4 ml-auto">
                    @if ($currentStep < 4)
                        <flux:button variant="primary" wire:click="nextStep">Continue</flux:button>
                    @else
                        <flux:button type="submit" color="green">Upload Vehicle</flux:button>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>
