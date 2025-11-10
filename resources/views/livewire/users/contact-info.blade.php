<?php

use Livewire\Volt\Component;
use App\Models\User;

new class extends Component {
    public $user;
    public $buslocation = [];

    protected $listeners = ['location-updated' => 'mount', 'show-locationFlashMessage'=>'flashMessage'];

    public function getBusinessContacts()
    {
        $this->buslocation = $this->user->buslocation()->first();
    }

    public function flashMessage()
    {
        session()->flash('message', 'Business Location successfully updated!');
    }

    public function mount(User $user = null)
    {
        $this->user = $user;
        $this->getBusinessContacts();
    }
}; ?>

<div class="mt-8 pt-6 border-t border-gray-200 dark:border-slate-700">

    <div class="p-6 rounded-xl shadow-xl bg-white dark:bg-slate-800 border-t-4 border-lime-500">

        <div class="flex items-center space-x-4 mb-6 border-b pb-4 dark:border-slate-700">
            <flux:icon name="building-office" class="w-10 h-10 text-lime-600 dark:text-lime-400 flex-shrink-0" />
            <div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ $user->organisation }}
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 flex items-center mt-1">
                    <flux:icon name="user-circle" class="size-4 mr-1" />
                    Contact Person: <span
                        class="font-medium text-gray-700 dark:text-gray-200 ml-1">{{ $user->contact_person }}</span>
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-4">

            {{-- CONTACT DETAILS --}}
            <div class="lg:col-span-1 space-y-3">
                <h4
                    class="text-lg font-semibold text-lime-600 dark:text-lime-400 border-b border-lime-100 dark:border-lime-900/50 pb-1 mb-2">
                    Contact</h4>

                {{-- Phone Number --}}
                <div class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                    <flux:icon name="phone" class="size-4 mr-3 text-lime-500 flex-shrink-0" />
                    <a href="tel:{{ $user->contact_phone }}">{{ $user->contact_phone }}</a>
                </div>

                {{-- WhatsApp --}}
                <div class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                    <flux:icon name="chat-bubble-oval-left-ellipsis" class="size-4 mr-3 text-green-500 flex-shrink-0" />
                    <a href="https://wa.me/{{ $user->whatsapp }}" title="Whatsapp">{{ $user->whatsapp }}</a>
                </div>

                {{-- Email --}}
                <div class="flex items-center text-sm text-gray-700 dark:text-gray-300 truncate">
                    <flux:icon name="envelope" class="size-4 mr-3 text-blue-500 flex-shrink-0" />
                    <a href="mailto:{{ $user->email }}" class="hover:underline truncate">{{ $user->email }}</a>
                </div>
            </div>

            {{-- ADDRESS DETAILS --}}

            <div class="lg:col-span-2 space-y-3">
                <div class="block w-full p-2">
                    <x-form.flash-message-success />
                </div>
                <h4
                    class="text-lg font-semibold text-lime-600 dark:text-lime-400 border-b border-lime-100 dark:border-lime-900/50 pb-1 mb-2">
                    Location</h4>
                @if (!empty($buslocation))
                    {{-- Address Line --}}
                    <div class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                        <flux:icon name="map-pin" class="size-4 mr-3 text-gray-500 flex-shrink-0" />
                        <span>{{ $buslocation['address'] }}</span>
                    </div>

                    {{-- City --}}
                    <div class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                        <flux:icon name="building-office-2" class="size-4 mr-3 text-gray-500 flex-shrink-0" />
                        <span>{{ $buslocation['city'] }}</span>
                    </div>

                    {{-- Country --}}
                    <div class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                        <flux:icon name="flag" class="size-4 mr-3 text-gray-500 flex-shrink-0" />
                        <span>{{ $buslocation['country'] }}</span>
                    </div>
                @else
                    {{-- Fallback if no business location data is available --}}
                    <flux:callout icon="information-circle">
                        <flux:callout.heading>No Business Location Data Found</flux:callout.heading>
                        <flux:callout.text>
                            The business location information is currently unavailable or has not been provided.
                        </flux:callout.text>
                    </flux:callout>

                    <flux:modal.trigger name="create-location">
                        <flux:button class="w-full mt-4" variant="primary">
                            <flux:icon name="home-modern" class="w-4 h-4 mr-2" />
                            Add Location
                        </flux:button>
                    </flux:modal.trigger>

                    <livewire:users.add-contact-info :user="$user" />
                @endif
            </div>



        </div>

    </div>
</div>
