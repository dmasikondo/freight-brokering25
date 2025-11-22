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
        <div class="min-h-screen flex flex-col items-center">
            <div class="flex flex-col  max-w-md ">
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
                            Welcome Back
                        </h1>
                    </div>

                    <div class="mb-8">
                        <h1 class="md:text-2xl font-black text-lime-600 md:tracking-wider dark:text-lime-400">
                            TRANSPARTNER<span class="text-gray-700 font-light ml-1 dark:text-gray-300">LOGISTICS</span>
                        </h1>
                    </div>
                </div>

                <x-card.feature-card :feature="[
                    'color' => 'emerald',
                    'title' => 'Sign In',
                    'icon' => 'key',
                    'description' => 'Log in to your dashboard',
                ]">
                    <form wire:submit="login" class="flex flex-col gap-6 my-6">
                        <!-- Email Address -->
                        <flux:input wire:model="email" :label="__('Email address')" type="email" required autofocus
                            autocomplete="email" placeholder="email@domain.com" />

                        <!-- Password -->
                        <div class="relative">
                            <flux:input wire:model="password" :label="__('Password')" type="password" required
                                autocomplete="current-password" :placeholder="__('Password')" viewable />


                        </div>

                        <!-- Remember Me -->
                        <flux:checkbox wire:model="remember" :label="__('Remember me')" />

                        <div class="flex items-center justify-end">
                            <flux:button variant="primary" type="submit" class="w-full">{{ __('Log in') }}
                            </flux:button>
                        </div>
                    </form>


                    <div class=" pt-6 mt-8 ">
                        <flux:separator text="or" />
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Don't have an account?
                            <a href="{{ route('register') }}"
                                class="font-bold text-lime-600 hover:text-lime-700 transition-colors duration-200 dark:text-lime-400 dark:hover:text-lime-300 ml-1">
                                Register Now
                            </a>
                        </p>
                        @if (Route::has('password.request'))
                            <flux:link class=" text-sm" :href="route('password.request')"
                                wire:navigate>
                                {{ __('Forgot your password?') }}
                            </flux:link>
                        @endif
                    </div>

                    <div
                        class="fixed top-10 left-10 w-20 h-20 bg-lime-200 rounded-full blur-xl opacity-30 animate-pulse dark:bg-lime-900">
                    </div>
                    <div
                        class="fixed bottom-10 right-10 w-32 h-32 bg-lime-300 rounded-full blur-xl opacity-20 animate-bounce dark:bg-lime-800">
                    </div>
                </x-card.feature-card>
            </div>

        </div>
    </x-layouts.app>
</div>
