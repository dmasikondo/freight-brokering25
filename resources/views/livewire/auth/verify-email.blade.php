<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    /**
     * Send an email verification notification to the user.
     */
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);

            return;
        }

        Auth::user()->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div class="mt-4 flex flex-col gap-6">
    <x-layouts.app.horizontal-nav :title="__('verify Email')">
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
                           Secure Account
                        </h1>
                    </div>

                    <div class="mb-8">
                        <h1 class="md:text-2xl font-black text-lime-600 md:tracking-wider dark:text-lime-400">
                            TRANSPARTNER<span class="text-gray-700 font-light ml-1 dark:text-gray-300">LOGISTICS</span>
                        </h1>
                    </div>
                </div>
                <flux:text class="text-center my-6">
                    {{ __('Please click the email verification link sent to') }}
                    <b class="font-mono">{{ Auth::user()->masked_email }}</b>
                </flux:text>

                @if (session('status') == 'verification-link-sent')
                    <flux:text class="text-center font-medium !dark:text-green-400 !text-green-600">
                        {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                    </flux:text>
                @endif

                <div class="flex flex-col items-center justify-between space-y-3">
                    <flux:button wire:click="sendVerification" variant="primary" class="w-full">
                        {{ __('Resend verification email') }}
                    </flux:button>

                    <flux:link class="text-sm cursor-pointer" wire:click="logout">
                        {{ __('Log out') }}
                    </flux:link>
                </div>
            </div>
        </div>
    </x-layouts.app.horizontal-nav>
</div>
