<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use App\Models\Trailer;
use App\Models\User;

new class extends Component {
    public $currentStep =1;
    public $availability_date;
    public $origin_country;
    public $origin_city;
    public $destination_country;
    public $destination_city;
    public $trailer_type;
    public $available_capacity;
    public $rate;
    public $zimbabweCities =[];
    public $fullName;
    public $email;
    public $phone;
    public $whatsapp;
    public $self = false;
    public $isDraft = false;

    #[Locked]
    public $laneId;

    #[Computed]
    public function trailers()
    {
        return Trailer::orderBy('name')->get();
    }   

    public function saveDraft()
    {
        $this->isDraft = true;
        $this->uploadvehicle();
    }

    public function submitVehicle()
    {
        $this->isDraft = false;
        $this->uploadvehicle();        
    }

    public function uploadvehicle()
    {
        $validated = $this->validate();
        $validated['cityfrom']  =$validated['origin_city'];
        $validated['countryfrom']  =$validated['origin_country'];
        $validated['cityto']  =$validated['destination_city'];
        $validated['countryto']  =$validated['destination_country'];   
        $validated['capacity']  =$validated['available_capacity'];   
        $validated['trailer'] = strtolower($validated['trailer_type']);
        $this->isDraft? $validated['status'] = 'draft': $validated['status'] = 'submitted';

         $lane = auth()->user()->lanes()->updateOrCreate(['id'=>$this->laneId], $validated);

         if($this->currentStep ==4){
            $validatedContacts['full_name'] = $validated['fullName'];
            $validatedContacts['phone_number'] = $validated['fullName'];
            $validatedContacts['whatsapp'] = $validated['whatsapp'];
            $validatedContacts['email'] = $validated['email'];
           $lane->contacts()->updateOrCreate(['contactable_id'=>$this->laneId], $validatedContacts);
         }
         $this->laneId? $sessionMessage ='vehicle successfully updated' : $sessionMessage ='Vehicle successfully submitted';
         session()->flash('message', $sessionMessage);        
    }


    
    public function selfContact()
    {
        if($this->self == true){
            $selfContact = User::select('email','contact_phone','whatsapp','contact_person')->where('id', auth()->user()->id)->firstOrFail();
            $this->email = $selfContact->email;
            $this->phone = $selfContact->contact_phone;
            $this->whatsapp = $selfContact->whatsapp;
            $this->fullName = $selfContact->contact_person;
        }
        else{
            if(!$this->LaneId){
                $this->reset(['email','phone','whatsapp','fullName']);
            }
            
        }
        
    }    

    protected function rules(): array
    {
       $validatedThreeStages = [            
            'origin_country' => ['required', 'string', 'max:255'],
            'origin_city' => ['required', 'string', 'max:255'],
            'destination_country' => ['required', 'string', 'max:255'],
            'destination_city' => ['required', 'string', 'max:255'], 
            'trailer_type'=>['required'],
            'available_capacity'=>['required'],
            'availability_date'=>['required','date','afterOrEqual:today'],
            'rate' => ['required'],
        ];
        
        $validatedFourthStage = [
            'fullName'=>['required'],
            'phone'=>['required_without_all:whatsapp,email'],
            'whatsapp'=>['nullable'],
            'email'=>['nullable'],
        ];
        $validatedAllStages = array_merge($validatedThreeStages, $validatedFourthStage);
        if($this->currentStep == 3){
            return $validatedThreeStages;
        }
        return $validatedAllStages;
    }  



    public function mount()
    {
        $this->zimbabweCities = \App\Models\ZimbabweCity::orderBy('name')->pluck('name', 'name')->toArray();
    }

    // Multi-step form navigation methods
    public function nextStep(): void
    {
        $this->validateStep();
        if ($this->currentStep < 4) {            
            $this->currentStep = $this->currentStep + 1;  
        }
    } 
    
    
    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep = $this->currentStep - 1;
        }
    } 
    
    public function validateStep(): void
    {
        if ($this->currentStep == 1) {
            $this->validateOnly('availability_date');
            $this->validateOnly('origin_country');
            $this->validateOnly('origin_city');
            $this->validateOnly('destination_country');
            $this->validateOnly('destination_city');            
        }
        if ($this->currentStep == 2) {
            $this->validateOnly('trailer_type');
            $this->validateOnly('available_capacity');
        }
        if ($this->currentStep == 3) {            
            $this->validateOnly('rate');          
        }
        
        if ($this->currentStep == 4) {
            $this->validateOnly('fullName');
            $this->validateOnly('email');  
            $this->validateOnly('phone');  
            $this->validateOnly('whatsapp');  
        }         
    }    
   

}; ?>

<div id="vehicle_creation" 
     x-data="
        { 
            currentStep: @entangle('currentStep'),
            availability_date: @entangle('availability_date'),
            origin_country: @entangle('origin_country'),
            origin_city: @entangle('origin_city'),
            destination_country: @entangle('destination_country'),
            destination_city: @entangle('destination_city'),
            trailer_type: @entangle('trailer_type'),
            available_capacity: @entangle('available_capacity'),
            rate: @entangle('rate'),
            fullName: @entangle('fullName'),
            phone: @entangle('phone'),
            whatsapp: @entangle('whatsapp'),
            email: @entangle('email'),
            self: @entangle('self'),
        }" 
     x-cloak
     class="min-h-screen p-4 flex flex-col items-center pb-8">
    
    <div class="w-full max-w-7xl mt-8">
        <div class="flex flex-col md:flex-row gap-8 w-full">
            <!-- Left Panel: Summary -->
            <div class="p-6 rounded-3xl shadow-xl w-full md:w-1/2 order-2 md:order-1 "> 
                <div class="text-right">
                </div>
                
                <!-- Step 1: Journey Details -->
                <div x-show = "currentStep == 1" class="flex items-start gap-4" :class="{'opacity-50': currentStep != 1}">
                    <div class="bg-blue-500 rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold">1</div>
                    <div>
                        <h3 class="text-lg font-bold">Journey Details</h3>
                        <p class="text-sm text-gray-400">Where is the vehicle available for a journey?</p>
                    </div>
                </div>
                
                <!-- Step 2: Vehicle Specifications -->
                <div x-show = "currentStep == 2" class="flex items-start gap-4" :class="{'opacity-50': currentStep != 2}">
                    <div class="bg-blue-500 rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold">2</div>
                    <div>
                        <h3 class="text-lg font-bold">Vehicle Specifications</h3>
                        <p class="text-sm text-gray-400">Tell us about the vehicle and its capacity.</p>
                    </div>
                </div>
                
                <!-- Step 3: Pricing Information -->
                <div x-show = "currentStep == 3" class="flex items-start gap-4" :class="{'opacity-50': currentStep != 3}">
                    <div class="bg-blue-500 rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold">3</div>
                    <div>
                        <h3 class="text-lg font-bold">Pricing Information</h3>
                        <p class="text-sm text-gray-400">State your rate for the carriage.</p>
                    </div>
                </div>

                <!-- Step 4: Contact Person -->
                <div x-show = "currentStep == 4" class="flex items-start gap-4" :class="{'opacity-50': currentStep != 4}">
                    <div class="bg-blue-500 rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold">4</div>
                    <div>
                        <h3 class="text-lg font-bold">Contact Person</h3>
                        <p class="text-sm text-gray-400">Who should we contact for this listing?</p>
                    </div>
                </div>
                
                <div class="mt-6 pt-4 border-t border-gray-700">
                    <h4 class="text-sm font-medium mb-2">Your Information</h4>
                    <div class="space-y-2 text-xs">
                        <div class="flex items-center gap-2 text-gray-400" x-show="availability_date">
                            <span class="w-4 h-4 text-indigo-400">📅</span>
                            <span x-text="'Available on: ' + new Date(availability_date).toLocaleDateString()"></span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-400" x-show="origin_country || destination_country">
                            <span class="w-4 h-4 text-green-400">📍</span>
                            <span x-text="origin_city + ', ' + origin_country + ' to ' + destination_city + ', ' + destination_country"></span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-400" x-show="trailer_type || available_capacity">
                            <span class="w-4 h-4 text-blue-400">🚛</span>
                            <span x-text="trailer_type + ' - ' + available_capacity + ' tonnes capacity'"></span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-400" x-show="rate">
                            <span class="w-4 h-4 text-yellow-400">💰</span>
                            <span x-text="'Rate: ' + rate"></span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-400" x-show="fullName">
                            <span class="w-4 h-4 text-orange-400">👤</span>
                            <span x-text="'Contact: ' + fullName"></span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-400" x-show="email">
                            <span class="w-4 h-4 text-orange-400">📧</span>
                            <span x-text="email"></span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-400" x-show="phone">
                            <span class="w-4 h-4 text-blue-400">📞</span>
                            <span x-text="'Phone: ' + phone"></span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-400" x-show="whatsapp">
                            <span class="w-4 h-4 text-green-400">📱</span>
                            <span x-text="'WhatsApp: ' + whatsapp"></span>
                        </div>
                    </div>
                </div> 
            </div>

            <!-- Right Panel: Form -->
            <div class="p-6 rounded-3xl shadow-xl w-full md:w-1/2 order-1 md:order-2 ">
                <form wire:submit.prevent="submitVehicle">
                    <!-- Step 1: Journey Details -->
                  <div x-show ="currentStep == 1">
                    <div  class="my-2 space-y-2">
                        <flux:text class="text-base my-2">Tell us about the journey</flux:text>                     
                        <flux:input type="date" label="Availability Date" wire:model="availability_date"/>

                        <flux:fieldset class="border border-gray-400 p-4">
                            <flux:legend>

                               <span class="text-2xl"> &#128667; </span> Vehicle Location on Availability Date 
                            </flux:legend>
                                <flux:select label="Country" wire:model="origin_country" placeholder="Select Origin Country">
                                    <flux:select.option></flux:select.option>
                                    <flux:select.option value="zimbabwe">Zimbabwe</flux:select.option>
                                    <flux:select.option value="south africa">South Africa</flux:select.option>  
                                </flux:select>

                            <div x-show="origin_country=='zimbabwe'" class="">
                                <flux:select wire:model="origin_city" placeholder="Select Origin Town / City" label="Town / City">
                                    <flux:select.option></flux:select.option>
                                @foreach($zimbabweCities as $city)
                                    <flux:select.option value="{{ $city }}">{{$city}}</flux:select.option>
                                @endforeach
                                </flux:select>
                            </div> 
                            <div x-show="origin_country=='south africa'" class="">
                                <flux:input label="Town / City" wire:model="origin_city"/>
                            </div> 
                        </flux:fieldset>
                    </div> 

                    <div class="my-2 space-y-2">

                        <flux:fieldset class="border border-gray-400 p-4">
                            <flux:legend>
                                
                                Journey Destination <span class="text-2xl"> &#128666; </span>
                            </flux:legend>
                                <flux:select label="Destination Country" wire:model="destination_country" placeholder="Select Destination Country">
                                    <flux:select.option></flux:select.option>
                                    <flux:select.option value="zimbabwe">Zimbabwe</flux:select.option>
                                    <flux:select.option value="south africa">South Africa</flux:select.option>  
                                </flux:select>

                            <div x-show="destination_country=='zimbabwe'" class="">
                                <flux:select wire:model="destination_city" placeholder="Select Origin Town / City" label="Town / City">
                                    <flux:select.option></flux:select.option>
                                @foreach($zimbabweCities as $city)
                                    <flux:select.option value="{{ $city }}">{{$city}}</flux:select.option>
                                @endforeach
                                </flux:select>
                            </div> 
                            <div x-show="destination_country=='south africa'" class="">
                                <flux:input label="Town / City" wire:model="destination_city"/>
                            </div> 
                        </flux:fieldset>
                    </div> 
                  </div>                    

                    <!-- Step 2: Vehicle Specifications -->
                    <div x-show="currentStep==2" class="my-2 space-y-2">
                        <flux:text class="text-base my-2">  Vehicle Specifications </flux:text>
                            <flux:text class="text-base my-2">Selected your Prefferred Trailer (if any)</flux:text>  
                            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                
                                @foreach($this->trailers as $trailer)
                                    @php
                                        // Convert the name to the hyphenated format for the icon.
                                        $iconName = strtolower(str_replace(' ', '-', $trailer->name));
                                    @endphp
                                    <label for="trailer-{{ $trailer->id}}" class="cursor-pointer">
                                        <div class="flex flex-col items-center justify-center p-4 rounded-lg border-2 border-gray-200 transition-all duration-200 hover:bg-gray-100 has-[:checked]:bg-blue-50 has-[:checked]:border-blue-500">
                                            <x-graphic name="{{ $iconName }}" class="size-24" />
                                            <span class="mt-2 text-sm font-medium text-gray-600 has-[:checked]:text-blue-700">{{ $trailer->name }}</span>
                                            <input id="trailer-{{ $trailer->id}}" type="radio" wire:model.live="trailer_type" value="{{ $trailer->name}}" class="sr-only" />
                                        </div>
                                    </label>
                                @endforeach
                            </div> 

                        <flux:input kbd="tonnes" label="Available Capacity" wire:model="available_capacity" type="number"/> 
                    </div>

                    <!-- Step 3: Pricing Information -->
                    <div x-show="currentStep==3" class="my space-y-2">
                        <flux:text class="text-base my-2">Pricing Information</flux:text>
                        <flux:input kbd="US$ or US$/km" label="Rate" wire:model="rate"/> 
                    </div>
                    
                    <!-- Step 4: Contact Person-->
                    <div x-show="currentStep==4" class="my-2 space-y-2">
                        <flux:text class="text-base my-2">Contact Person</flux:text>
                        <flux:switch label='Me?' wire:model.live='self' wire:click='selfContact' />
                        <flux:input label="Full Name" wire:model="fullName" x-model='fullName'  />
                        <flux:input type='email' label="Email" wire:model="email" x-model='email'  />
                        <flux:input type="tel" label="Contact Phone" wire:model="phone" x-model='phone'  />
                        <flux:input type="tel" label="Whatsapp (optional)" wire:model="whatsapp" x-model='whatsapp'  />
                    </div>
                    
                    <!-- Navigation Buttons -->
                    <div class="flex justify-between mt-8 space-x-2"> 
                        <flux:button wire:click="previousStep" x-show="currentStep > 1" variant='primary' color='zinc' icon='chevron-double-left' class='ml-auto'>
                            Back
                        </flux:button>

                        <flux:button wire:click="nextStep" x-show="currentStep < 4" variant='primary' color='cyan' icon='chevron-double-right' class='ml-auto'>
                            Next 
                        </flux:button>

                        <flux:button wire:click="saveDraft" x-show="currentStep >= 3" variant='primary' color='lime' icon='paper-airplane' class='ml-auto'>
                            Save Draft 💾
                        </flux:button>                        
                        
                        <flux:button wire:click="submitVehicle" x-show="currentStep == 4" variant='primary' color='green'  class='ml-auto'>
                            Submit
                        </flux:button>
                    </div>
                    
                    <!-- Progress Indicators -->
                     <div class="flex justify-center mt-8 gap-2">
                        <div class="h-2 w-2 rounded-full" :class="{'w-8 bg-blue-500': currentStep == 1, 'bg-gray-600': currentStep != 1}"></div>
                        <div class="h-2 w-2 rounded-full" :class="{'w-8 bg-blue-500': currentStep == 2, 'bg-gray-600': currentStep != 2}"></div>
                        <div class="h-2 w-2 rounded-full" :class="{'w-8 bg-blue-500': currentStep == 3, 'bg-gray-600': currentStep != 3}"></div>
                        <div class="h-2 w-2 rounded-full" :class="{'w-8 bg-blue-500': currentStep == 4, 'bg-gray-600': currentStep != 4}"></div>
                    </div> 
                </form>
                </form>
            </div> 
        </div>
    </div>
</div>
