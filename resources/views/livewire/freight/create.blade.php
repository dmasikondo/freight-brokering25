<?php

use Livewire\Volt\Component;
use App\Models\Good;
use App\Models\Trailer;
use Livewire\Attributes\Computed;
use App\Models\User;

new class extends Component {

    public $currentStep = 1;
    public $category;
    public $goods;
    public $description;
    public $unitType;
    public $unit;
    public $quantity;
    public $originCountry;
    public $zimbabweCities =[];
    public $originCity;
    public $originAddress;
    public $destinationCountry;
    public $destinationCity;
    public $destinationAddress;
    public $hazardous = false;
    public $distance;
    public $paymentOption;
    public $carriageRate;
    //public $trailers =[];
    public $selectedTrailer;
    public $pickupDate;
    public $deliveryDate;
    public $fullName;
    public $email;
    public $phone;
    public $whatsapp;
    public $self = false;
    public $isDraft = false;
    
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
        if($this->unitType=='weight'){
            $this->unit = 'tonnes';
        }
        else{
            $this->unit = 'litres';
        }
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
            $this->reset(['email','phone','whatsapp','fullName']);
        }
        
    }

    protected function rules(): array
    {
       $validatedSixStages = [
            'category' => ['required', 'string', 'max:255'],
            'goods' => ['required', 'string', 'max:255'],
            'hazardous'=>['nullable'],
            'unitType' =>['required'],
            'quantity' =>['required'],
            'description' =>['required'],
            'originCountry' => ['required', 'string', 'max:255'],
            'originCity' => ['required', 'string', 'max:255'],
            'originAddress' => ['required', 'string', 'max:255'],
            'destinationCountry' => ['required', 'string', 'max:255'],
            'destinationCity' => ['required', 'string', 'max:255'],
            'destinationAddress' => ['required', 'string', 'max:255'],  
            'distance' =>['sometimes'],
            'paymentOption'=>['required'],
            'carriageRate'=>['required'],  
            'selectedTrailer'=>['nullable'],
            'pickupDate'=>['required','date','afterOrEqual:today'],
            'deliveryDate'=>['required','date','afterOrEqual:pickupDate'],
        ];
        
        $validatedSeventhStage = [
            'fullName'=>['required'],
            'phone'=>['required_without_all:whatsapp,email'],
            'whatsapp'=>['nullable'],
            'email'=>['nullable'],
        ];
        $validatedAllStages = array_merge($validatedSixStages, $validatedSeventhStage);
        if($this->currentStep == 6){
            return $validatedSixStages;
        }
        return $validatedAllStages;
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
         $validated['weight'] = $validated['quantity']. $this->unit;
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
         $validated['vehicle_type'] = $validated['selectedTrailer'];
         $this->isDraft? $validated['status'] = 'draft': $validated['status'] = 'submitted';

         $categoryId = Good::select('id')->whereName($validated['category'])->first();
         $freight = auth()->user()->freights()->create($validated);
         $freight->goods()->sync($categoryId->id);	

         if($this->currentStep ==7){
            $validatedContacts['full_name'] = $validated['fullName'];
            $validatedContacts['phone_number'] = $validated['fullName'];
            $validatedContacts['whatsapp'] = $validated['whatsapp'];
            $validatedContacts['email'] = $validated['email'];
           $freight->contacts()->create($validatedContacts);
         }

         session()->flash('message','Freight successfully uploaded');
         $this->redirectRoute('freights.index'); 
    }

    public function mount()
    {
        $this->zimbabweCities = \App\Models\ZimbabweCity::orderBy('name')->pluck('name', 'name')->toArray();
       // $this->trailers = \App\Models\Trailer::orderBy('name')->pluck('name','id')->toArray();   
            
        
    }

    // Multi-step form navigation methods
    public function nextStep(): void
    {
        $this->validateStep();
        if ($this->currentStep < 7) {
            
            $this->currentStep = $this->currentStep + 1;                
            
        }
    }
    
    public function previousStep(): void
    {
        //$this->validateStep();
        if ($this->currentStep > 1) {
            $this->currentStep = $this->currentStep - 1;
        }
    }
    
    public function validateStep(): void
    {
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

<div id="freight" x-data="
        { currentStep: @entangle('currentStep'),
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
         }" 
         x-cloak
    class="min-h-screen p-4 flex flex-col items-center pb-8">
    <div class="w-full max-w-7xl mt-8">
        <div class="flex flex-col md:flex-row gap-8 w-full">
            <div class="p-6 rounded-3xl shadow-xl w-full md:w-1/2 order-2 md:order-1">
                <!-- Step 1: Shipment -->
                <x-steps.registration-steps
                    :step="1"
                    icon="cube"
                    title="Shipment!"
                    description="State what you are shipping."
                    usageTitle="Used for:"
                    :items="['Proper handling & storage', 'Customs & legal requirements']"
                />

                <!-- Step 2: Description -->
                <x-steps.registration-steps
                    :step="2"
                    icon="clipboard-list"
                    title="Details!"
                    description="Quantity & any additional relevant info."
                    usageTitle="We'll use this for:"
                    :items="['Cost Calculation', 'Traceability & Accountability']"
                /> 
                <!-- Step 3: Pickup Address -->
                <x-steps.registration-steps
                    :step="3"
                    icon="location-marker"
                    title="Pickup Address"
                    description="Where is the goods going to be loaded from?"
                    usageTitle="Why we need this:"
                    :items="['Logistics coordination', 'Avoiding delays & errors']"
                />

                <!-- Step 4: Pickup Address -->
                <x-steps.registration-steps
                    :step="4"
                    icon="location-marker"
                    title="Delivery Address"
                    description="Tell us about offloading address"
                    usageTitle="Why we need this:"
                    :items="['Accuracy in delivery', 'Cost implications']"
                />                

                <!-- Step 5: Preferences-->
                <x-steps.registration-steps
                    :step="5"
                    icon="shield-check"
                    title="Payment and Trailer Options"
                    description="Tell us about your preferences"
                    usageTitle="Our reason for asking:"
                    :items="['Convenience', 'Security']"
                />  
                
                <!-- Step 6: Dates-->
                <x-steps.registration-steps
                    :step="6"
                    icon="calendar-days"
                    title="Dates"
                    description="Pickup & Delivery Dates"
                    usageTitle="Why we are requesting this info:"
                    :items="['Scheduling efficiency', 'Meeting your expectations']"
                />  
                
                <!-- Step 7: Contact Person-->
                <x-steps.registration-steps
                    :step="7"
                    icon="user"
                    title="Contact Person"
                    description="Provide multiple contact methods for easy & prompt reach"
                    usageTitle="This helps us in:"
                    :items="['Communication clarity', 'Issue resolution','Accountability']"
                /> 
                
                <div class="mt-6 pt-4 border-t border-gray-700">
                    <h4 class="text-sm font-medium mb-2">Your Information</h4>
                    <div class="space-y-2 text-xs">
                        <div class="flex items-center gap-2" x-show="category || goods">
                            <x-graphic name="cube" class="size-3.5 text-blue-400"/>
                            <span x-text="category + ': ' + goods" class="text-gray-400"></span>
                            <template x-if='hazardous'>
                                <span >hazardous goods</span>
                            </template>
                            
                        </div>
                        <div class="flex items-center gap-2 text-gray-400" x-show="quantity">
                            <flux:icon.scale color='lime' class="size-3.5" />
                            <span x-text="quantity + ' ' + unit  "></span>
                            <p x-text="description"></p>
                        </div>
                        <div class="flex items-center gap-2" x-show="originCountry">
                            <x-graphic name="location-marker" class="size-3.5 text-green-400"/>
                            <span x-text="originAddress + ', '+ originCity + ', '+ originCountry" class="text-gray-400"></span>
                        </div> 
                        <div class="flex items-center gap-2" x-show="destinationCountry">
                            <x-graphic name="location-marker" class="size-3.5 text-red-400"/>
                            <span x-text="destinationAddress + ', '+ destinationCity + ', '+ destinationCountry + ': '+ distance +' km'" ></span>
                        </div>    
                        
                        <div class="flex items-center gap-2" x-show="paymentOption">
                            <x-graphic name="shield-check" class="size-3.5 text-yellow-400"/>
                            <span x-text="paymentOption + ', '+ carriageRate " ></span>
                            <template x-if='selectedTrailer'>
                                @php
                                    $iconName = strtolower(str_replace(' ', '-', $selectedTrailer));
                                @endphp
                                <x-graphic name="{{ $iconName }}" class="size-4" />
                                <span x-text="selectedTrailer"></span>                          
                            </template>
                        </div> 
                        
                        <div class="flex items-center gap-2" x-show="pickupDate">
                            <x-graphic name="calendar-days" class="size-4.5 text-indigo-400"/>
                            <span x-text="'To be picked up on: ' + pickupDate + ' & delivered on:  ' + deliveryDate" ></span>
                        </div>                        

                        <div class="flex items-center gap-2" x-show="fullName">
                            <x-graphic name="user" class="size-3.5 text-blue-400"/>
                            <span x-text="'Contact Person: '+ fullName" class="text-gray-400"></span>
                        </div>

                        <div class="flex items-center gap-2" x-show="email">
                            <x-graphic name="email-open" class="size-3.5 text-orange-400"/>
                            <span x-text="email" class="text-gray-400"></span>
                        </div> 

                        <div class="flex items-center gap-2" x-show="phone">
                            <x-graphic name="phone" class="size-3.5 text-blue-400"/>
                            <span x-text="'Phone: ' + phone" class="text-gray-400"></span>
                        </div>                        
                        <div class="flex items-center gap-2" x-show="whatsapp">
                            <x-graphic name="whatsapp" class="size-3.5 text-green-400"/>
                            <span x-text="'WhatsApp: ' + whatsapp" class="text-gray-400"></span>
                        </div>                        
                       
                    </div>
                </div>                

            </div>
            <div class="p-6 rounded-3xl shadow-xl w-full md:w-1/2 order-2 md:order-1">
                <form wire:submit.prevent="uploadFreight">
                    <div x-show="currentStep=='1'" class="my-2 space-y-2">
                        <flux:text class="text-base my-2">What are you shipping?</flux:text>
                        <flux:select wire:model="category" placeholder="Select Freight Category" indicator="checkbox">
                            <flux:select.option disabled>Choose one</flux:select.option>
                            @foreach ($this->categories as $category)
                                <flux:select.option value="{{$category->name}}">{{$category->name}}</flux:select.option>
                            @endforeach                            
                        </flux:select>
                        <flux:error name="category" />                       

                        <flux:input label="Name of Goods" wire:model="goods"/>

                        <flux:field variant="inline">
                            <flux:switch label='Hazardous Goods?' wire:model="hazardous" />
                        </flux:field>                         
                    </div> 

                    <div  x-show="currentStep==2" class="my-2 space-y-2">
                        <flux:text class="text-base my-2">Tell us more about the goods</flux:text>
                        <flux:radio.group wire:model="unitType" label="Select Weight / Volume of Goods" wire:click="setQuantityUnit">
                            <flux:radio value="weight" label="Weight" checked />
                            <flux:radio value="volume" label="Volume" />
                        </flux:radio.group>                        
                        <flux:input kbd="{{ $unit }}" label="Goods Quantity" wire:model="quantity" type="number"/> 
                        <flux:textarea rows="auto" label="More details about this load.." wire:model="description" />                     
                    </div>

                    <div  x-show="currentStep==3" class="my-2 space-y-2">
                        <flux:text class="text-base my-2">Where are you shipping from?</flux:text>
                        <flux:select wire:model="originCountry" placeholder="Select Origin Country">
                            <flux:select.option></flux:select.option>
                            <flux:select.option value="zimbabwe">Zimbabwe</flux:select.option>
                            <flux:select.option value="south africa">South Africa</flux:select.option>                            
                        </flux:select>
                        <flux:error name='originCountry' />

                        <div x-show="originCountry=='zimbabwe'" class="">
                            <flux:select wire:model="originCity" placeholder="Select Origin Town / City" label="Town / City">
                                <flux:select.option></flux:select.option>
                            @foreach($zimbabweCities as $city)
                                <flux:select.option value="{{ $city }}">{{$city}}</flux:select.option>
                            @endforeach
                            </flux:select>
                        </div>
                        <div x-show="originCountry=='south africa'" class="">
                            <flux:input label="Town / City" wire:model="originCity"/>
                        </div> 
                        <flux:input label="Physical Street Address" wire:model="originAddress"/>                          
                    </div>  
                    
                    <div  x-show="currentStep==4" class="my-2 space-y-2">
                        <flux:text class="text-base my-2">Where are you shipping to?</flux:text>
                        <flux:select wire:model="destinationCountry" placeholder="Select Destination Country">
                            <flux:select.option></flux:select.option>
                            <flux:select.option value="zimbabwe">Zimbabwe</flux:select.option>
                            <flux:select.option value="south africa">South Africa</flux:select.option>
                        </flux:select>
                        <flux:error name='destinationCountry'/>

                        <div x-show="destinationCountry=='zimbabwe'" class="">
                            <flux:select wire:model="destinationCity" placeholder="Select Destination Town / City" label="Town / city">
                                <flux:select.option></flux:select.option>
                            @foreach($zimbabweCities as $city)
                                <flux:select.option value="{{ $city }}">{{$city}}</flux:select.option>
                            @endforeach
                            </flux:select>
                        </div>

                        <div x-show="destinationCountry=='south africa'" class="">
                            <flux:input label="Town / city" wire:model="destinationCity"/>
                        </div>
                        <flux:input label="Physical Street Address" wire:model="destinationAddress"/>
                        <flux:input kbd='km' label="Distance" wire:model="distance"/>
                    </div>  
                        
                    <div  x-show="currentStep==5" class="my space-y-2">
                        <flux:text class="text-base my-2">Preferences</flux:text>
                            <flux:radio.group wire:model="paymentOption" label="Preferred Payment Option" variant="segmented" size="sm">
                                <flux:radio label="Full Budget" value="full_budget"/>
                                <flux:radio label="Rate of Carriage" value="rate_of_carriage"/>
                            </flux:radio.group>   
                            <flux:input kbd="US$" label="Budget Amount" wire:model="carriageRate"/>  
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
                                            <input id="trailer-{{ $trailer->id}}" type="radio" wire:model="selectedTrailer" value="{{ $trailer->name}}" class="sr-only" />
                                        </div>
                                    </label>
                                @endforeach
                            </div> 
                    </div>
                    <div  x-show="currentStep==6" class="my-2 space-y-2">
                        <flux:text class="text-base my-2">Transportation Dates</flux:text>
                        <flux:input type="date" label="Freight Pickup Date" wire:model="pickupDate"/>
                        <flux:input type="date" label="Expected Delivery Date" wire:model="deliveryDate"/>
                    </div>
                    <div  x-show="currentStep==7" class="my-2 space-y-2">
                        <flux:text class="text-base my-2">Contact Person</flux:text>
                        <flux:switch label='Me?' wire:model='self' wire:click='selfContact' />
                        <flux:input label="Full Name" wire:model="fullName" x-model='fullName'  :readonly='$self' />
                        <flux:input type='email' label="Email" wire:model="email" x-model='email'  :readonly='$self' />
                        <flux:input type="tel" label="Contact Phone" wire:model="phone" x-model='phone'  :readonly='$self' />
                        <flux:input type="tel" label="Whatsapp (optional)" wire:model="whatsapp" x-model='whatsapp'  :readonly='$self' />

                    </div>
                    
                    <div class="flex justify-between mt-8 space-x-2"> 
                        <flux:button wire:click="previousStep" x-show="currentStep > 1" variant='primary' color='zinc' icon='chevron-double-left' class='ml-auto'>                            
                            Back
                        </flux:button>

                        <flux:button wire:click="nextStep" x-show="currentStep < 7" variant='primary' color='cyan' icon='chevron-double-right' class='ml-auto'>
                            Next
                        </flux:button>

                        <flux:button wire:click="saveDraft" x-show="currentStep >= 6" variant='primary' color='lime' icon='server' class='ml-auto'>
                            Save Draft
                        </flux:button>

                        <flux:button wire:click="submit" x-show="currentStep == 7" variant='primary' color='green' icon='paper-airplane' class='ml-auto'>
                            Submit
                        </flux:button>
                        
                    </div>
                    
                    <div class="flex justify-center mt-8 gap-2">
                        <div class="h-2 w-8 rounded-full bg-blue-500" :class="{'w-2 bg-gray-600': currentStep != 1, 'w-8 bg-blue-500': currentStep == 1}"></div>
                        <div class="h-2 w-2 rounded-full" :class="{'w-2 bg-gray-600': currentStep != 2, 'w-8 bg-blue-500': currentStep == 2}"></div>
                        <div class="h-2 w-2 rounded-full" :class="{'w-2 bg-gray-600': currentStep != 3, 'w-8 bg-blue-500': currentStep == 3}"></div>
                        <div class="h-2 w-2 rounded-full" :class="{'w-2 bg-gray-600': currentStep != 4, 'w-8 bg-blue-500': currentStep == 4}"></div>
                        <div class="h-2 w-2 rounded-full" :class="{'w-2 bg-gray-600': currentStep != 5, 'w-8 bg-blue-500': currentStep == 5}"></div>
                        <div class="h-2 w-2 rounded-full" :class="{'w-2 bg-gray-600': currentStep != 6, 'w-8 bg-blue-500': currentStep == 6}"></div>
                        <div class="h-2 w-2 rounded-full" :class="{'w-2 bg-gray-600': currentStep != 7, 'w-8 bg-blue-500': currentStep == 7}"></div>
                    </div>                    
                </form>

            </div>            
        </div>
    </div>
</div>
