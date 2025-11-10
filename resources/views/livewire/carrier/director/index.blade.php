<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Contact;
use Livewire\Attribute\Computed;
use Livewire\Attribute\On;
use Livewire\Attribute\Locked;

new class extends Component {
    public $user;
    public $numberOfDirectors;
    public $directors = [];

    #[Locked]
    public $directorId;

    protected $listeners = ['profile-updated'=>'getDirectors',];

    #[Computed]
    public function getDirectors()
    {
        return [
            $this->directors = $this->user->directors()->get(), 
            $this->numberOfDirectors = $this->directors->count()
        ];
    }

    public function deleteDirector($directorId = null)
    {
        $this->directorId = $directorId;
        $director = Contact::findOrFail($this->directorId);
        $director->delete();
        $this->dispatch('profile-updated');
        session()->flash('message', 'Director record successfully deleted!');
    }

    public function editDirector($directorId = null)
    {
        $this->directorId = $directorId;
        $this->dispatch('editing-director', $this->user, $this->directorId);
        \Flux::modals()->close();  
        \Flux::modal('create-director')->show();
    }    

    public function mount(User $user = null)
    {
        $this->user = $user;
        $this->getDirectors();
    }
}; ?>

<div>


    <flux:modal name="show-directors" class="max-w-2xl">

        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">
            <flux:icon name="users" class="size-6 inline-block mr-2 text-lime-600 dark:text-lime-400" />
            Company Directors ({{ $numberOfDirectors }})
        </h3>
        <div class="block w-full p-2">
            <x-form.flash-message-success />
        </div>

        {{-- Determine the optimal grid based on the number of directors --}}
        @php
            $directorCount = $numberOfDirectors;
            $gridClasses = match (true) {
                $directorCount === 1 => 'grid-cols-1',
                $directorCount === 2 => 'grid-cols-1 md:grid-cols-2',
                $directorCount === 3 => 'grid-cols-1 md:grid-cols-3',
                $directorCount >= 4 => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
                default => 'grid-cols-1',
            };
        @endphp

        @if ($directorCount > 0)
            <div class="grid {{ $gridClasses }} gap-6">
                @foreach ($directors as $director)
                    <div
                        class="shadow-lg hover:shadow-xl transition duration-300 border border-lime-200 dark:border-slate-700 p-2">
                        <flux:dropdown position="bottom" align="end" class="float-end">
                            <flux:button icon:trailing="ellipsis-horizontal" size="xs" />

                            <flux:menu>
                                <flux:menu.item icon="pencil-square" class="hover:text-cyan-700 hover:bg-cyan-200" wire:click="editDirector('{{ $director->id }}')">
                                    Edit
                                    Director
                                </flux:menu.item>
                                <flux:menu.separator />
                                <flux:menu.item variant="danger" icon="trash"
                                    wire:click="deleteDirector('{{ $director->id }}')"
                                    wire:confirm.prompt="Are you sure you want to delete the director details?\n\nType DELETE to confirm|DELETE">
                                    Delete Director

                                </flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                        <flux:heading>{{ $director['full_name'] ?? 'Director' }}</flux:heading>

                        <ul class="mx-2 px-2">

                            {{-- Address --}}
                            <li>
                                <flux:icon name="map-pin" class="size-4 text-gray-500 dark:text-gray-400 mr-2" />
                                <div class="">
                                    <span class="font-medium">Address:</span> {{ $director['address'] ?? 'N/A' }}
                                </div>
                            </li>

                            {{-- City --}}
                            <li>
                                <flux:icon name="building-office-2"
                                    class="size-4 text-gray-500 dark:text-gray-400 mr-2" />
                                <div class="">
                                    <span class="font-medium">City:</span> {{ $director['city'] ?? 'N/A' }}
                                </div>
                            </li>

                            {{-- Country --}}
                            <li>
                                <flux:icon name="flag" class="size-4 text-gray-500 dark:text-gray-400 mr-2" />
                                <div class="">
                                    <span class="font-medium">Country:</span> {{ $director['country'] ?? 'N/A' }}
                                </div>
                            </li>

                            {{-- Optional Fields --}}

                            @if (!empty($director['phone_number']))
                                <li>
                                    <flux:icon name="phone" class="size-4 text-lime-600 dark:text-lime-400 mr-2" />
                                    <div class="">
                                        <a href="tel:{{ $director['phone_number'] }}"
                                            class="text-lime-600 hover:underline dark:text-lime-400">
                                            {{ $director['phone_number'] }}
                                        </a>
                                    </div>
                                </li>
                            @endif

                            @if (!empty($director['whatsapp']))
                                <li>
                                    <flux:icon name="chat-bubble-oval-left-ellipsis"
                                        class="size-4 text-green-500 mr-2" />
                                    <div class="">
                                        <a href="https://wa.me/{{ $director['whatsapp'] }}" target="_blank"
                                            class="text-green-600 hover:underline dark:text-green-400">
                                            WhatsApp
                                        </a>
                                    </div>
                                </li>
                            @endif

                            @if (!empty($director['email']))
                                <li>
                                    <flux:icon name="envelope" class="size-4 text-indigo-500 mr-2" />
                                    <div class="">
                                        <a href="mailto:{{ $director['email'] }}"
                                            class="text-indigo-600 hover:underline dark:text-indigo-400">
                                            {{ $director['email'] }}
                                        </a>
                                    </div>
                                </li>
                            @endif

                        </ul>

                    </div>
                @endforeach
            </div>
        @else
            {{-- Fallback if no directors are available --}}
            <flux:callout icon="information-circle">
                <flux:callout.heading>No Directors Found</flux:callout.heading>
                <flux:callout.text>
                    The company's director information is currently unavailable or has not been provided.
                </flux:callout.text>
            </flux:callout>
        @endif

    </flux:modal>
</div>
