<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Illuminate\Support\Str;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $company = '';
    public string $first_name = '';
    public string $last_name = '';
    public string $contact_phone = '';
    public string $phone_type = '';
    public string $whatsapp = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'company' => ['nullable', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:30'],
            'phone_type' => ['required', 'string','in:mobile,landline,other', 'max:20'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        if (empty($this->company)) {
            $validated['company'] = $validated['first_name'] . ' ' . $validated['last_name'];
        }
        
        $validated['contact_person'] = $validated['first_name'] . ' ' . $validated['last_name'];
        $validated['slug'] = Str::slug($validated['first_name'] . ' ' . $validated['last_name']) . '-' . uniqid();
        $validated['password'] = Hash::make($validated['password']);

        event(new Registered(($user = User::create($validated))));
        Auth::login($user);
        $this->redirectIntended(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="flex flex-col max-w-5xl mx-auto bg-white shadow-lg rounded-lg mt-10 p-4">
    <h2 class="text-xl font-bold mb-2">Registration</h2>
    <p class="text-gray-600 mb-4 text-sm">Please fill in your details below.</p>

    <div class="flex flex-col md:flex-row">
        <div class="w-full md:w-1/2 p-2 border-r">
            <form wire:submit.prevent="register" class="flex flex-col gap-2">
                <!-- Company -->
                <div>
                    <label for="company" class="block text-sm font-medium text-gray-700">Company</label>
                    <input type="text" id="company" wire:model="company" class="mt-1 block w-full border border-gray-300 rounded-md p-1 text-sm" placeholder="Company name">
                </div>

                <!-- First Name and Last Name -->
                <div class="flex gap-2">
                    <div class="w-full">
                        <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                        <input type="text" id="first_name" wire:model="first_name" required class="mt-1 block w-full border border-gray-300 rounded-md p-1 text-sm" placeholder="First name">
                    </div>
                    <div class="w-full">
                        <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                        <input type="text" id="last_name" wire:model="last_name" required class="mt-1 block w-full border border-gray-300 rounded-md p-1 text-sm" placeholder="Last name">
                    </div>
                </div>

                <!-- Contact Phone -->
                <div>
                    <label for="contact_phone" class="block text-sm font-medium text-gray-700">Contact Phone</label>
                    <input type="tel" id="contact_phone" wire:model="contact_phone" required class="mt-1 block w-full border border-gray-300 rounded-md p-1 text-sm" placeholder="Contact phone number">
                </div>

                <!-- Phone Type -->
                <div>
                    <label for="phone_type" class="block text-sm font-medium text-gray-700">Phone Type</label>
                    <select id="phone_type" wire:model="phone_type" required class="mt-1 block w-full border border-gray-300 rounded-md p-1 text-sm">
                        <option value="" disabled selected>Select phone type</option>
                        <option value="mobilephone">Mobile</option>
                        <option value="landline">Landline</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <!-- WhatsApp -->
                <div>
                    <label for="whatsapp" class="block text-sm font-medium text-gray-700">WhatsApp</label>
                    <input type="tel" id="whatsapp" wire:model="whatsapp" class="mt-1 block w-full border border-gray-300 rounded-md p-1 text-sm" placeholder="WhatsApp number (optional)">
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                    <input type="email" id="email" wire:model="email" required class="mt-1 block w-full border border-gray-300 rounded-md p-1 text-sm" placeholder="email@example.com">
                </div>
            </form>
        </div>

        <div class="w-full md:w-1/2 p-2">
            <h2 class="text-xl font-bold mb-2">Create Your Login</h2>
            <p class="text-gray-600 mb-4 text-sm">Set your password for your account.</p>

            <form wire:submit.prevent="register" class="flex flex-col gap-2">
                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" id="password" wire:model="password" required class="mt-1 block w-full border border-gray-300 rounded-md p-1 text-sm" placeholder="Password">
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                    <input type="password" id="password_confirmation" wire:model="password_confirmation" required class="mt-1 block w-full border border-gray-300 rounded-md p-1 text-sm" placeholder="Confirm password">
                </div>

                <div class="flex items-center justify-end">
                    <button type="submit" class="w-full bg-blue-600 text-white py-1 rounded-md hover:bg-blue-700 text-sm">Create Account</button>
                </div>
            </form>
        </div>
    </div>

    <div class="text-center text-sm text-gray-600 mt-4">
        <span>Already have an account? </span>
        <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Log in</a>
    </div>
</div>