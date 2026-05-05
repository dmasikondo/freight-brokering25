<?php

use Livewire\Volt\Component;
use App\Models\Good;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use App\Models\User;
use App\Models\Freight;
use App\Models\Trailer;
use App\Models\ZimbabweCity;
use App\Enums\FreightStatus;
use App\Services\FreightService;

new class extends Component {
    public $currentStep = 1;
    public $category;
    public $goods;
    public $description;
    public $unitType;
    public $unit;
    public $quantity;
    public $originCountry;
    public $zimbabweCities = [];
    public $originCity;
    public $originAddress;
    public $destinationCountry;
    public $destinationCity;
    public $destinationAddress;
    public $hazardous = false;
    public $distance;
    public $paymentOption;
    public $carriageRate;
    public $selectedTrailer;
    public $pickupDate;
    public $deliveryDate;
    public $fullName;
    public $email;
    public $phone;
    public $whatsapp;
    public $self = false;
    public $isDraft = false;
    public $amount;
    public $badgeStatusColor;
    public $badgeStatusLabel;
    public $status = FreightStatus::DRAFT;

    public $shipper_id;
    public $shipperSearch = '';
    public $showShipperResults = false;

    #[Locked]
    public $freightId;

    #[Computed]
    public function categories()
    {
        return Good::orderBy('name')->get();
    }

    #[Computed]
    public function trailers()
    {
        return Trailer::orderBy('name')->get();
    }

    public function setQuantityUnit()
    {
        if ($this->unitType == 'weight') {
            $this->unit = 'tonnes';
        } else {
            $this->unit = 'litres';
        }
    }

    public function selfContact()
    {
        if ($this->self == true) {
            $selfContact = User::select('email', 'contact_phone', 'whatsapp', 'contact_person')
                ->where('id', auth()->user()->id)
                ->firstOrFail();
            $this->email = $selfContact->email;
            $this->phone = $selfContact->contact_phone;
            $this->whatsapp = $selfContact->whatsapp;
            $this->fullName = $selfContact->contact_person;
        } else {
            if (!$this->freightId) {
                $this->reset(['email', 'phone', 'whatsapp', 'fullName']);
            }
        }
    }

    protected function rules(): array
    {
        $validatedSixStages = [
            'category' => ['required', 'string', 'max:255'],
            'goods' => ['required', 'string', 'max:255'],
            'hazardous' => ['nullable'],
            'unitType' => ['required'],
            'quantity' => ['required'],
            'description' => ['required'],
            'originCountry' => ['required', 'string', 'max:255'],
            'originCity' => ['required', 'string', 'max:255'],
            'originAddress' => ['required', 'string', 'max:255'],
            'destinationCountry' => ['required', 'string', 'max:255'],
            'destinationCity' => ['required', 'string', 'max:255'],
            'destinationAddress' => ['required', 'string', 'max:255'],
            'distance' => ['sometimes'],
            'paymentOption' => ['required'],
            'carriageRate' => ['required'],
            'selectedTrailer' => ['nullable'],
            'pickupDate' => ['required', 'date', 'afterOrEqual:today'],
            'deliveryDate' => ['required', 'date', 'afterOrEqual:pickupDate'],
        ];

        $validatedSeventhStage = [
            'fullName' => ['required'],
            'phone' => ['required_without_all:whatsapp,email'],
            'whatsapp' => ['nullable'],
            'email' => ['nullable'],
        ];

        $validatedAllStages = array_merge($validatedSixStages, $validatedSeventhStage);

        if ($this->currentStep == 6) {
            return $validatedSixStages;
        }

        return $validatedAllStages;
    }

    public function selectShipper($id, $name)
    {
        $this->shipper_id = $id;
        $this->shipperSearch = $name;
        $this->showShipperResults = false;
        $this->autoFillContact(User::find($id));
    }

    private function autoFillContact($user)
    {
        $this->fullName = $user->contact_person;
        $this->email = $user->email;
        $this->phone = $user->contact_phone;
        $this->whatsapp = $user->whatsapp;
    }

    public function saveDraft()
    {
        $this->isDraft = true;
        $this->uploadFreight();
    }

    public function submit()
    {
        $this->isDraft = false;
        $this->uploadFreight();
    }

    public function uploadFreight()
    {
        $validated = $this->validate();
        $validated['name'] = $validated['goods'];
        $validated['is_hazardous'] = $validated['hazardous'];
        $validated['weight'] = $validated['quantity'];
        $validated['countryfrom'] = $validated['originCountry'];
        $validated['cityfrom'] = $validated['originCity'];
        $validated['pickup_address'] = $validated['originAddress'];
        $validated['countryto'] = $validated['destinationCountry'];
        $validated['cityto'] = $validated['destinationCity'];
        $validated['delivery_address'] = $validated['destinationAddress'];
        $validated['payment_option'] = $validated['paymentOption'];
        $validated['carriage_rate'] = $validated['carriageRate'];
        $validated['datefrom'] = $validated['pickupDate'];
        $validated['dateto'] = $validated['deliveryDate'];
        $validated['capacity_unit'] = $validated['unitType'];
        $validated['vehicle_type'] = $validated['selectedTrailer'];

        $this->isDraft ? ($validated['status'] = 'draft') : ($validated['status'] = 'submitted');

        $validated['shipper_id'] = $this->shipper_id;

        $categoryId = Good::select('id')->whereName($validated['category'])->first();
        $freight = auth()
            ->user()
            ->freights()
            ->updateOrCreate(['id' => $this->freightId], $validated);

        $freight->goods()->sync($categoryId->id);
        $this->freightId = $freight->id;

        if ($this->currentStep == 7) {
            $validatedContacts['full_name'] = $validated['fullName'];
            $validatedContacts['phone_number'] = $validated['phone']; // fixed: was fullName
            $validatedContacts['whatsapp'] = $validated['whatsapp'];
            $validatedContacts['email'] = $validated['email'];
            $freight->contacts()->updateOrCreate(['contactable_id' => $this->freightId], $validatedContacts);
        }

        $sessionMessage = $this->freightId ? 'Freight successfully updated' : 'Freight successfully uploaded';

        session()->flash('message', $sessionMessage);
        return $this->redirectRoute('freights.show', ['freight' => $freight->uuid]);
    }

    public function mount(Freight $freight)
    {
        // Gate: only permitted roles may reach this component
        abort_unless(auth()->user()->hasAnyRole(FreightService::FREIGHT_CREATOR_ROLES), 403, 'You do not have permission to create or edit freight postings.');

        $this->zimbabweCities = ZimbabweCity::orderBy('name')->pluck('name', 'name')->toArray();

        $this->currentStep = 0;

        // Pre-fill shipper info when the logged-in user is a shipper
        if (auth()->user()->hasRole('shipper')) {
            $this->shipper_id = auth()->id();
            $this->shipperSearch = auth()->user()->organisation ?? auth()->user()->contact_person;
            $this->fullName = auth()->user()->contact_person;
            $this->email = auth()->user()->email;
            $this->phone = auth()->user()->contact_phone;
            $this->whatsapp = auth()->user()->whatsapp;
        }

        if ($freight->id) {
           // $freight = Freight::with('creator', 'goods')->findOrFail($freightId);
            $this->shipper_id = $freight->shipper_id;

            // Pre-fill shipperSearch label for editing
            if ($freight->shipper) {
                $this->shipperSearch = $freight->shipper->organisation ?? $freight->shipper->contact_person;
            }

            foreach ($freight->goods as $good) {
                $this->category = $good->name;
            }

            $this->goods = $freight->name;
            $this->description = $freight->description;
            $this->quantity = $freight->weight;
            $this->originCountry = $freight->countryfrom;
            $this->originCity = $freight->cityfrom;
            $this->originAddress = $freight->pickup_address;
            $this->destinationCountry = $freight->countryto;
            $this->destinationCity = $freight->cityto;
            $this->destinationAddress = $freight->delivery_address;
            $this->amount = $freight->weight;
            $this->hazardous = $freight->hazardous;
            $this->distance = $freight->distance;
            $this->paymentOption = $freight->payment_option;
            $this->carriageRate = $freight->carriage_rate;
            $this->unitType = $freight->capacity_unit;
            $this->selectedTrailer = $freight->vehicle_type?->value;
            $this->pickupDate = date('Y-m-d', strtotime($freight->datefrom));
            $this->deliveryDate = date('Y-m-d', strtotime($freight->dateto));
            $this->fullName = $freight->creator->contact_person;
            $this->email = $freight->creator->email;
            $this->phone = $freight->creator->contact_phone;
            $this->whatsapp = $freight->creator->whatsapp;
            $this->badgeStatusColor = $freight->status->color();
            $this->badgeStatusLabel = $freight->status->label();
            $this->parseGoodsQuantity();
            $this->status = $freight->status;
        } else {
            $this->status = FreightStatus::DRAFT;
        }
    }

    public function parseGoodsQuantity()
    {
        if (preg_match('/(\d+)\s*(tonnes|litres)/i', $this->amount, $matches)) {
            $this->quantity = (int) $matches[1];
            $this->unitType = strtolower($matches[2]) == 'tonnes' ? 'weight' : 'volume';
            $this->unit = strtolower($matches[2]);
        }
    }

    #[Computed]
    public function shippers()
    {
        if (empty($this->shipperSearch)) {
            return [];
        }

        return User::query()
            ->whereHas('roles', function ($query) {
                $query->where('name', 'shipper');
            })
            ->where(function ($query) {
                $query
                    ->where('organisation', 'like', '%' . $this->shipperSearch . '%')
                    ->orWhere('contact_person', 'like', '%' . $this->shipperSearch . '%')
                    ->orWhere('email', 'like', '%' . $this->shipperSearch . '%');
            })
            ->take(5)
            ->get();
    }

    #[Computed]
    public function badgeColor()
    {
        $statusEnum = $this->status instanceof FreightStatus ? $this->status : FreightStatus::from($this->status);

        return $statusEnum->color();
    }

    #[Computed]
    public function badgeLabel()
    {
        $statusEnum = $this->status instanceof FreightStatus ? $this->status : FreightStatus::from($this->status);

        return $statusEnum->label();
    }

    public function nextStep(): void
    {
        $this->validateStep();
        if ($this->currentStep < 7) {
            $this->currentStep++;
        }
    }

    public function previousStep(): void
    {
        if ($this->currentStep > 0) {
            $this->currentStep--;
        }
    }

    public function validateStep(): void
    {
        if ($this->currentStep == 0) {
            $this->validate(['shipper_id' => 'required'], ['shipper_id.required' => 'Please select a shipper to continue.']);
        }

        if ($this->currentStep == 1) {
            $this->validateOnly('category');
            $this->validateOnly('goods');
            $this->validateOnly('hazardous');
        }

        if ($this->currentStep == 2) {
            $this->validateOnly('unitType');
            $this->validateOnly('quantity');
            $this->validateOnly('description');
        }

        if ($this->currentStep == 3) {
            $this->validateOnly('originCountry');
            $this->validateOnly('originCity');
            $this->validateOnly('originAddress');
        }

        if ($this->currentStep == 4) {
            $this->validateOnly('destinationCountry');
            $this->validateOnly('destinationCity');
            $this->validateOnly('destinationAddress');
            $this->validateOnly('distance');
        }

        if ($this->currentStep == 5) {
            $this->validateOnly('paymentOption');
            $this->validateOnly('carriageRate');
            $this->validateOnly('selectedTrailer');
        }

        if ($this->currentStep == 6) {
            $this->validateOnly('pickupDate');
            $this->validateOnly('deliveryDate');
        }

        if ($this->currentStep == 7) {
            $this->validateOnly('fullName');
            $this->validateOnly('email');
            $this->validateOnly('phone');
            $this->validateOnly('whatsapp');
        }
    }
}; ?>

<div id="freight" x-data="{
    currentStep: @entangle('currentStep'),
    category: @entangle('category'),
    goods: @entangle('goods'),
    hazardous: @entangle('hazardous'),
    quantity: @entangle('quantity'),
    originCountry: @entangle('originCountry'),
    originCity: @entangle('originCity'),
    originAddress: @entangle('originAddress'),
    destinationCountry: @entangle('destinationCountry'),
    destinationCity: @entangle('destinationCity'),
    destinationAddress: @entangle('destinationAddress'),
    distance: @entangle('distance'),
    paymentOption: @entangle('paymentOption'),
    carriageRate: @entangle('carriageRate'),
    selectedTrailer: @entangle('selectedTrailer'),
    pickupDate: @entangle('pickupDate'),
    deliveryDate: @entangle('deliveryDate'),
    fullName: @entangle('fullName'),
    phone: @entangle('phone'),
    whatsapp: @entangle('whatsapp'),
    description: @entangle('description'),
    email: @entangle('email'),
    unitType: @entangle('unitType'),
    unit: @entangle('unit'),
}" x-cloak class="min-h-screen p-4 flex flex-col items-center pb-8">
    <div class="w-full max-w-7xl mt-8">
        <section class="w-full pt-8 pb-4 bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-8 text-center">
                    <flux:heading size="xl" level="1">Freight Posting</flux:heading>
                    <flux:subheading>Complete the details below to list your shipment on the manifest.</flux:subheading>
                </div>

                <div class="relative">
                    <div class="absolute inset-x-0 hidden lg:block top-7 px-24">
                        <svg class="w-full" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 875 48" fill="none">
                            <path
                                d="M2 29C20.2154 33.6961 38.9915 35.1324 57.6111 37.5555C80.2065 40.496 102.791 43.3231 125.556 44.5555C163.184 46.5927 201.26 45 238.944 45C312.75 45 385.368 30.7371 458.278 20.6666C495.231 15.5627 532.399 11.6429 569.278 6.11109C589.515 3.07551 609.767 2.09927 630.222 1.99998C655.606 1.87676 681.208 1.11809 706.556 2.44442C739.552 4.17096 772.539 6.75565 805.222 11.5C828 14.8064 850.34 20.2233 873 24"
                                stroke="currentColor" class="text-zinc-300 dark:text-zinc-700" stroke-width="2"
                                stroke-linecap="round" stroke-dasharray="6 10" />
                        </svg>
                    </div>

                    <div class="relative grid grid-cols-4 md:grid-cols-8 gap-2 text-center">
                        @php
                            $steps = [
                                0 => ['label' => 'Shipper', 'icon' => 'user'],
                                1 => ['label' => 'Goods', 'icon' => 'cube'],
                                2 => ['label' => 'Details', 'icon' => 'clipboard-list'],
                                3 => ['label' => 'Origin', 'icon' => 'map-pin'],
                                4 => ['label' => 'Destination', 'icon' => 'truck'],
                                5 => ['label' => 'Pricing', 'icon' => 'currency-dollar'],
                                6 => ['label' => 'Dates', 'icon' => 'calendar'],
                                7 => ['label' => 'Review', 'icon' => 'shield-check'],
                            ];
                        @endphp

                        @foreach ($steps as $index => $step)
                            <div class="flex flex-col items-center group">
                                <div :class="currentStep >= {{ $index }} ?
                                    'bg-cyan-600 border-cyan-600 text-white shadow-cyan-200' :
                                    'bg-white dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700 text-zinc-400'"
                                    class="relative z-10 flex items-center justify-center w-10 h-10 md:w-14 md:h-14 rounded-full border-2 shadow-sm transition-all duration-500">
                                    <template x-if="currentStep > {{ $index }}">
                                        <flux:icon.check class="size-5 md:size-6" variant="micro" />
                                    </template>
                                    <template x-if="currentStep <= {{ $index }}">
                                        <span class="text-sm md:text-lg font-bold">{{ $index + 1 }}</span>
                                    </template>
                                </div>
                                <span
                                    :class="currentStep == {{ $index }} ? 'text-cyan-700 dark:text-cyan-400 font-bold' :
                                        (currentStep > {{ $index }} ? 'text-zinc-800 dark:text-zinc-200' :
                                            'text-zinc-400')"
                                    class="mt-3 text-[10px] md:text-xs uppercase tracking-tighter md:tracking-wider transition-colors duration-500">
                                    {{ $step['label'] }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <div class="flex flex-col md:flex-row gap-8 w-full">

            {{-- LEFT PANEL: step hints + live summary --}}
            <div class="p-6 rounded-3xl shadow-xl w-full md:w-1/2 order-2 md:order-1">
                <div class="text-right">
                    <flux:badge size="sm" :color="$this->badgeColor">
                        {{ $this->badgeLabel }}
                    </flux:badge>
                </div>

                <x-steps.registration-steps :step="1" icon="cube" title="Shipment!"
                    description="State what you are shipping." usageTitle="Used for:" :items="['Proper handling & storage', 'Customs & legal requirements']" />
                <x-steps.registration-steps :step="2" icon="clipboard-list" title="Details!"
                    description="Quantity & any additional relevant info." usageTitle="We'll use this for:"
                    :items="['Cost Calculation', 'Traceability & Accountability']" />
                <x-steps.registration-steps :step="3" icon="location-marker" title="Pickup Address"
                    description="Where is the goods going to be loaded from?" usageTitle="Why we need this:"
                    :items="['Logistics coordination', 'Avoiding delays & errors']" />
                <x-steps.registration-steps :step="4" icon="location-marker" title="Delivery Address"
                    description="Tell us about offloading address" usageTitle="Why we need this:" :items="['Accuracy in delivery', 'Cost implications']" />
                <x-steps.registration-steps :step="5" icon="shield-check" title="Payment and Trailer Options"
                    description="Tell us about your preferences" usageTitle="Our reason for asking:"
                    :items="['Convenience', 'Security']" />
                <x-steps.registration-steps :step="6" icon="calendar-days" title="Dates"
                    description="Pickup & Delivery Dates" usageTitle="Why we are requesting this info:"
                    :items="['Scheduling efficiency', 'Meeting your expectations']" />
                <x-steps.registration-steps :step="7" icon="user" title="Contact Person"
                    description="Provide multiple contact methods for easy & prompt reach"
                    usageTitle="This helps us in:" :items="['Communication clarity', 'Issue resolution', 'Accountability']" />

                <div class="mt-6 pt-4 border-t border-gray-700">
                    <h4 class="text-sm font-medium mb-2">Your Information</h4>
                    <div class="space-y-2 text-xs">
                        <div class="flex items-center gap-2" x-show="category || goods">
                            <x-graphic name="cube" class="size-3.5 text-blue-400" />
                            <span x-text="category + ': ' + goods" class="text-gray-400"></span>
                            <template x-if='hazardous'>
                                <span>hazardous goods</span>
                            </template>
                        </div>
                        <div class="flex items-center gap-2 text-gray-400" x-show="quantity">
                            <flux:icon.scale color='lime' class="size-3.5" />
                            <span x-text="quantity + ' ' + unit"></span>
                            <p x-text="description"></p>
                        </div>
                        <div class="flex items-center gap-2" x-show="originCountry">
                            <x-graphic name="location-marker" class="size-3.5 text-green-400" />
                            <span x-text="originAddress + ', '+ originCity + ', '+ originCountry"
                                class="text-gray-400"></span>
                        </div>
                        <div class="flex items-center gap-2" x-show="destinationCountry">
                            <x-graphic name="location-marker" class="size-3.5 text-red-400" />
                            <span
                                x-text="destinationAddress + ', '+ destinationCity + ', '+ destinationCountry + ': '+ distance +' km'"></span>
                        </div>
                        <div class="flex items-center gap-2" x-show="paymentOption">
                            <x-graphic name="shield-check" class="size-3.5 text-yellow-400" />
                            <span x-text="paymentOption + ', '+ carriageRate"></span>
                            <template x-if='selectedTrailer'>
                                @php $iconName = strtolower(str_replace(' ', '-', $selectedTrailer ?? '')); @endphp
                                <x-graphic name="{{ $iconName }}" class="size-24" />
                            </template>
                            <flux:text>{{ $selectedTrailer }}</flux:text>
                        </div>
                        <div class="flex items-center gap-2" x-show="pickupDate">
                            <x-graphic name="calendar-days" class="size-4.5 text-indigo-400" />
                            <span
                                x-text="'To be picked up on: ' + new Date(pickupDate).toLocaleDateString('en-GB', {weekday: 'short', day: '2-digit', month: 'short', year: 'numeric'})"></span>
                            <span
                                x-text="' & delivered on: ' + new Date(deliveryDate).toLocaleDateString('en-GB', {weekday: 'short', day: '2-digit', month: 'short', year: 'numeric'})"></span>
                        </div>
                        <div class="flex items-center gap-2" x-show="fullName">
                            <x-graphic name="user" class="size-3.5 text-blue-400" />
                            <span x-text="'Contact Person: '+ fullName" class="text-gray-400"></span>
                        </div>
                        <div class="flex items-center gap-2" x-show="email">
                            <x-graphic name="email-open" class="size-3.5 text-orange-400" />
                            <span x-text="email" class="text-gray-400"></span>
                        </div>
                        <div class="flex items-center gap-2" x-show="phone">
                            <x-graphic name="phone" class="size-3.5 text-blue-400" />
                            <span x-text="'Phone: ' + phone" class="text-gray-400"></span>
                        </div>
                        <div class="flex items-center gap-2" x-show="whatsapp">
                            <x-graphic name="whatsapp" class="size-3.5 text-green-400" />
                            <span x-text="'WhatsApp: ' + whatsapp" class="text-gray-400"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT PANEL: form steps --}}
            <div class="p-6 rounded-3xl shadow-xl w-full md:w-1/2 order-2 md:order-1">
                <form wire:submit.prevent="uploadFreight">

                    {{-- ── STEP 0: Shipper Identification ── --}}
                    <div x-show="currentStep == 0" class="my-2 space-y-4">
                        <flux:heading size="lg">Shipper Identification</flux:heading>
                        <flux:text class="mb-4">Identify the owner of this freight shipment.</flux:text>

                        @php
                            $isShipper = auth()->user()->hasRole('shipper');
                            $isUnrestricted = auth()
                                ->user()
                                ->hasAnyRole(\App\Services\FreightService::UNRESTRICTED_SHIPPER_ROLES);
                            $isTerritoryStaff = auth()
                                ->user()
                                ->hasAnyRole(\App\Services\FreightService::TERRITORY_RESTRICTED_ROLES);
                            // Both unrestricted and territory-restricted staff see the search box
                            $canSearchShippers = $isUnrestricted || $isTerritoryStaff;
                        @endphp

                        @if ($isShipper)
                            {{-- Shippers see a read-only card of their own identity --}}
                            <div
                                class="p-6 bg-cyan-50 dark:bg-cyan-950 border border-cyan-200 dark:border-cyan-800 rounded-2xl space-y-3">
                                <div class="flex items-center gap-4 mb-2">
                                    <div
                                        class="size-12 rounded-full bg-cyan-100 dark:bg-cyan-900 flex items-center justify-center text-cyan-700 dark:text-cyan-300 font-bold text-lg">
                                        {{ substr(auth()->user()->contact_person ?? 'S', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-bold">
                                            {{ auth()->user()->organisation ?? 'Individual Shipper' }}</div>
                                        <div class="text-sm text-zinc-500">Your account — freight will be posted under
                                            your name</div>
                                    </div>
                                </div>
                                <flux:separator variant="subtle" />
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    <span class="text-zinc-500">Contact Person:</span>
                                    <span class="font-medium">{{ auth()->user()->contact_person }}</span>
                                    <span class="text-zinc-500">Email:</span>
                                    <span class="font-medium">{{ auth()->user()->email }}</span>
                                    @if (auth()->user()->contact_phone)
                                        <span class="text-zinc-500">Phone:</span>
                                        <span class="font-medium">{{ auth()->user()->contact_phone }}</span>
                                    @endif
                                </div>
                            </div>
                            {{-- Hidden input keeps shipper_id wired --}}
                            <input type="hidden" wire:model="shipper_id">
                        @elseif ($canSearchShippers)
                            {{-- Staff with search access --}}
                            <div class="relative" x-on:click.away="$wire.showShipperResults = false">
                                <flux:input label="Search Shippers" placeholder="Search by name, email, or company..."
                                    wire:model.live.debounce.300ms="shipperSearch"
                                    x-on:focus="$wire.showShipperResults = true" icon="magnifying-glass" />

                                {{-- Results dropdown --}}
                                @if ($showShipperResults)
                                    @if (count($this->shippers) > 0)
                                        <div
                                            class="absolute z-50 w-full bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl mt-1 shadow-2xl overflow-hidden">
                                            @foreach ($this->shippers as $shipper)
                                                <button type="button"
                                                    wire:click="selectShipper({{ $shipper->id }}, '{{ addslashes($shipper->organisation ?? $shipper->contact_person) }}')"
                                                    class="w-full text-left px-4 py-3 hover:bg-cyan-50 dark:hover:bg-cyan-900 border-b last:border-0 transition-colors">
                                                    <span
                                                        class="font-bold block">{{ $shipper->organisation ?? 'Individual' }}</span>
                                                    <span class="text-xs text-zinc-500">{{ $shipper->contact_person }}
                                                        • {{ $shipper->email }}</span>
                                                    @if ($isTerritoryStaff)
                                                        {{-- Subtle territory badge so staff know the scope --}}
                                                        <span
                                                            class="text-[10px] text-cyan-600 dark:text-cyan-400">Territory
                                                            shipper</span>
                                                    @endif
                                                </button>
                                            @endforeach
                                        </div>
                                    @elseif (!empty($shipperSearch))
                                        {{-- No results message --}}
                                        <div
                                            class="absolute z-50 w-full bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl mt-1 shadow-2xl p-4 text-center">
                                            <flux:icon.user class="size-8 mx-auto text-zinc-400 mb-2" />
                                            <p class="text-sm text-zinc-500">
                                                No shippers found
                                                @if ($isTerritoryStaff)
                                                    in your territory
                                                @endif
                                                matching <strong>"{{ $shipperSearch }}"</strong>.
                                            </p>
                                            @if ($isTerritoryStaff)
                                                <p class="text-xs text-zinc-400 mt-1">You can only assign shippers
                                                    within your assigned territory.</p>
                                            @endif
                                        </div>
                                    @endif
                                @endif
                            </div>

                            {{-- Confirmation badge once a shipper is picked --}}
                            @if ($shipper_id)
                                <div
                                    class="flex items-center gap-2 p-2 bg-green-50 dark:bg-green-950 border border-green-200 dark:border-green-800 rounded-lg">
                                    <flux:icon.check-circle variant="mini"
                                        class="text-green-600 dark:text-green-400" />
                                    <span class="text-sm text-green-800 dark:text-green-300">
                                        Selected: <strong>{{ $shipperSearch }}</strong>
                                    </span>
                                    <button type="button"
                                        wire:click="$set('shipper_id', null); $set('shipperSearch', '')"
                                        class="ml-auto text-xs text-zinc-400 hover:text-red-500 transition-colors">
                                        Change
                                    </button>
                                </div>
                            @endif

                            <flux:error name="shipper_id" />
                        @else
                            {{-- Fallback: role exists in FREIGHT_CREATOR_ROLES but doesn't fit above buckets --}}
                            <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-800">
                                Your current role does not permit shipper search. Please contact an administrator.
                            </div>
                        @endif
                    </div>

                    {{-- ── STEP 1: Goods ── --}}
                    <div x-show="currentStep=='1'" class="my-2 space-y-2">
                        <flux:text class="text-base my-2">What are you shipping?</flux:text>
                        <flux:select wire:model="category" placeholder="Select Freight Category"
                            indicator="checkbox">
                            <flux:select.option disabled>Choose one</flux:select.option>
                            @foreach ($this->categories as $category)
                                <flux:select.option value="{{ $category->name }}">{{ $category->name }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="category" />
                        <flux:input label="Name of Goods" wire:model="goods" />
                        <flux:field variant="inline">
                            <flux:switch label='Hazardous Goods?' wire:model="hazardous" />
                        </flux:field>
                    </div>

                    {{-- ── STEP 2: Details ── --}}
                    <div x-show="currentStep==2" class="my-2 space-y-2">
                        <flux:text class="text-base my-2">Tell us more about the goods</flux:text>
                        <flux:radio.group wire:model="unitType" label="Select Weight / Volume of Goods"
                            wire:click="setQuantityUnit">
                            <flux:radio value="tonnes" label="Weight" checked />
                            <flux:radio value="litres" label="Volume" />
                        </flux:radio.group>
                        <flux:input kbd="{{ $unitType }}" label="Goods Quantity" wire:model="quantity"
                            type="number" />
                        <flux:textarea rows="auto" label="More details about this load.."
                            wire:model="description" />
                    </div>

                    {{-- ── STEP 3: Origin ── --}}
                    <div x-show="currentStep==3" class="my-2 space-y-2">
                        <flux:text class="text-base my-2">Where are you shipping from?</flux:text>
                        <flux:select wire:model="originCountry" placeholder="Select Origin Country">
                            <flux:select.option></flux:select.option>
                            <flux:select.option value="zimbabwe">Zimbabwe</flux:select.option>
                            <flux:select.option value="south africa">South Africa</flux:select.option>
                        </flux:select>
                        <flux:error name='originCountry' />
                        <div x-show="originCountry=='zimbabwe'">
                            <flux:select wire:model="originCity" placeholder="Select Origin Town / City"
                                label="Town / City">
                                <flux:select.option></flux:select.option>
                                @foreach ($zimbabweCities as $city)
                                    <flux:select.option value="{{ $city }}">{{ $city }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>
                        <div x-show="originCountry=='south africa'">
                            <flux:input label="Town / City" wire:model="originCity" />
                        </div>
                        <flux:input label="Physical Street Address" wire:model="originAddress" />
                    </div>

                    {{-- ── STEP 4: Destination ── --}}
                    <div x-show="currentStep==4" class="my-2 space-y-2">
                        <flux:text class="text-base my-2">Where are you shipping to?</flux:text>
                        <flux:select wire:model="destinationCountry" placeholder="Select Destination Country">
                            <flux:select.option></flux:select.option>
                            <flux:select.option value="zimbabwe">Zimbabwe</flux:select.option>
                            <flux:select.option value="south africa">South Africa</flux:select.option>
                        </flux:select>
                        <flux:error name='destinationCountry' />
                        <div x-show="destinationCountry=='zimbabwe'">
                            <flux:select wire:model="destinationCity" placeholder="Select Destination Town / City"
                                label="Town / city">
                                <flux:select.option></flux:select.option>
                                @foreach ($zimbabweCities as $city)
                                    <flux:select.option value="{{ $city }}">{{ $city }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>
                        <div x-show="destinationCountry=='south africa'">
                            <flux:input label="Town / city" wire:model="destinationCity" />
                        </div>
                        <flux:input label="Physical Street Address" wire:model="destinationAddress" />
                        <flux:input kbd='km' label="Distance" wire:model="distance" type="number"/>
                    </div>

                    {{-- ── STEP 5: Pricing & Trailer ── --}}
                    <div x-show="currentStep==5" class="my space-y-2">
                        <flux:text class="text-base my-2">Preferences</flux:text>
                        <flux:radio.group wire:model.live="paymentOption" label="Preferred Payment Option"
                            variant="segmented" size="sm">
                            <flux:radio label="Full Budget" value="full_budget" />
                            <flux:radio label="Rate of Carriage" value="rate_of_carriage" />
                        </flux:radio.group>
                        <flux:input :kbd="$paymentOption == 'rate_of_carriage' ? 'US$/km' : 'US$'"
                            label="Budget Amount" wire:model="carriageRate" />
                        <flux:text class="text-base my-2">Selected your Preferred Trailer (if any)</flux:text>
                        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach ($this->trailers as $trailer)
                                @php
                                    $iconName = $trailer->name->iconName(); // e.g. "curtain-side", "flat-bed"
                                @endphp
                                <label for="trailer-{{ $trailer->id }}" class="cursor-pointer">
                                    <div
                                        class="flex flex-col items-center justify-center p-4 rounded-lg border-2 border-gray-200 transition-all duration-200 hover:bg-gray-100 
            has-[:checked]:bg-blue-50 has-[:checked]:border-blue-500">
                                        <x-graphic name="{{ $iconName }}" class="size-24" />
                                        <span class="mt-2 text-sm font-medium text-gray-600">
                                            {{ $trailer->name->label() }}
                                        </span>
                                        <input id="trailer-{{ $trailer->id }}" type="radio"
                                            wire:model.live="selectedTrailer" value="{{ $trailer->name->value }}"
                                            class="sr-only" />
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- ── STEP 6: Dates ── --}}
                    <div x-show="currentStep==6" class="my-2 space-y-2">
                        <flux:text class="text-base my-2">Transportation Dates</flux:text>
                        <flux:input type="date" label="Freight Pickup Date" wire:model.live="pickupDate" />
                        <flux:input type="date" label="Expected Delivery Date" wire:model="deliveryDate" />
                    </div>

                    {{-- ── STEP 7: Contact Person ── --}}
                    <div x-show="currentStep==7" class="my-2 space-y-2">
                        <flux:text class="text-base my-2">Contact Person</flux:text>
                        <flux:switch label='Me?' wire:model='self' wire:click='selfContact' />
                        <flux:input label="Full Name" wire:model="fullName" x-model='fullName'
                            :readonly='$self' />
                        <flux:input type='email' label="Email" wire:model="email" x-model='email'
                            :readonly='$self' />
                        <flux:input type="tel" label="Contact Phone" wire:model="phone" x-model='phone'
                            :readonly='$self' />
                        <flux:input type="tel" label="Whatsapp (optional)" wire:model="whatsapp"
                            x-model='whatsapp' :readonly='$self' />
                    </div>

                    {{-- ── Navigation ── --}}
                    <div class="flex justify-between mt-8 space-x-2">
                        <flux:button wire:click="previousStep" x-show="currentStep > 0" variant='ghost'
                            icon='chevron-double-left' class='ml-auto'>
                            Back
                        </flux:button>
                        <flux:button wire:click="nextStep" x-show="currentStep < 7" variant='primary' color='cyan'
                            icon='chevron-double-right' class='ml-auto'>
                            Next
                        </flux:button>
                        <flux:button wire:click="saveDraft" x-show="currentStep >= 6" variant='primary'
                            color='lime' icon='server' class='ml-auto'>
                            Save Draft
                        </flux:button>
                        <flux:button wire:click="submit" x-show="currentStep == 7" variant='primary' color='green'
                            icon='paper-airplane' class='ml-auto'>
                            Submit
                        </flux:button>
                    </div>

                    {{-- ── Progress dots ── --}}
                    <div class="flex justify-center mt-8 gap-2">
                        @for ($i = 1; $i <= 7; $i++)
                            <div class="h-2 rounded-full transition-all duration-300"
                                :class="currentStep == {{ $i }} ? 'w-8 bg-blue-500' : 'w-2 bg-gray-600'">
                            </div>
                        @endfor
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
