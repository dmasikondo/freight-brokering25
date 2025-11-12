<?php

use Livewire\Volt\Component;
use App\Models\User;

new class extends Component {

    public $user;

    public function mount(User $user = null)
    {
        $this->user = $user;
    }
}; ?>

<div class="space-y-6">    
        <!-- Header Section -->
        <div class="flex justify-between items-center my-2">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Carrier Onboarding</h1>
            </div>
            <div class="flex gap-3">
                <flux:button type="submit" icon="truck"  variant="primary" color="green" href="{{ route('lane.create') }}" wire:navigate >
                    Upload Vehicles
                </flux:button>
            </div>
        </div>
        <livewire:carrier.profile-completion-check :user="$user" />

        <livewire:users.contact-info :user='$user' />

        <!-- Form Completion Status -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <livewire:carrier.director.status-info :user="$user" />

            <livewire:carrier.fleet.status-info :user="$user" />

            <livewire:carrier.traderef.status-info :user="$user" />
        </div>

        <livewire:carrier.document-upload :user="$user" />

        <livewire:carrier.recent-file-uploads :user="$user" />

    
</div>
