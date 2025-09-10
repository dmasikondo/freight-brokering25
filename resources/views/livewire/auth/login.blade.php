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

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
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
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
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
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }
}; ?>

<div id="login" class="min-h-screen bg-gradient-to-b from-[#0f172a] to-[#1e293b] text-white p-4 flex flex-col items-center pb-8">
    <div class="w-full max-w-md mt-12">
        <h1 class="text-5xl font-bold text-center bg-gradient-to-r from-blue-400 to-purple-500 text-transparent bg-clip-text mb-4">
            Welcome Back
        </h1>
        <p class="text-center text-gray-400 text-xl mb-12">
            Sign in to your logistics account
        </p>

        <div class="bg-gray-800 p-8 rounded-3xl shadow-xl">
            <!-- Login Form -->
            <form method="post" wire:submit="login">
                <div class="space-y-6">
                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block text-gray-300 text-sm font-medium mb-2">Email Address</label>
                        <input
                            type="email"
                            id="email"
                            wire:model="email"
                            placeholder="your@email.com"
                            class="w-full p-4 bg-gray-700 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-white"
                            required
                        />
                    </div>

                    <!-- Password Field -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="password" class="block text-gray-300 text-sm font-medium">Password</label>
                            <a href="#" class="text-sm text-blue-400 hover:text-blue-300">Forgot password?</a>
                        </div>
                        <input
                            type="password"
                            id="password"
                            wire:model="password"
                            placeholder="••••••••"
                            class="w-full p-4 bg-gray-700 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-white"
                            required
                        />
                    </div>

                    <!-- Remember Me & Submit -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input
                                type="checkbox"
                                id="remember"
                                wire:model="remember"
                                class="h-4 w-4 text-blue-500 focus:ring-blue-400 border-gray-600 rounded bg-gray-700"
                            />
                            <label for="remember" class="ml-2 block text-sm text-gray-300">Remember me</label>
                        </div>

                        <button
                            type="submit"
                            class="px-6 py-3 bg-blue-600 hover:bg-blue-500 rounded-xl transition duration-300"
                        >
                            Sign In
                        </button>
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
        </div>
    </div>

</div>
