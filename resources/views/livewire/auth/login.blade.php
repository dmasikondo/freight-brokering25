<?php

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->ensureIsNotRateLimited();

        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email) . '|' . request()->ip());
    }
}; ?>
<div class="">
    <x-layouts.app :title="__('Transpartner Logistics::login')">
        {{-- <div id="login" class="min-h-screen bg-gradient-to-b from-[#0f172a] to-[#1e293b] text-white p-4 flex flex-col items-center pb-8"> --}}
        <div id="login" class="min-h-screen  flex flexflex-col items-center pb-8">
            <div class="w-full lg:max-w-md mt-4">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-medium" wire:navigate>
                    <span class="flex md:max-w-lg  mb-1 items-center justify-center rounded-md">
                        <x-app-logo-icon class="size-16 fill-current text-black dark:text-white" />
                    </span>
                    <span class="sr-only">{{ config('app.name', 'Transpartner Logistics') }}</span>
                </a>
                <h1
                    class="text-5xl font-bold text-center bg-gradient-to-r from-blue-400 to-emerald-500 text-transparent bg-clip-text mb-4">
                    Welcome Back
                </h1>
                <p class="text-center text-gray-400 text-xl mb-12">
                    Sign in to Transpartner Logistics
                </p>
                {{-- <div class="bg-gray-800 p-8 rounded-3xl shadow-xl"> --}}
                <div class=" p-8 rounded-3xl shadow-xl">
                    <x-card.feature-card :feature="[
                        'color' => 'emerald',
                        'title' => 'Login',
                        'icon' => 'key',
                        'iconContainerClass' => 'w-8 h-8',
                        'description' => 'Please enter your credentials to continue.',
                    ]">
                        <!-- Login Form -->
                        <form method="post" wire:submit="login">
                            <div class="space-y-6 mt-4">
                                <flux:input :invalid label="Email Address" placeholder="your@email.com"
                                    wire:model="email" class="w-full" />


                                <flux:input :invalid type='password' label="Password" placeholder="••••••••"
                                    wire:model="password" viewable class="w-full" />





                                <!-- Remember Me & Submit -->
                                <div class="">
                                    <div class="flex items-center">
                                        <input type="checkbox" id="remember" wire:model="remember"
                                            class="h-4 w-4 text-blue-500 focus:ring-blue-400 border-gray-600 rounded bg-gray-700" />
                                        <label for="remember" class="ml-2 block text-sm text-gray-300">Remember
                                            me</label>
                                    </div>
                                    <div class="my-2 text-right">
                                        <flux:button type="submit" variant="primary">Sign In</flux:button>
                                    </div>
                                    
                                </div>
                                <div class="my-6 text-right">
                                    <a href="#" class="text-sm text-blue-400 hover:text-emerald-300">Forgot
                                        password?</a>
                                </div>                                
                            </div>
                        </form>

                        <!-- Divider -->
                        <div class="flex items-center my-6">
                            <div class="flex-grow border-t border-gray-600"></div>
                            <span class="flex-shrink mx-4 text-gray-400 text-sm">OR</span>
                            <div class="flex-grow border-t border-gray-600"></div>
                        </div>


                        <!-- Sign Up Link -->
                        <div class="mt-8 text-center text-sm text-gray-400">
                            Don't have an account?
                            <a href="/register" class="text-blue-400 hover:text-blue-300 font-medium">Sign up</a>
                        </div>
                    </x-card.feature-card>
                </div>
            </div>

        </div>
    </x-layouts.app>
</div>
