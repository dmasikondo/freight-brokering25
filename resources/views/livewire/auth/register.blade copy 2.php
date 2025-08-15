<?php

namespace App\Livewire;

use App\Models\Role;
use App\Models\User;
use App\Services\UserRegistrationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
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

    // The service class is injected here
    protected UserRegistrationService $userRegistrationService;

    public function boot(userRegistrationService $userRegistrationService)
    {
        $this->userRegistrationService = $userRegistrationService;
    }

    public function mount(?string $role = null): void
    {
        $this->zimbabweCities = \App\Models\ZimbabweCity::orderBy('name')->pluck('name', 'name')->toArray();
        
        // This is where the service class simplifies the logic
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
    }

    protected function rules(): array
    {
        $baseRules = [
            'first_name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'contact_phone' => ['required', 'string', 'regex:/^(\+\d{1,3}[- ]?)?\d{7,15}$/'],
            'phone_type' => ['required', 'string', 'in:mobile,landline,other', 'max:20'],
            'whatsapp' => ['nullable', 'regex:/^(\+\d{1,3}[- ]?)?\d{7,15}$/'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ];

        // Conditional rules are simplified based on the `isStaffRegistration` flag
        if ($this->isStaffRegistration) {
            $staffRules = [
                'role' => ['required', 'string', 'in:' . implode(',', $this->allowedRoles->toArray())],
                'company_name' => ['required_if:role,shipper,carrier', 'nullable', 'string', 'max:255'],
                'ownership_type' => ['required_if:role,shipper,carrier'],
                'country' => ['required_if:role,shipper,carrier', 'nullable', 'string', 'in:Zimbabwe,South Africa'],
                'address' => ['required_if:role,shipper,carrier', 'nullable', 'string', 'max:255'],
                'city' => ['required_if:role,shipper,carrier', 'nullable', 'string'],
            ];
            return array_merge($baseRules, $staffRules);
        }

        // Self-registration rules
        return array_merge($baseRules, [
            'company_name' => ['required', 'string', 'max:255'],
            'ownership_type' => ['required'],
            'customer_type' => ['required'],
            'country' => ['required', 'string', 'in:Zimbabwe,South Africa'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string'],
        ]);
    }

    public function register(): void
    {
        $validated = $this->validate();

        // Pass the validated data and the creator to the service class
        $creator = $this->isStaffRegistration ? Auth::user() : null;
        $user = $this->userRegistrationService->registerUser($validated, $creator);

        if (!$user) {
            // Handle error, e.g., show a message to the user
            $this->addError('general', 'Failed to register user. Please check permissions or try again.');
            return;
        }

        if ($this->isStaffRegistration) {
            $this->redirect(route('admin.users.index'), navigate: true);
        } else {
            // The service class already fired the event and created the user,
            // we just need to log them in.
            Auth::login($user);
            $this->redirectIntended(route('dashboard', absolute: false), navigate: true);
        }
    }
    
    // Multi-step form navigation methods
    public function nextStep(): void
    {
       $this->validateStep();
       if ($this->currentStep < 5) {
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
           $this->validateOnly('company_name');
           $this->validateOnly('ownership_type');
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
            <!-- Right Column (Info) -->
            <div class="bg-gray-800 p-6 rounded-3xl shadow-xl w-full md:w-1/2 order-2 md:order-1">
                <!-- Steps are now hidden for staff registration -->
                <div x-show="!isStaffRegistration">
                    <x-steps.registration-steps :step="1" icon="user" title="Full Name"
                        description="Enter your legal name as it appears on official documents." usageTitle="Used for:"
                        :items="['Account verification', 'Shipping documentation']" />
                    <x-steps.registration-steps :step="2" icon="phone" title="Contact Info"
                        description="Primary contact number and optional WhatsApp." usageTitle="We'll use this to:"
                        :items="['Confirm shipments', 'Send delivery updates']" />
                    <x-steps.registration-steps :step="3" icon="building-office-2" title="Business Info"
                        description="Tell us about your company and role." usageTitle="Why we need this:"
                        :items="['Verify business credentials', 'Match with relevant partners']" />
                    <x-steps.registration-steps :step="4" icon="location-marker" title="Business Location"
                        description="Tell us about your company location" usageTitle="Our reason for asking:"
                        :items="['Verify business credentials', 'Logistics and Shipping Optimization']" />
                    <x-steps.registration-steps :step="5" icon="lock-closed" title="Login Details"
                        description="Create secure credentials for your account." usageTitle="We will protect your data with:"
                        :items="['Secure password hashing', 'Industry standard encryption']" />
                </div>
                <!-- Login details summary is shown for both -->
                <div x-show="isStaffRegistration || currentStep === 5" class="space-y-3">
                    <div class="flex items-center gap-2">
                        <x-graphic name="lock-closed" class="size-5 text-blue-400" />
                        <h3 class="text-lg font-semibold">Login Details</h3>
                    </div>
                    <!-- Password strength logic... -->
                </div>

                <!-- Compact Preview Section (Updated) -->
                <div class="mt-6 pt-4 border-t border-gray-700">
                    <h4 class="text-sm font-medium mb-2">Your Information</h4>
                    <div class="space-y-2 text-xs">
                        <!-- ... other preview items remain the same ... -->
                    </div>
                </div>
            </div>

            <!-- Left Column (Form) -->
            <div class="bg-gray-800 p-8 rounded-3xl shadow-xl w-full md:w-1/2 order-1 md:order-2">
                <form wire:submit.prevent="register">
                    <div x-show="currentStep===1 || isStaffRegistration">
                        <h2 class="text-2xl font-bold mb-6">Personal Details</h2>
                        <!-- User Role section for staff-assisted registration -->
                        <div x-show="isStaffRegistration" class="mb-6">
                            <h3 class="text-lg font-semibold mb-2">User Role</h3>
                            <div class="flex flex-wrap gap-4">
                                @foreach($allowedRoles as $roleName => $roleLabel)
                                    <div class="flex flex-col mt-2">
                                        <div class="flex items-center">
                                            <input type="radio" x-model="role" value="{{ $roleName }}" id="{{ $roleName }}" class="mr-2" wire:model="role" />
                                            <label for="{{ $roleName }}" class="text-gray-300 flex items-center gap-2">
                                                @if($roleName === 'shipper')
                                                    <x-graphic name="cube" class="size-5 text-orange-400"/>
                                                @elseif($roleName === 'carrier')
                                                    <x-graphic name="exchange" class="size-5 text-teal-400"/>
                                                @else
                                                    <x-graphic name="user" class="size-5 text-blue-400"/>
                                                @endif
                                                {{ ucfirst($roleLabel) }}
                                            </label>
                                        </div>
                                        @if($roleName === 'carrier')
                                            <p x-show="role === 'carrier'" class="text-xs italic text-gray-400 ml-7 mt-1">They provide trucks/trailers to move goods</p>
                                        @elseif($roleName === 'shipper')
                                            <p x-show="role === 'shipper'" class="text-xs italic text-gray-400 ml-7 mt-1">They have goods that need to be transported</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                           
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-form.input
                                @class(['border-red-500' => $errors->has('first_name')])
                                wire:model="first_name"
                                model="first_name"
                                type="text"
                                placeholder="First Name"
                                required />
                            <x-form.input-error field="first_name"/>
                            <x-form.input
                                @class(['border-red-500' => $errors->has('surname')])
                                wire:model="surname"
                                model="surname"
                                type="text"
                                placeholder="Surname"
                                required />
                            <x-form.input-error field="surname"/>
                        </div>
                    </div>
                    
                    <!-- Contact details are in step 2 for self-registration but always present for staff-assisted -->
                    <div x-show="currentStep===2 || isStaffRegistration" class="mt-6">
                        <h2 class="text-2xl font-bold mb-6">Contact Info</h2>
                        <x-form.select
                            @class(['border-red-500' => $errors->has('phone_type'), 'mb-4'])
                            wire:model="phone_type"
                            model="phone_type"
                            placeholder="Type of Phone"
                            required
                            :options="['mobile' => 'Mobile', 'landline' => 'Landline', 'other' => 'Other']"
                        />
                        <x-form.input-error field="phone_type"/>
                        <x-form.input
                            @class(['border-red-500' => $errors->has('contact_phone'), 'mb-4'])
                            wire:model="contact_phone"
                            model="contact_phone"
                            type="text"
                            placeholder="Phone Number (e.g. +263772123456)"
                            required />
                        <x-form.input-error field="contact_phone"/>
                        <x-form.input
                            @class(['border-red-500' => $errors->has('whatsapp'), 'mb-4'])
                            wire:model="whatsapp"
                            model="whatsapp"
                            type="text"
                            placeholder="WhatsApp Number (Optional)"
                        />
                        <x-form.input-error field="whatsapp"/>
                    </div>
                    
                    <!-- Company info for self-registration (step 3) or when role is shipper/carrier (staff-assisted) -->
                    <div x-show="(currentStep === 3 && !isStaffRegistration) )" x-cloak>
                        <h2 class="text-2xl font-bold mb-6">Company Overview</h2>
                        <x-form.input
                            placeholder="Company Name" 
                            model="company_name"
                            wire:model="company_name"
                            @class(['border-red-500'=>$errors->has('company_name')])
                        />
                        <x-form.input-error field="company_name"/>                       
                        
                        <div @class(['border-red-500 border'=>$errors->has('ownership_type'), 'mr-2',"flex flex-col border-b border-gray-600 mb-4 pb-4"])>
                            <h3 class="text-lg font-bold text-gray-400">Ownership Type</h3>
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
                    </div>

                    <!-- Location info for self-registration (step 4) or when role is shipper/carrier (staff-assisted) -->
                    <div x-show="(currentStep===4 && !isStaffRegistration) || (isStaffRegistration && (role === 'shipper' || role === 'carrier'))" x-cloak>
                        <h2 class="text-2xl font-bold mb-6">Business Location</h2>
                        <x-form.select
                            @class(['border-red-500' => $errors->has('country'), 'mb-4'])
                            wire:model..live="country"
                            model="country"
                            placeholder="Country"
                            required
                            :options="['Zimbabwe' => 'Zimbabwe', 'South Africa' => 'South Africa']"
                        />
                        <x-form.input-error field="country"/>
                        <x-form.select
                            @class(['border-red-500' => $errors->has('city')])
                            wire:model.live="city"
                            model="city"
                            placeholder="City"
                            required
                            :options="$zimbabweCities"
                        />
                        <x-form.input-error field="city"/>
                        <x-form.input
                            @class(['border-red-500' => $errors->has('address'), 'mb-4'])
                            wire:model.live="address"
                            model.live="address"
                            type="text"
                            placeholder="Physical Address"
                            required />
                        <x-form.input-error field="address"/>
                    </div>

                    <!-- Step 5 is shown for both flows -->
                    <div x-show="currentStep === 5 || isStaffRegistration">
                        <h2 class="text-2xl font-bold mb-6">Create login details</h2>
                        <x-form.input
                            @class(['border-red-500' => $errors->has('email'), 'mb-4'])
                            wire:model="email"
                            model="email"
                            type="email"
                            placeholder="Email Address"
                            required />
                        <x-form.input-error field="email"/>
                        <x-form.input
                            @class(['border-red-500' => $errors->has('password'), 'mb-4'])
                            wire:model="password"
                            model="password"
                            type="password"
                            placeholder="Password"
                            required />
                        <x-form.input-error field="password"/>
                        <x-form.input
                            @class(['border-red-500' => $errors->has('password_confirmation')])
                            wire:model="password_confirmation"
                            model="password_confirmation"
                            type="password"
                            placeholder="Confirm Password"
                            required />
                        <x-form.input-error field="password_confirmation"/>
                    </div>
                    
                    <div class="flex justify-between mt-8">
                        <button wire:click="previousStep" x-show="currentStep > 1 && !isStaffRegistration"
                            type="button"
                            class="px-6 py-3 bg-gray-700 hover:bg-gray-600 rounded-xl transition duration-300">
                            Back
                        </button>

                        <button wire:click="nextStep" x-show="currentStep < 5 && !isStaffRegistration"
                            type="button"
                            class="px-6 py-3 bg-blue-600 hover:bg-blue-500 rounded-xl transition duration-300 ml-auto">
                            Next
                        </button>

                        <!-- The submit button is now conditional -->
                        <button type="submit"
                            x-show="(currentStep === 5 && !isStaffRegistration) || isStaffRegistration"
                            class="px-6 py-3 bg-green-600 hover:bg-green-500 rounded-xl transition duration-300 flex items-center gap-2">
                            <x-graphic name="paper-airplane" class="size-5" />
                            Submit
                        </button>
                    </div>

                    <div class="flex justify-center mt-8 gap-2" x-show="!isStaffRegistration">
                        <div class="h-2 rounded-full"
                            :class="{ 'w-8 bg-blue-500': currentStep === 1, 'w-2 bg-gray-600': currentStep !== 1 }"></div>
                        <div class="h-2 rounded-full"
                            :class="{ 'w-8 bg-blue-500': currentStep === 2, 'w-2 bg-gray-600': currentStep !== 2 }"></div>
                        <div class="h-2 rounded-full"
                            :class="{ 'w-8 bg-blue-500': currentStep === 3, 'w-2 bg-gray-600': currentStep !== 3 }"></div>
                        <div class="h-2 rounded-full"
                            :class="{ 'w-8 bg-blue-500': currentStep === 4, 'w-2 bg-gray-600': currentStep !== 4 }"></div>
                        <div class="h-2 rounded-full"
                            :class="{ 'w-8 bg-blue-500': currentStep === 5, 'w-2 bg-gray-600': currentStep !== 5 }"></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

