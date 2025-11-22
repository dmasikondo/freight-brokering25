<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        Password::sendResetLink($this->only('email'));

        session()->flash('status', __('Check a reset link sent to the inbox / spam of the given  email, if it exists in our database.'));
    }
}; ?>
<div class="">
    <x-layouts.app :title="__('Forgot Password')">
        <div class="min-h-screen flex flex-col items-center">
        <div class="flex flex-col gap-6 max-w-md ">
                    <x-card.feature-card :feature="[
                        'color' => 'amber',
                        'title' => 'Forgot Password',
                        'icon' => 'shield-exclamation',
                        'description' => 'Enter your email to receive a password reset link.',
                    ]"> 

            <!-- Session Status -->
            <x-auth-session-status class="text-center" :status="session('status')" />

            <form wire:submit="sendPasswordResetLink" class="flex flex-col gap-6">
                <!-- Email Address -->
                <flux:input wire:model="email" :label="__('Email Address')" type="email" required autofocus
                    placeholder="email@example.com" />

                <flux:button variant="primary" type="submit" class="w-full">{{ __('Email password reset link') }}
                </flux:button>
            </form>

            <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-400">
                <span>{{ __('Or, return to') }}</span>
                <flux:link :href="route('login')" wire:navigate>{{ __('log in') }}</flux:link>
            </div>
                    </x-card.feature-card>
        </div>            
        </div>

    </x-layouts.app>
</div>
