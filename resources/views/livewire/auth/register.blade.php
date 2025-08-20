<?php

namespace App\Livewire;

use App\Models\Role;
use App\Models\User;
use App\Services\UserRegistrationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    // Component state properties...
    public bool $isStaffRegistration = false;
    public $currentStep = 1;
    public $allowedRoles = []; 
    public string $role = '';
    public string $first_name = '';
    public string $surname = '';
    public string $contact_phone = '';
    public string $phone_type = '';
    public string $whatsapp = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $company_name = '';
    public string $customer_type = '';
    public string $ownership_type = '';
    public string $country = '';
    public string $city = '';
    public string $address = '';
    public $zimbabweCities = [];
    public ?string $slug = null; // Added to handle editing

    // The service class is injected here
    protected UserRegistrationService $userRegistrationService;

    public function boot(UserRegistrationService $userRegistrationService)
    {
        $this->userRegistrationService = $userRegistrationService;
    }

    public function mount(?string $role = null, ?string $slug = null): void
    {       
        $this->zimbabweCities = \App\Models\ZimbabweCity::orderBy('name')->pluck('name', 'name')->toArray();
        
       
        if (Auth::check()) {
            
            $this->isStaffRegistration = true;
            $this->allowedRoles = $this->userRegistrationService->getAllowedRolesForUser(Auth::user());
        } else {
            // Self-registration is for shippers and carriers
            $this->allowedRoles = Role::whereIn('name', ['shipper', 'carrier'])->pluck('name', 'name');
        }

        if ($role) {
            $this->role = $role;
            $this->currentStep = 1;
        }

        // Editing logic
        if ($slug) {            
            $user = User::where('slug', $slug)->firstOrFail();
            $this->authorize('update', $user);
            $fullName = $user->contact_person;

            // Split the full name by spaces
            $nameParts = explode(' ', $fullName);

            // Get the last word as the surname
            $this->surname = array_pop($nameParts);

            // Join the remaining words as the first name
            $this->first_name = implode(' ', $nameParts); 

            $this->slug = $user->slug;
            $this->contact_phone = $user->contact_phone;
            $this->phone_type = $user->phone_type;
            $this->whatsapp = $user->whatsapp ?? '';
            $this->email = $user->email;
            $this->company_name = $user->organisation ?? '';

        // Populate customer_type and ownership_type from the roles relationship
        if ($user->roles->isNotEmpty()) {
            $role = $user->roles->first();
            $this->customer_type = $role->name;
            $this->ownership_type = $role->pivot->classification ?? '';
        }

        // Populate address, city, and country from the buslocation relationship
        if ($user->buslocation->isNotEmpty()) {
            $buslocation = $user->buslocation->first();
            $this->country = $buslocation->country ?? '';
            $this->city = $buslocation->city ?? '';
            $this->address = $buslocation->address ?? '';
        }            
            $this->password = '';
            $this->password_confirmation = '';
        }
    }

    protected function rules(): array
    {
        return [
            // 'role' => ['required', 'string', 'in:' . implode(',', $this->allowedRoles->toArray())],
            'first_name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'contact_phone' => ['required', 'string', 'regex:/^(\+\d{1,3}[- ]?)?\d{7,15}$/'],
            'phone_type' => ['required', 'string', 'in:mobile,landline,other', 'max:20'],
            'whatsapp' => ['nullable', 'regex:/^(\+\d{1,3}[- ]?)?\d{7,15}$/'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class, 'email')->ignore($this->slug, 'slug')],
            'password' => [$this->slug ? 'nullable' : 'required', 'string', 'confirmed', Rules\Password::defaults()],
            'customer_type' => ['required', 'string'],
            'ownership_type' => ['required_if:customer_type,shipper,carrier'],
            'company_name' => ['required_if:customer_type,shipper,carrier', 'nullable', 'string', 'max:255'],
            'country' => ['required_if:customer_type,shipper,carrier', 'nullable', 'string', 'in:Zimbabwe,South Africa'],
            'address' => ['required_if:customer_type,shipper,carrier', 'nullable', 'string', 'max:255'],
            'city' => ['required_if:customer_type,shipper,carrier', 'nullable', 'string'],
        ];
    }

    public function register(): void
    {
        $validated = $this->validate();
        // Pass the user model if it exists, otherwise pass null       
        $userToregister = $this->slug ? User::where('slug', $this->slug)->first() : null;

         if ($this->slug) {
          
        $userToUpdate = User::where('slug', $this->slug)->firstOrFail();     

        $this->userRegistrationService->registerUser($validated, auth()->user(), $userToUpdate);
            // Redirect back after editing
            $this->redirect(route('users.index'), navigate: true);

        } else {
            // Existing registration logic
            $creator = $this->isStaffRegistration ? Auth::user() : null;
            $user = $this->userRegistrationService->registerUser($validated, $creator);

            if (!$user) {
                $this->addError('general', 'Failed to register user. Please check permissions or try again.');
                return;
            }

            if ($this->isStaffRegistration) {
                $this->redirect(route('users.index'), navigate: true);
            } else {
                Auth::login($user);
                $this->redirectIntended(route('dashboard', absolute: false), navigate: true);
            }
        }
    }
    
    // Multi-step form navigation methods
    public function nextStep(): void
    {
        $this->validateStep();
        if ($this->currentStep < 5) {
            if ($this->currentStep === 3) {
                if (($this->customer_type !== 'shipper') && ($this->customer_type !== 'carrier')) {
                    $this->currentStep = $this->currentStep + 1;
                }
            }
            $this->currentStep = $this->currentStep + 1;
        }
    }
    
    public function previousStep(): void
    {
        $this->validateStep();
        if ($this->currentStep > 1) {
            if ($this->currentStep === 5) {
                if ($this->customer_type !== 'carrier' && $this->customer_type !== 'shipper') {
                    $this->currentStep = 4;
                }
            }
            $this->currentStep = $this->currentStep - 1;
        }
    }
    
    public function validateStep(): void
    {
        if ($this->currentStep === 1) {
            $this->validateOnly('first_name');
            $this->validateOnly('surname');
        }
        if ($this->currentStep === 2) {
            $this->validateOnly('phone_type');
            $this->validateOnly('contact_phone');
            $this->validateOnly('whatsapp');
        }
        if ($this->currentStep === 3) {
            if ($this->customer_type === 'carrier' || $this->customer_type === 'shipper') {
                $this->validateOnly('company_name');
                $this->validateOnly('ownership_type');
            }
            $this->validateOnly('customer_type');
        }
        if ($this->currentStep === 4) {
            $this->validateOnly('country');
            $this->validateOnly('city');
            $this->validateOnly('address');
        }
    }
};?>

<div id="contact" x-data="
        { currentStep: @entangle('currentStep'), 
            isStaffRegistration: @entangle('isStaffRegistration'), 
            role: @entangle('role'),
            currentStep: @entangle('currentStep'),
            first_name: @entangle('first_name'),
            surname: @entangle('surname'),
            phone_type: @entangle('phone_type'),
            contact_phone: @entangle('contact_phone'),
            whatsapp: @entangle('whatsapp'),
            company_name: @entangle('company_name'),
            ownership_type: @entangle('ownership_type'),
            customer_type: @entangle('customer_type'),
            email: @entangle('email'),
            password: @entangle('password'),
            password_confirmation: @entangle('password_confirmation'),
            country: @entangle('country'),
            city: @entangle('city'),
            address: @entangle('address'),            
         }" 
         x-cloak
    class="min-h-screen bg-gradient-to-b from-[#0f172a] to-[#1e293b] text-white p-4 flex flex-col items-center pb-8">

    <div class="w-full max-w-6xl mt-8">
        <h1 class="text-5xl font-bold text-center bg-gradient-to-r from-blue-400 to-purple-500 text-transparent bg-clip-text mb-4">
            Ship Smarter!!
        </h1>
        <p class="text-center text-gray-400 text-xl mb-12">
            <span x-show="!isStaffRegistration">Register as a Shipper or a Carrier to get started.</span>
            <span x-show="isStaffRegistration">Add a new user.</span>
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

                <!-- Step 4: Business Info -->
                <x-steps.registration-steps
                    :step="4"
                    icon="location-marker"
                    title="Business Location"
                    description="Tell us about your company location"
                    usageTitle="Our reason for asking:"
                    :items="['Verify business credentials', 'Logistics and Shipping Optimization']"
                />                

                <!-- Step 5: Login Details -->
                <div x-show="currentStep === 5" class="space-y-3">
                    <div class="flex items-center gap-2">
                        <x-graphic name="lock-closed" class="size-5 text-blue-400"/>
                        <h3 class="text-lg font-semibold">Login Details</h3>
                    </div>
                    <p class="text-sm text-gray-300">Create secure credentials for the account.</p>
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
                        <div class="flex items-center gap-2" x-show="customer_type">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" :class="{'text-teal-400': customer_type === 'carrier', 'text-orange-400': customer_type === 'shipper'}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path x-show="customer_type === 'carrier'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                <path x-show="customer_type === 'carrier'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                <path x-show="customer_type === 'shipper'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <span x-text="customer_type === 'carrier' ? 'Carrier' : 'Shipper'" class="text-gray-400"></span>
                        </div>
                        <div class="flex items-center gap-2" x-show="email">
                            <x-graphic name="email-open" class="size-3.5 text-red-400"/>
                            <span x-text="email" class="text-gray-400"></span>
                        </div>
                        <div class="flex items-center gap-2" x-show="country">
                            <x-graphic name="location-marker" class="size-3.5 text-green-400"/>
                            <span x-text="address + ', '+ city + ', '+ country" class="text-gray-400"></span>
                        </div>                        
                    </div>
                </div>
            </div>

            <!-- Left Column (Form) - Second on mobile -->
            <div class="bg-gray-800 p-8 rounded-3xl shadow-xl w-full md:w-1/2 order-1 md:order-2">
                <form wire:submit.prevent>
                    <div x-show="currentStep===1">
                        <h2 class="text-2xl font-bold mb-6">
                            What's <span>{{$isStaffRegistration? "the user's": 'your'}}</span> name?

                        </h2>
                        <div class="flex flex-col md:flex-row gap-4">
                            <x-form.input
                                placeholder="First Name" 
                                model="first_name"
                                wire:model="first_name"
                                @class(['border-red-500'=>$errors->has('first_name')])
                            />
                           <x-form.input-error field="first_name"/> 
                            <x-form.input
                                placeholder="Surname" 
                                model="surname"
                                wire:model='surname'
                                @class(['border-red-500'=>$errors->has('surname')])
                            />
                            <x-form.input-error field="surname"/>                    
                        </div>
                    </div>

                    <div x-show="currentStep===2">
                        <h2 class="text-2xl font-bold mb-6">
                             What's <span>{{$isStaffRegistration? "the user's": 'your'}}</span> contact details?
                        </h2>
                        <x-form.select
                            @class(['border-red-500'=>$errors->has('phone_type'), 'mb-4']) 
                            placeholder="Phone Type"
                            model="phone_type"  
                            wire:model="phone_type"                      
                            :options="[
                                'mobile' => 'Mobile',
                                'landline' => 'Landline', 
                                'other' => 'Other'
                            ]"
                                                       
                        />
                        <x-form.input-error field="phone_type"/>   

                        <x-form.input 
                            placeholder="Contact Phone Number" 
                            model="contact_phone"
                            wire:model="contact_phone"
                            @class(['border-red-500'=>$errors->has('contact_phone'),'mb-4'])
                            pattern="^\+?[1-9]\d{6,14}$"
                            required
                            title="Enter a valid phone number (e.g., +2637720000)"                           
                        /> 
                        <x-form.input-error field="contact_phone"/> 

                        <x-form.input 
                            placeholder="WhatsApp (optional)" 
                            model="whatsapp"
                            wire:model="whatsapp"
                            @class(['border-red-500'=>$errors->has('whatsapp')])
                            pattern="^\+?[1-9]\d{6,14}$"
                            required
                            title="Enter a valid whatsApp number (e.g., +2637720000)"                             
                        />
                        <x-form.input-error field="whatsapp"/>
                    </div>

                    <div x-show="(currentStep === 3 && !isStaffRegistration)">
                        <h2 class="text-2xl font-bold mb-6">Company Overview</h2>
                            <x-form.input
                                placeholder="Company Name" 
                                model="company_name"
                                wire:model="company_name"
                                @class(['border-red-500'=>$errors->has('company_name')])
                            />
                            <x-form.input-error field="company_name"/>   
                        <div @class(['border-red-500 border'=>$errors->has('customer_type'), 'mr-2',"flex flex-col border-b border-gray-600 mb-4 pb-4"])>
                            <h3 class="text-lg font-bold text-gray-400">Customer Type</h3>
                            <div class="flex flex-col mt-2">
                                <div class="flex items-center">
                                    <input type="radio" x-model="customer_type" value="carrier" id="carrier" class="mr-2" wire:model="customer_type" />
                                    <label for="carrier" class="text-gray-300 flex items-center gap-2">
                                        <x-graphic name="exchange" class="size-5 text-teal-400"/>
                                        Carrier
                                    </label>
                                </div>
                                <p x-show="customer_type === 'carrier'" class="text-xs italic text-gray-400 ml-7 mt-1">You provide trucks/trailers to move goods</p>
                            </div>
                            <div class="flex flex-col mt-2">
                                <div class="flex items-center">
                                    <input type="radio" x-model="customer_type" value="shipper" id="shipper" class="mr-2" wire:model="customer_type"/>
                                    <label for="shipper" class="text-gray-300 flex items-center gap-2">
                                        <x-graphic name="cube" class="size-5 text-orange-400"/>
                                        Shipper
                                    </label>
                                </div>
                                <p x-show="customer_type === 'shipper'" class="text-xs italic text-gray-400 ml-7 mt-1">You have goods that need to be transported</p>
                            </div>
                            <x-form.input-error field="customer_type"/>
                        </div> 
                        <div  @class(['border-red-500 border'=>$errors->has('ownership_type'), 'mr-2',"flex flex-col border-b border-gray-600 mb-4 pb-4"])>
                            <h3 class="text-lg font-bold text-gray-400">
                                 Select User Ownership Type
                            </h3>
                            <div class="flex items-center mt-2">
                                <input type="radio" x-model="ownership_type" value="real_owner" id="real_owner"  wire:model="ownership_type" />
                                <label for="real_owner" class="text-gray-300 flex items-center gap-2">
                                    <x-graphic name="shield-check" class="size-5 text-yellow-400"/>
                                    Real Owner
                                </label>
                            </div>
                            <div class="flex items-center mt-2">
                                <input type="radio" x-model="ownership_type" value="broker_agent" id="broker_agent" class="mr-2" wire:model="ownership_type" />
                                <label for="broker_agent" class="text-gray-300 flex items-center gap-2">
                                    <x-graphic name="exchange" class="size-5 text-blue-400"/>
                                    Broker / Agent
                                </label>

                            </div>
                            <x-form.input-error field="ownership_type"/> 
                        </div>                                              
                    </div>

                    <div x-show="(currentStep === 3 && isStaffRegistration)">
                        <h2 class="text-2xl font-bold mb-6">Assign the User Role</h2>
                        <div @class(['border-red-500 border'=>$errors->has('customer_type'), 'mr-2',"flex flex-col border-b border-gray-600 mb-4 pb-4"])>
                            <div class="">
                                @foreach ($allowedRoles as $title )
                                <div class="mb-4">
                                    <x-form.radio-button
                                        id="role-{{ $title }}"
                                        label="{{ $title }}"
                                        icon="shield-check"
                                        value="{{ $title }}"
                                        model="customer_type"
                                        @class([
                                            'border-red-500' => $errors->has('customer_type')
                                        ]) {{-- Or use a conditional class --}}
                                    />                                      
                                    <p x-show="customer_type === 'carrier' && '{{ $title }}' === 'carrier'" class="text-xs italic text-gray-400 ml-7 mt-1">
                                        provides trucks/trailers to move goods
                                    </p>
                                    <p x-show="customer_type === 'shipper' && '{{ $title }}' === 'shipper'" class="text-xs italic text-gray-400 ml-7 mt-1">
                                        has goods that need to be transported
                                    </p> 
                                <!-- Ownership Type and Company Name Section Section -->
                                    <div  x-show="customer_type === 'carrier' && '{{ $title }}' === 'carrier'" class="border border-gray-400 p-2 flex flex-wrap gap-2 mt-4 space-y-2">
                                        <div class="w-full">
                                            @include('partials.forms.ownership_type')  
                                        </div>
                                       <div class="w-full">
                                            @include('partials.forms.company_name') 
                                        </div>                                      
                                        
                                                                                                         
                                    </div>  
                                    <div  x-show="customer_type === 'shipper' && '{{ $title }}' === 'shipper'" class="border border-gray-400 p-2 flex flex-wrap gap-2 mt-4 space-y-2">  
                                        <div class="w-full">
                                            @include('partials.forms.ownership_type')  
                                        </div>
                                       <div class="w-full">
                                            @include('partials.forms.company_name') 
                                        </div>                                                                                                       
                                    </div>                                                                                           
                                </div>
                                @endforeach    
                            </div>
                        </div>
                      
                        

                    </div>                    
                    
                    <div x-show="currentStep===4   && (customer_type ==='carrier' || customer_type ==='shipper')" x-cloak>
                        <h2 class="text-2xl font-bold mb-6">Where Located?</h2>

                        <x-form.select
                            @class(['border-red-500' => $errors->has('country'), 'mb-4'])
                            placeholder="Country"
                            wire:model.live="country"
                            :options="['Zimbabwe' => 'Zimbabwe', 'South Africa' => 'South Africa']"
                        />
                        <x-form.input-error field="country" />

                        <div x-show="country==='Zimbabwe'">
                            <x-form.select
                                @class(['border-red-500' => $errors->has('city'), 'mb-4'])
                                placeholder="City"
                                wire:model="city"
                                :options="$zimbabweCities"
                            />
                            <x-form.input-error field="city" />
                        </div>

                        <div x-show="country==='South Africa'">
                            <x-form.input 
                                placeholder="City"
                                model="city"
                                wire:model="city"
                                @class(['border-red-500' => $errors->has('city'), 'mb-4'])
                            />
                            <x-form.input-error field="city" />
                        </div>

                        <x-form.input 
                            placeholder="Street Address" 
                            model="address"
                            wire:model="address"
                            @class(['border-red-500' => $errors->has('address')])
                            required
                        />
                        <x-form.input-error field="address" />
                    </div>  
                    
                    <div x-show="currentStep === 5">                      
                        <h2 class="text-2xl font-bold mb-6">
                             Create <span>{{$isStaffRegistration? "default": 'your'}}</span> login
                        </h2>
                        <x-form.input
                            type="email"
                            placeholder="Email" 
                            model="email"
                            wire:model="email"
                            class="mb-4"
                            @class(['border-red-500'=>$errors->has('email'), 'mb-4'])
                            required
                            />
                           <x-form.input-error field="email"/> 

                        <x-form.input
                        type="password"
                            placeholder="Password" 
                            model="password"
                            wire:model="password"
                            @class(['border-red-500'=>$errors->has('password'), 'mb-4'])
                            required
                            />
                           <x-form.input-error field="password"/>  
                        <x-form.input
                            type="password"
                            placeholder="Repeat Password" 
                            model="password_confirmation"
                            wire:model="password_confirmation"
                            @class(['border-red-500'=>$errors->has('password_confirmation')])
                            required
                            />
                           <x-form.input-error field="password_confirmation"/>  
                    </div>                  

                    <div class="flex justify-between mt-8">                       
                        <button wire:click="previousStep" x-show="currentStep > 1" class="px-6 py-3 bg-gray-700 hover:bg-gray-600 rounded-xl transition duration-300">
                            Back
                        </button>
                        
                        <button  wire:click="nextStep" x-show="currentStep < 5" class="px-6 py-3 bg-blue-600 hover:bg-blue-500 rounded-xl transition duration-300 ml-auto">
                            Next
                        </button>
                        
                        <button wire:click="register" x-show="currentStep === 5" class="px-6 py-3 bg-green-600 hover:bg-green-500 rounded-xl transition duration-300 flex items-center gap-2">
                            <x-graphic name="paper-airplane" class="size-5"/>
                            Submit
                        </button>
                    </div>
                    
                    <div class="flex justify-center mt-8 gap-2">
                        <div class="h-2 w-8 rounded-full bg-blue-500" :class="{'w-2 bg-gray-600': currentStep !== 1, 'w-8 bg-blue-500': currentStep === 1}"></div>
                        <div class="h-2 w-2 rounded-full" :class="{'w-2 bg-gray-600': currentStep !== 2, 'w-8 bg-blue-500': currentStep === 2}"></div>
                        <div class="h-2 w-2 rounded-full" :class="{'w-2 bg-gray-600': currentStep !== 3, 'w-8 bg-blue-500': currentStep === 3}"></div>
                        <div class="h-2 w-2 rounded-full" :class="{'w-2 bg-gray-600': currentStep !== 4, 'w-8 bg-blue-500': currentStep === 4}"></div>
                        <div class="h-2 w-2 rounded-full" :class="{'w-2 bg-gray-600': currentStep !== 5, 'w-8 bg-blue-500': currentStep === 5}"></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

