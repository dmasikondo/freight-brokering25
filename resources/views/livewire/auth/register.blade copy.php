<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
//use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Illuminate\Support\Str;

new #[Layout('components.layouts.auth')] class extends Component {
    public $currentStep=1;
    public string $company_name = 'taraz investments';
    public string $first_name = '';
    public string $surname = '';
    public string $contact_phone = '';
    public string $phone_type = '';
    public string $whatsapp = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $carrier_shipper;
    public string $ownership_type;

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        // $validated = $this->validate([
        //     'company_name' => ['required', 'string', 'max:255'],
        //     'first_name' => ['required', 'string', 'max:255'],
        //     'surname' => ['required', 'string', 'max:255'],
        //     'contact_phone' => ['required', 'string', 'max:30'],
        //     'phone_type' => ['required', 'string','in:mobile,landline,other', 'max:20'],
        //     'whatsapp' => ['nullable', 'string', 'max:30'],
        //     'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
        //     'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        // ]);

        // if (empty($this->company)) {
        //     $validated['company'] = $validated['first_name'] . ' ' . $validated['last_name'];
        // }
        
        // $validated['contact_person'] = $validated['first_name'] . ' ' . $validated['last_name'];
        // $validated['slug'] = Str::slug($validated['first_name'] . ' ' . $validated['last_name']) . '-' . uniqid();
        // $validated['password'] = Hash::make($validated['password']);
       

        event(new Registered(($user = User::create($validated))));
        Auth::login($user);
        $this->redirectIntended(route('dashboard', absolute: false), navigate: true);
    }

    public function nextStep()
    {
       //$this->validateStep();
       return  $this->currentStep = $this->currentStep+1;
        // if ($this->currentStep < 3) {
        //    return  $this->currentStep = $this->currentStep + 1;
        //     //dd($this->currentStep);
        // }
        
    }  

    public function previousStep()
    {
        $this->currentStep = $this->currentStep-1;
    }
    
    // public function validateStep()
    // {
    //     if ($this->currentStep === 1) {
    //         $this->validateOnly('name');
    //     } elseif ($this->currentStep === 2) {
    //         $this->validateOnly('email');
    //     } elseif ($this->currentStep === 3) {
    //         $this->validateOnly('password');
    //     }
    // }     
}; ?>

<div id="contact"
     x-data="{
         {{-- ...registrationForm(), --}}
         currentStep: @entangle('currentStep'),
         first_name: @entangle('first_name'),
         surname: @entangle('surname'),
         phone_type: @entangle('phone_type'),
         contact_phone: @entangle('contact_phone'),
         whatsapp: @entangle('whatsapp'),
         company_name: @entangle('company_name'),
         ownership_type: @entangle('ownership_type'),
         carrier_shipper: @entangle('carrier_shipper'),
         email: @entangle('email'),
         password: @entangle('password'),
         password_confirmation: @entangle('password_confirmation')
     }"
     x-cloak
     class="min-h-screen bg-gradient-to-b from-[#0f172a] to-[#1e293b] text-white p-4 flex flex-col items-center pb-8">
    <div class="w-full max-w-6xl mt-8">
        <h1 class="text-5xl font-bold text-center bg-gradient-to-r from-blue-400 to-purple-500 text-transparent bg-clip-text mb-4">
            Ship Smarter!!
        </h1>
        <p class="text-center text-gray-400 text-xl mb-12">
            Register as a Shipper or a Carrier to get started.
        </p>

        <div class="flex flex-col md:flex-row gap-8 w-full">
            <!-- Right Column (Info) - First on mobile -->
            <div class="bg-gray-800 p-6 rounded-3xl shadow-xl w-full md:w-1/2 order-2 md:order-1">
                <!-- Step 1: Full Name -->
                <x-steps.registration-steps
                    :step="1"
                    icon="user"
                    title="Full Name"
                    description="Enter your legal name as it appears on official documents."
                    usageTitle="Used for:"
                    :items="['Account verification', 'Shipping documentation']"
                />
                <!-- Step 2: Contact Info -->
                <x-steps.registration-steps
                    :step="2"
                    icon="phone"
                    title="Contact Info"
                    description="Primary contact number and optional WhatsApp."
                    usageTitle="We'll use this to:"
                    :items="['Confirm shipments', 'Send delivery updates']"
                />

                <!-- Step 3: Business Info -->
                <x-steps.registration-steps
                    :step="3"
                    icon="building-office-2"
                    title="Business Info"
                    description="Tell us about your company and role."
                    usageTitle="Why we need this:"
                    :items="['Verify business credentials', 'Match with relevant partners']"
                />

                <!-- Step 4: Login Details -->
                <div x-show="currentStep === 4" class="space-y-3">
                    <div class="flex items-center gap-2">
                        <x-graphic name="lock-closed" class="size-5 text-blue-400"/>
                        <h3 class="text-lg font-semibold">Login Details</h3>
                    </div>
                    <p class="text-sm text-gray-300">Create secure credentials for your account.</p>
                    <div class="bg-gray-700/50 p-3 rounded-lg text-xs space-y-1">
                        <p class="text-gray-400">A secure password must have at least:</p>                        
                        <div class="flex items-center gap-1.5" :class="{'text-green-400': password.length >= 8, 'text-gray-300': password.length < 8}">
                            {{-- <x-graphic name="lock-closed" class="size-5 text-blue-400" :class="{'text-green-400': password.length >= 8, 'text-gray-500': password.length < 8}"/> --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" :class="{'text-green-400': password.length >= 8, 'text-gray-500': password.length < 8}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            8+ characters
                        </div>
                        <div class="flex items-center gap-1.5" :class="{'text-green-400': /[A-Z]/.test(password), 'text-gray-300': !/[A-Z]/.test(password)}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" :class="{'text-green-400': /[A-Z]/.test(password), 'text-gray-500': !/[A-Z]/.test(password)}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            1 uppercase letter
                        </div>
                        <div class="flex items-center gap-1.5" :class="{'text-green-400': /[0-9]/.test(password), 'text-gray-300': !/[0-9]/.test(password)}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" :class="{'text-green-400': /[0-9]/.test(password), 'text-gray-500': !/[0-9]/.test(password)}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            1 number
                        </div>
                    </div>
                </div>

                <!-- Compact Preview Section -->
                <div class="mt-6 pt-4 border-t border-gray-700">
                    <h4 class="text-sm font-medium mb-2">Your Information</h4>
                    <div class="space-y-2 text-xs">
                        <div class="flex items-center gap-2" x-show="first_name || surname">
                            <x-graphic name="user" class="size-3.5 text-blue-400"/>
                            <span x-text="first_name + ' ' + surname" class="text-gray-400"></span>
                        </div>
                        <div class="flex items-center gap-2" x-show="contact_phone">
                            <x-graphic name="phone" class="size-3.5 text-green-400"/>
                            <span x-text="phone_type + ': ' + contact_phone" class="text-gray-400"></span>
                        </div>
                        <div class="flex items-center gap-2" x-show="whatsapp">
                            <x-graphic name="whatsapp" class="size-3.5 text-green-400"/>
                            <span x-text="'WhatsApp: ' + whatsapp" class="text-gray-400"></span>
                        </div>
                        <div class="flex items-center gap-2" x-show="company_name">
                            <x-graphic name="building-office-2" class="size-3.5 text-yellow-400"/>
                            <span x-text="company_name" class="text-gray-400"></span>
                        </div>
                        <div class="flex items-center gap-2" x-show="ownership_type">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" :class="{'text-yellow-400': ownership_type === 'real_owner', 'text-blue-400': ownership_type === 'broker_agent'}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path x-show="ownership_type === 'real_owner'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                <path x-show="ownership_type === 'broker_agent'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                            <span x-text="ownership_type === 'real_owner' ? 'Real Owner' : 'Broker/Agent'" class="text-gray-400"></span>
                        </div>
                        <div class="flex items-center gap-2" x-show="carrier_shipper">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" :class="{'text-teal-400': carrier_shipper === 'carrier', 'text-orange-400': carrier_shipper === 'shipper'}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path x-show="carrier_shipper === 'carrier'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                <path x-show="carrier_shipper === 'carrier'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                <path x-show="carrier_shipper === 'shipper'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <span x-text="carrier_shipper === 'carrier' ? 'Carrier' : 'Shipper'" class="text-gray-400"></span>
                        </div>
                        <div class="flex items-center gap-2" x-show="email">
                            <x-graphic name="email-open" class="size-3.5 text-red-400"/>
                            <span x-text="email" class="text-gray-400"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Left Column (Form) - Second on mobile -->
            <div class="bg-gray-800 p-8 rounded-3xl shadow-xl w-full md:w-1/2 order-1 md:order-2">
                <form wire:submit.prevent>
                    <div x-show="currentStep===1">
                        <h2 class="text-2xl font-bold mb-6">What's your name?</h2>
                        <div class="flex flex-col md:flex-row gap-4">
                            <x-form.input
                                placeholder="First Name" 
                                model="first_name"
                                wire:model="first_name"
                            />
                            <x-form.input
                                placeholder="Surname" 
                                model="surname"
                                wire:model='surname'
                            />
                        </div>
                    </div>

                    <div x-show="currentStep===2">
                        <h2 class="text-2xl font-bold mb-6">What's your contact details?</h2>
                        <x-form.select
                            placeholder="Phone Type"
                            model="phone_type"                        
                            :options="[
                                'mobile' => 'Mobile',
                                'landline' => 'Landline', 
                                'other' => 'Other'
                            ]"
                            class="mb-4"

                        />
                        <x-form.input 
                            placeholder="Contact Phone Number" 
                            model="contact_phone"
                            class="mb-4"
                        />  
                        <x-form.input 
                            placeholder="WhatsApp (optional)" 
                            model="whatsapp"
                        />
                    </div>

                    <div x-show="currentStep === 3">
                        <h2 class="text-2xl font-bold mb-6">Company Overview</h2>
                        <x-form.input
                            placeholder="Company Name" 
                            model="company_name"
                        />                       
                        
                        <div class="flex flex-col border-b border-gray-600 mb-4 pb-4">
                            <h3 class="text-lg font-bold text-gray-400">Ownership Type</h3>
                            <div class="flex items-center mt-2">
                                <input type="radio" x-model="ownership_type" value="real_owner" id="real_owner" class="mr-2" />
                                <label for="real_owner" class="text-gray-300 flex items-center gap-2">
                                    <x-graphic name="shield-check" class="size-5 text-yellow-400"/>
                                    Real Owner
                                </label>
                            </div>
                            <div class="flex items-center mt-2">
                                <input type="radio" x-model="ownership_type" value="broker_agent" id="broker_agent" class="mr-2" />
                                <label for="broker_agent" class="text-gray-300 flex items-center gap-2">
                                    <x-graphic name="exchange" class="size-5 text-blue-400"/>
                                    Broker / Agent
                                </label>
                            </div>
                        </div>
                        <div class="flex flex-col border-b border-gray-600 mb-4 pb-4">
                            <h3 class="text-lg font-bold text-gray-400">Customer Type</h3>
                            <div class="flex flex-col mt-2">
                                <div class="flex items-center">
                                    <input type="radio" x-model="carrier_shipper" value="carrier" id="carrier" class="mr-2" />
                                    <label for="carrier" class="text-gray-300 flex items-center gap-2">
                                        <x-graphic name="exchange" class="size-5 text-teal-400"/>
                                        Carrier
                                    </label>
                                </div>
                                <p x-show="carrier_shipper === 'carrier'" class="text-xs italic text-gray-400 ml-7 mt-1">You provide trucks/trailers to move goods</p>
                            </div>
                            <div class="flex flex-col mt-2">
                                <div class="flex items-center">
                                    <input type="radio" x-model="carrier_shipper" value="shipper" id="shipper" class="mr-2" />
                                    <label for="shipper" class="text-gray-300 flex items-center gap-2">
                                        <x-graphic name="cube" class="size-5 text-orange-400"/>
                                        Shipper
                                    </label>
                                </div>
                                <p x-show="carrier_shipper === 'shipper'" class="text-xs italic text-gray-400 ml-7 mt-1">You have goods that need to be transported</p>
                            </div>
                        </div>
                    </div>

                    <div x-show="currentStep === 4">
                        <h2 class="text-2xl font-bold mb-6">Create your login</h2>
                        <x-form.input
                            type="email"
                            placeholder="Email" 
                            model="email"
                            class="mb-4"
                        />  
                        <x-form.input
                        type="password"
                            placeholder="Password" 
                            model="password"
                            class="mb-4"
                        /> 
                        <x-form.input
                            type="password"
                            placeholder="Repeat Password" 
                            model="password_confirmation"
                        />
                    </div>

                    <div class="flex justify-between mt-8">
                        <button   class="px-6 py-3 bg-gray-700 hover:bg-gray-600 rounded-xl transition duration-300">
                            <span x-text="currentStep + 'Step'"></span>
                        </button>                        
                        <button wire:click="previousStep" x-show="currentStep > 1" class="px-6 py-3 bg-gray-700 hover:bg-gray-600 rounded-xl transition duration-300">
                            Back
                        </button>
                        
                        <button  wire:click="nextStep" x-show="currentStep < 4" class="px-6 py-3 bg-blue-600 hover:bg-blue-500 rounded-xl transition duration-300 ml-auto">
                            Next
                        </button>
                        
                        <button wire:click="register" x-show="currentStep === 4" class="px-6 py-3 bg-green-600 hover:bg-green-500 rounded-xl transition duration-300 flex items-center gap-2">
                            <x-graphic name="paper-airplane" class="size-5"/>
                            Submit
                        </button>
                    </div>
                    
                    <div class="flex justify-center mt-8 gap-2">
                        <div class="h-2 w-8 rounded-full bg-blue-500" :class="{'w-2 bg-gray-600': currentStep !== 1, 'w-8 bg-blue-500': currentStep === 1}"></div>
                        <div class="h-2 w-2 rounded-full" :class="{'w-2 bg-gray-600': currentStep !== 2, 'w-8 bg-blue-500': currentStep === 2}"></div>
                        <div class="h-2 w-2 rounded-full" :class="{'w-2 bg-gray-600': currentStep !== 3, 'w-8 bg-blue-500': currentStep === 3}"></div>
                        <div class="h-2 w-2 rounded-full" :class="{'w-2 bg-gray-600': currentStep !== 4, 'w-8 bg-blue-500': currentStep === 4}"></div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // function registrationForm() {
        //     return {
        //         currentStep: 1,
        //         first_name: '',
        //         surname: '',
        //         phone_type: '',
        //         contact_phone: '',
        //         whatsapp: '',
        //         company_name: '',
        //         ownership_type: '',
        //         carrier_shipper: '',
        //         email: '',
        //         password: '',
        //         password_confirmation: '',
        //         nextStep() {
        //             if (this.currentStep < 4) {
        //                 this.currentStep++;
        //             }
        //         },
        //         previousStep() {
        //             if (this.currentStep > 1) {
        //                 this.currentStep--;
        //             }
        //         },
        //         submitForm() {
        //             console.log('Form submitted:', {
        //                 first_name: this.first_name,
        //                 surname: this.surname,
        //                 phone_type: this.phone_type,
        //                 contact_phone: this.contact_phone,
        //                 whatsapp: this.whatsapp,
        //                 company_name: this.company_name,
        //                 ownership_type: this.ownership_type,
        //                 carrier_shipper: this.carrier_shipper,
        //                 email: this.email,
        //                 password: this.password,
        //                 password_confirmation: this.password_confirmation,
        //             });
        //             alert('Form submitted successfully!');
        //         }
        //     };
        // }
    </script>
</div>

