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
    public ?string $slug = null;
    public bool $isEditing = false;

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
            $this->isEditing = true;
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
        }
    }

    protected function rules(): array
    {
        $rules = [
            'first_name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'contact_phone' => ['required', 'string', 'regex:/^(\+\d{1,3}[- ]?)?\d{7,15}$/'],
            'phone_type' => ['required', 'string', 'in:mobile,landline,other', 'max:20'],
            'whatsapp' => ['nullable', 'regex:/^(\+\d{1,3}[- ]?)?\d{7,15}$/'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class, 'email')->ignore($this->slug, 'slug')],
            'customer_type' => ['required', 'string'],
            'ownership_type' => ['required_if:customer_type,shipper,carrier'],
            'company_name' => ['required_if:customer_type,shipper,carrier', 'nullable', 'string', 'max:255'],
            'country' => ['required_if:customer_type,shipper,carrier', 'nullable', 'string', 'in:Zimbabwe,South Africa'],
            'address' => ['required_if:customer_type,shipper,carrier', 'nullable', 'string', 'max:255'],
            'city' => ['required_if:customer_type,shipper,carrier', 'nullable', 'string'],
        ];

        // Only require password for self-registration (not staff-assisted or editing)
        if (!$this->isStaffRegistration && !$this->isEditing) {
            $rules['password'] = ['required', 'string', 'confirmed', Rules\Password::defaults()];
        }

        return $rules;
    }

    public function register(): void
    {
        $validated = $this->validate();
        
        if ($this->slug) {
            // Update existing user
            $userToUpdate = User::where('slug', $this->slug)->firstOrFail();
            $this->userRegistrationService->registerUser($validated, auth()->user(), $userToUpdate);
            $this->redirect(route('users.index'), navigate: true);
        } else {
            // Create new user
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
        if ($this->currentStep < $this->getMaxStep()) {
            if ($this->currentStep === 3) {
                // if ($this->customer_type !== 'shipper' && $this->customer_type !== 'carrier') {
                //     $this->currentStep++;
                // }
            }
            $this->currentStep++;
        }
    }

    public function previousStep(): void
    {
        $this->validateStep();
        if ($this->currentStep > 1) {
            // if ($this->currentStep === $this->getMaxStep()) {
            //     if ($this->customer_type !== 'carrier' && $this->customer_type !== 'shipper') {
            //         $this->currentStep--;
            //     }
            // }
            $this->currentStep--;
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

    private function getMaxStep(): int
    {
        // For staff registration or editing, skip password step
        if ($this->isStaffRegistration || $this->isEditing) {
            return 4;
        }
        return 5;
    }
}; ?>

<div id="contact" x-data="{
    currentStep: @entangle('currentStep'),
    isStaffRegistration: @entangle('isStaffRegistration'),
    isEditing: @entangle('isEditing'),
    role: @entangle('role'),
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
    maxStep: {{ $this->isStaffRegistration || $this->isEditing ? 4 : 5 }}
}" x-cloak
    class="min-h-screen p-4 flex flex-col items-center pb-8">

    <div class="w-full max-w-7xl mt-8">
                <div class="flex flex-col items-center justify-center">
                    <div class="mt-4 space-y-6">
                        <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-medium" wire:navigate>
                            <span class="flex md:max-w-lg  mb-1 items-center justify-center rounded-md">
                                <x-app-logo-icon class="size-16 fill-current text-black dark:text-white" />
                            </span>
                            <span class="sr-only">{{ config('app.name', 'Transpartner Logistics') }}</span>
                        </a>
                        <h1
                            class="md:text-5xl font-bold text-center bg-gradient-to-r from-blue-400 to-emerald-500 text-transparent bg-clip-text mb-4">
                            Ship Smarter!!
                        </h1>
                    </div>
                </div> 
        <p class="text-center text-gray-400 text-xl mb-12">
            <span x-show="!isStaffRegistration && !isEditing">Register as a Shipper or a Carrier to get started.</span>
            <span x-show="isStaffRegistration && !isEditing">Add a new user.</span>
            <span x-show="isEditing">Update user information.</span>
        </p>

        <div class="flex flex-col md:flex-row gap-8 w-full">
            <!-- Right Column (Info) - First on mobile -->
            <div class="p-6 rounded-3xl shadow-xl w-full md:w-1/2 order-2 md:order-1">
                <!-- Step 1: Full Name -->
                <x-steps.registration-steps :step="1" icon="user" title="Full Name"
                    description="Enter the legal name as it appears on official documents." usageTitle="Used for:"
                    :items="['Account verification', 'Shipping documentation']" />
                
                <!-- Step 2: Contact Info -->
                <x-steps.registration-steps :step="2" icon="phone" title="Contact Info"
                    description="Primary contact number and optional WhatsApp." usageTitle="We'll use this to:"
                    :items="['Confirm shipments', 'Send delivery updates']" />

                <!-- Step 3: Business Info -->
                <x-steps.registration-steps :step="3" icon="building-office-2" title="Business Info"
                    description="Tell us about your company and role." usageTitle="Why we need this:"
                    :items="['Verify business credentials', 'Match with relevant partners']" />

                <!-- Step 4: Business Location -->
                <x-steps.registration-steps :step="4" icon="location-marker" title="Business Location"
                    description="Tell us about your company location" usageTitle="Our reason for asking:"
                    :items="['Verify business credentials', 'Logistics and Shipping Optimization']" />

                <!-- Step 5: Login Details - Only for self-registration -->
                <div x-show="currentStep === 5 && !isStaffRegistration && !isEditing" class="space-y-3">
                    <div class="flex items-center gap-2">
                        <x-graphic name="lock-closed" class="size-5 text-blue-400" />
                        <h3 class="text-lg font-semibold">Login Details</h3>
                    </div>
                    <p class="text-sm text-gray-300">Create secure credentials for the account.</p>
                    <div class="bg-gray-700/50 p-3 rounded-lg text-xs space-y-1">
                        <p class="text-gray-400">A secure password must have at least:</p>
                        <div class="flex items-center gap-1.5"
                            x-bind:class="password.length >= 8 ? 'text-green-400' : 'text-gray-300'">
                            <flux:icon.check class="size-3.5" x-bind:class="password.length >= 8 ? 'text-green-400' : 'text-gray-500'" />
                            8+ characters
                        </div>
                        <div class="flex items-center gap-1.5"
                            x-bind:class="/[A-Z]/.test(password) ? 'text-green-400' : 'text-gray-300'">
                            <flux:icon.check class="size-3.5" x-bind:class="/[A-Z]/.test(password) ? 'text-green-400' : 'text-gray-500'" />
                            1 uppercase letter
                        </div>
                        <div class="flex items-center gap-1.5"
                            x-bind:class="/[0-9]/.test(password) ? 'text-green-400' : 'text-gray-300'">
                            <flux:icon.check class="size-3.5" x-bind:class="/[0-9]/.test(password) ? 'text-green-400' : 'text-gray-500'" />
                            1 number
                        </div>
                    </div>
                </div>

                <!-- Compact Preview Section -->
                <div class="mt-6 pt-4 border-t border-gray-700">
                    <h4 class="text-sm font-medium mb-2">Your Information</h4>
                    <div class="space-y-2 text-xs">
                        <div class="flex items-center gap-2" x-show="first_name || surname">
                            <x-graphic name="user" class="size-3.5 text-blue-400" />
                            <span x-text="first_name + ' ' + surname" class="text-gray-400"></span>
                        </div>
                        <div class="flex items-center gap-2" x-show="contact_phone">
                            <x-graphic name="phone" class="size-3.5 text-green-400" />
                            <span x-text="phone_type + ': ' + contact_phone" class="text-gray-400"></span>
                        </div>
                        <div class="flex items-center gap-2" x-show="whatsapp">
                            <x-graphic name="whatsapp" class="size-3.5 text-green-400" />
                            <span x-text="'WhatsApp: ' + whatsapp" class="text-gray-400"></span>
                        </div>
                        <div class="flex items-center gap-2" x-show="company_name">
                            <x-graphic name="building-office-2" class="size-3.5 text-yellow-400" />
                            <span x-text="company_name" class="text-gray-400"></span>
                        </div>
                        <div class="flex items-center gap-2" x-show="ownership_type">
                            <flux:icon.shield-check class="size-3.5" x-bind:class="ownership_type === 'real_owner' ? 'text-yellow-400' : 'text-blue-400'" />
                            <span x-text="ownership_type === 'real_owner' ? 'Real Owner' : 'Broker/Agent'" class="text-gray-400"></span>
                        </div>
                        <div class="flex items-center gap-2" x-show="customer_type">
                            <flux:icon.truck class="size-3.5" x-bind:class="customer_type === 'carrier' ? 'text-teal-400' : 'text-orange-400'" />
                            <span x-text="customer_type === 'carrier' ? 'Carrier' : 'Shipper'" class="text-gray-400"></span>
                        </div>
                        <div class="flex items-center gap-2" x-show="email">
                            <x-graphic name="email-open" class="size-3.5 text-red-400" />
                            <span x-text="email" class="text-gray-400"></span>
                        </div>
                        <div class="flex items-center gap-2" x-show="country">
                            <x-graphic name="location-marker" class="size-3.5 text-green-400" />
                            <span x-text="address + ', '+ city + ', '+ country" class="text-gray-400"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Left Column (Form) - Second on mobile -->
            <div class="p-8 rounded-3xl shadow-xl w-full md:w-1/2 order-1 md:order-2">
                <form wire:submit.prevent>
                    <!-- Step 1: Full Name -->
                    <div x-show="currentStep===1">
                        <h2 class="text-2xl font-bold mb-6">
                            What's <span x-text="isEditing ? 'the user\'s' : (isStaffRegistration ? 'the user\'s' : 'your')"></span> name?
                        </h2>
                        <div class="flex flex-col md:flex-row gap-4">
                            <div class="w-full">
                                <x-form.input placeholder="First Name" model="first_name" wire:model="first_name"
                                    @class(['border-red-500' => $errors->has('first_name')]) />
                                <x-form.input-error field="first_name" />
                            </div>
                            <div class="w-full">
                                <x-form.input placeholder="Surname" model="surname" wire:model='surname'
                                    @class(['border-red-500' => $errors->has('surname')]) />
                                <x-form.input-error field="surname" />
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Contact Info -->
                    <div x-show="currentStep===2">
                        <h2 class="text-2xl font-bold mb-6">
                            What's <span x-text="isEditing ? 'the user\'s' : (isStaffRegistration ? 'the user\'s' : 'your')"></span> contact details?
                        </h2>
                        <x-form.select @class(['border-red-500' => $errors->has('phone_type'), 'mb-4']) 
                            placeholder="Phone Type" model="phone_type"
                            wire:model="phone_type" :options="[
                                'mobile' => 'Mobile',
                                'landline' => 'Landline',
                                'other' => 'Other',
                            ]" />
                        <x-form.input-error field="phone_type" />

                        <x-form.input placeholder="Contact Phone Number" model="contact_phone"
                            wire:model="contact_phone" @class(['border-red-500' => $errors->has('contact_phone'), 'mb-4']) 
                            pattern="^\+?[1-9]\d{6,14}$"
                            required title="Enter a valid phone number (e.g., +2637720000)" />
                        <x-form.input-error field="contact_phone" />

                        <x-form.input placeholder="WhatsApp (optional)" model="whatsapp" wire:model="whatsapp"
                            @class(['border-red-500' => $errors->has('whatsapp')]) pattern="^\+?[1-9]\d{6,14}$"
                            title="Enter a valid whatsApp number (e.g., +2637720000)" />
                        <x-form.input-error field="whatsapp" />
                    </div>

                    <!-- Step 3: Business Info - Self Registration -->
                    <div x-show="(currentStep === 3 && !isStaffRegistration)">
                        <h2 class="text-2xl font-bold mb-6">Company Overview</h2>
                        <x-form.input placeholder="Company Name" model="company_name" wire:model="company_name"
                            @class(['border-red-500' => $errors->has('company_name'), 'mb-4']) />
                        <x-form.input-error field="company_name" />
                        
                        <div @class([
                            'border-red-500 border' => $errors->has('customer_type'),
                            'mr-2',
                            'flex flex-col border-b border-gray-600 mb-4 pb-4',
                        ])>
                            <h3 class="text-lg font-bold text-gray-400">Customer Type</h3>
                            <div class="flex flex-col mt-2">
                                <div class="flex items-center">
                                    <input type="radio" x-model="customer_type" value="carrier" id="carrier"
                                        class="mr-2" wire:model="customer_type" />
                                    <label for="carrier" class="text-gray-300 flex items-center gap-2">
                                        <x-graphic name="exchange" class="size-5 text-teal-400" />
                                        Carrier
                                    </label>
                                </div>
                                <p x-show="customer_type === 'carrier'"
                                    class="text-xs italic text-gray-400 ml-7 mt-1">You provide trucks/trailers to move goods</p>
                            </div>
                            <div class="flex flex-col mt-2">
                                <div class="flex items-center">
                                    <input type="radio" x-model="customer_type" value="shipper" id="shipper"
                                        class="mr-2" wire:model="customer_type" />
                                    <label for="shipper" class="text-gray-300 flex items-center gap-2">
                                        <x-graphic name="cube" class="size-5 text-orange-400" />
                                        Shipper
                                    </label>
                                </div>
                                <p x-show="customer_type === 'shipper'"
                                    class="text-xs italic text-gray-400 ml-7 mt-1">You have goods that need to be transported</p>
                            </div>
                        </div>
                        <x-form.input-error field="customer_type" />
                        
                        <div @class([
                            'border-red-500 border' => $errors->has('ownership_type'),
                            'mr-2',
                            'flex flex-col border-b border-gray-600 mb-4 pb-4',
                        ])>
                            <h3 class="text-lg font-bold text-gray-400">
                                Select User Ownership Type
                            </h3>
                            <div class="flex items-center mt-2">
                                <input type="radio" x-model="ownership_type" value="real_owner" id="real_owner"
                                    class="mr-2" wire:model="ownership_type" />
                                <label for="real_owner" class="text-gray-300 flex items-center gap-2">
                                    <x-graphic name="shield-check" class="size-5 text-yellow-400" />
                                    Real Owner
                                </label>
                            </div>
                            <div class="flex items-center mt-2">
                                <input type="radio" x-model="ownership_type" value="broker_agent"
                                    id="broker_agent" class="mr-2" wire:model="ownership_type" />
                                <label for="broker_agent" class="text-gray-300 flex items-center gap-2">
                                    <x-graphic name="exchange" class="size-5 text-blue-400" />
                                    Broker / Agent
                                </label>
                            </div>
                        </div>
                        <x-form.input-error field="ownership_type" />
                    </div>

                    <!-- Step 3: Business Info - Staff Registration -->
                    <div x-show="(currentStep === 3 && isStaffRegistration)">
                        <h2 class="text-2xl font-bold mb-6">Assign the User Role</h2>
                        <div @class([
                            'border-red-500 border' => $errors->has('customer_type'),
                            'mr-2',
                            'flex flex-col border-b border-gray-600 mb-4 pb-4',
                        ])>
                            <div class="">
                                @foreach ($allowedRoles as $title)
                                    <div class="mb-4">
                                        <x-form.radio-button id="role-{{ $title }}" label="{{ $title }}"
                                            icon="shield-check" value="{{ $title }}" model="customer_type"
                                            @class(['border-red-500' => $errors->has('customer_type')]) />
                                        <p x-show="customer_type === 'carrier' && '{{ $title }}' === 'carrier'"
                                            class="text-xs italic text-gray-400 ml-7 mt-1">
                                            provides trucks/trailers to move goods
                                        </p>
                                        <p x-show="customer_type === 'shipper' && '{{ $title }}' === 'shipper'"
                                            class="text-xs italic text-gray-400 ml-7 mt-1">
                                            has goods that need to be transported
                                        </p>
                                        
                                        <!-- Ownership Type and Company Name Section -->
                                        <div x-show="customer_type === 'carrier' && '{{ $title }}' === 'carrier'"
                                            class="border border-gray-400 p-2 flex flex-wrap gap-2 mt-4 space-y-2">
                                            <div class="w-full">
                                                @include('partials.forms.ownership_type')
                                            </div>
                                            <div class="w-full">
                                                @include('partials.forms.company_name')
                                            </div>
                                        </div>
                                        <div x-show="customer_type === 'shipper' && '{{ $title }}' === 'shipper'"
                                            class="border border-gray-400 p-2 flex flex-wrap gap-2 mt-4 space-y-2">
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
                        <x-form.input-error field="customer_type" />
                    </div>

                    <!-- Step 4: Location -->
                    <div x-show="currentStep===4 && (customer_type ==='carrier' || customer_type ==='shipper')"  x-cloak>
                        <h2 class="text-2xl font-bold mb-6">Where Located?</h2>

                        <x-form.select @class(['border-red-500' => $errors->has('country'), 'mb-4']) 
                            placeholder="Country" wire:model.live="country"
                            :options="[$country=>$country]+['Zimbabwe' => 'Zimbabwe', 'South Africa' => 'South Africa']"
                             />
                        <x-form.input-error field="country" />

                        <div x-show="country==='Zimbabwe'">
                            <x-form.select @class(['border-red-500' => $errors->has('city'), 'mb-4']) 
                                placeholder="City" wire:model="city" :options="[$city=>$city] + $zimbabweCities"/>
                            <x-form.input-error field="city" />
                        </div>

                        <div x-show="country==='South Africa'">
                            <x-form.input placeholder="City" model="city" wire:model="city"
                                @class(['border-red-500' => $errors->has('city'), 'mb-4']) />
                            <x-form.input-error field="city" />
                        </div>

                        <x-form.input placeholder="Street Address" model="address" wire:model="address"
                            @class(['border-red-500' => $errors->has('address')]) required />
                        <x-form.input-error field="address" />
                    </div>

                    <!-- Step 4: Email for staff registration/editing (no location needed) -->
                    <div x-show="currentStep === 4 && (isStaffRegistration || isEditing)">
                        
                        <h2 class="text-2xl font-bold mb-6">
                            <span x-show="isEditing">Update email</span>
                            <span x-show="!isEditing">Set email</span>
                        </h2>
                        
                        <x-form.input type="email" placeholder="Email" model="email" wire:model="email"
                            @class(['border-red-500' => $errors->has('email'), 'mb-4']) required />
                        <x-form.input-error field="email" />
                        
                        <p class="text-sm text-gray-400 mt-2" x-show="!isEditing">
                            <flux:icon.information-circle class="size-4 inline" /> A system-generated password will be created for this email.
                        </p>
                    </div>

                    <!-- Step 5: Password - Only for self-registration -->
                    <div x-show="currentStep === 5 && !isStaffRegistration && !isEditing">
                        <h2 class="text-2xl font-bold mb-6">Create your login</h2>
                        
                        <x-form.input type="email" placeholder="Email" model="email" wire:model="email"
                            @class(['border-red-500' => $errors->has('email'), 'mb-4']) required />
                        <x-form.input-error field="email" />

                        <x-form.input type="password" placeholder="Password" model="password" wire:model="password"
                            @class(['border-red-500' => $errors->has('password'), 'mb-4']) required />
                        <x-form.input-error field="password" />
                        
                        <x-form.input type="password" placeholder="Repeat Password" model="password_confirmation"
                            wire:model="password_confirmation" @class(['border-red-500' => $errors->has('password_confirmation')]) required />
                        <x-form.input-error field="password_confirmation" />
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="flex justify-between mt-8">
                        <flux:button wire:click="previousStep" x-show="currentStep > 1" variant="primary" icon="backward" color="sky">Back</flux:button>

                        <flux:button wire:click="nextStep" x-show="currentStep < maxStep" variant="primary" icon="forward" color="teal">Next</flux:button>

                        <flux:button  wire:click="register" x-show="currentStep === maxStep" variant="primary" icon="paper-airplane" color="lime">
                            <span x-show="isEditing">Update</span>
                            <span x-show="!isEditing">Submit</span>
                        </flux:button>
                    </div>

                    <!-- Progress Indicators -->
                    <div class="flex justify-center mt-8 gap-2">
                        <template x-for="step in maxStep" :key="step">
                            <div class="h-2 rounded-full transition-all duration-300"
                                x-bind:class="currentStep === step ? 'w-8 bg-blue-500' : 'w-2 bg-gray-600'">
                            </div>
                        </template>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>