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
        <div
            class="min-h-screen bg-gradient-to-br from-gray-50 to-lime-50 dark:from-slate-900 dark:to-slate-800 flex items-center justify-center">
            <div
                class="w-full lg:max-w-md text-center p-2 md:p-10 bg-white/80 backdrop-blur-sm border border-lime-200 rounded-3xl shadow-2xl transition-all duration-500 dark:bg-slate-800/80 dark:border-lime-500/30">
                <div class=" mt-4">
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
                    <p class="text-gray-500 dark:text-gray-400 mt-2">Sign in to your dashboard</p>
                </div>

                <div class="space-y-6">
                    <form wire:submit="login" class="text-left">

                        <div class="relative mt-5 group">
                            <input id="email" type="email" name="email" required autocomplete="email"
                                class="w-full px-4 py-3 pt-5 border  rounded-xl focus:ring-2 focus:ring-lime-500 focus:border-lime-500 transition duration-150 ease-in-out dark:bg-slate-700 dark:border-slate-600 dark:text-white peer   @error('email') border-red-600 @enderror"
                                placeholder=" " wire:model="email">
                            <label for="email"
                                class="absolute top-1/2 -translate-y-1/2 left-4 text-gray-500 dark:text-gray-400 pointer-events-none origin-left 
                                  transition-all duration-200 transform 
                                  peer-focus:top-2 peer-focus:text-xs peer-focus:text-lime-600 dark:peer-focus:text-lime-400 
                                  peer-focus:scale-75 
                                  peer-not-placeholder-shown:top-2 peer-not-placeholder-shown:text-xs peer-not-placeholder-shown:text-lime-600 dark:peer-not-placeholder-shown:text-lime-400
                                  peer-not-placeholder-shown:scale-75">
                                Email Address
                            </label>
                            @error('email')
                                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="relative mt-8 group">
                            <input id="password" type="password" name="password" required
                                autocomplete="current-password"
                                class="w-full px-4 py-3 pt-5 border border-lime-300 rounded-xl focus:ring-2 focus:ring-lime-500 focus:border-lime-500 transition duration-150 ease-in-out dark:bg-slate-700 dark:border-slate-600 dark:text-white peer @error('email')  @enderror"
                                placeholder=" " wire:model="password">
                            <label for="password"
                                class="absolute top-1/2 -translate-y-1/2 left-4 text-gray-500 dark:text-gray-400 pointer-events-none origin-left 
                                  transition-all duration-200 transform 
                                  peer-focus:top-2 peer-focus:text-xs peer-focus:text-lime-600 dark:peer-focus:text-lime-400 
                                  peer-focus:scale-75 
                                  peer-not-placeholder-shown:top-2 peer-not-placeholder-shown:text-xs peer-not-placeholder-shown:text-lime-600 dark:peer-not-placeholder-shown:text-lime-400
                                  peer-not-placeholder-shown:scale-75">
                                Password
                            </label>
                            @error('password')
                                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                            @enderror
                        </div>


                        <div class="flex items-center justify-between mt-4">
                            <div class="flex items-center">
                                <input id="remember_me" type="checkbox"
                                    class="rounded border-gray-300 text-lime-600 shadow-sm focus:ring-lime-500 dark:bg-slate-700 dark:border-slate-600"
                                    name="remember" wire:model="remember">
                                <label for="remember_me"
                                    class="ml-2 block text-sm text-gray-900 dark:text-gray-300">Remember me</label>
                            </div>

                            <a href="{{ route('password.request') }}" wire:navigation
                                class="text-sm font-semibold text-lime-600 hover:text-lime-700 transition-colors duration-200 dark:text-lime-400 dark:hover:text-lime-300">
                                Forgot Password?
                            </a>
                        </div>

                        <flux:button icon="lock-closed" variant="primary" type="submit" color="green"
                            class='my-2 w-full'>Log In</flux:button>
                    </form>
                </div>

                <div class="border-t border-gray-200 pt-6 mt-8 dark:border-gray-700">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Don't have an account?
                        <a href="{{ route('register') }}"
                            class="font-bold text-lime-600 hover:text-lime-700 transition-colors duration-200 dark:text-lime-400 dark:hover:text-lime-300 ml-1">
                            Register Now
                        </a>
                    </p>
                </div>
            </div>

            <div
                class="fixed top-10 left-10 w-20 h-20 bg-lime-200 rounded-full blur-xl opacity-30 animate-pulse dark:bg-lime-900">
            </div>
            <div
                class="fixed bottom-10 right-10 w-32 h-32 bg-lime-300 rounded-full blur-xl opacity-20 animate-bounce dark:bg-lime-800">
            </div>

        </div>
    </x-layouts.app>
</div>
