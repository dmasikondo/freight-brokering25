<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Contact;
use Livewire\Attribute\Computed;
use Livewire\Attribute\On;
use Livewire\Attribute\Locked;

new class extends Component {
    public $user;
    public $numberOfTraderefs;
    public $traderefs = [];

    #[Locked]
    public $traderefId;

    protected $listeners = ['traderef-updated'=>'getTraderefs',];

    #[Computed]
    public function getTraderefs()
    {
        return [
            $this->traderefs = $this->user->traderefs()->get(), 
            $this->numberOfTraderefs = $this->traderefs->count()
        ];
    }

    public function deleteTraderef($traderefId = null)
    {
        $this->traderefId = $traderefId;
        $traderef = Contact::findOrFail($this->traderefId);
        $traderef->delete();
        $this->dispatch('traderef-updated');
        $this->dispatch('show-tradeFlashMessage');
        $this->dispatch('editing-traderef', $this->user);
       // $this->redirectRoute('dashboard');
    }

    public function editTraderef($traderefId = null)
    {
        $this->traderefId = $traderefId;
        $this->dispatch('editing-traderef', $this->user, $this->traderefId);
        \Flux::modals()->close();  
        \Flux::modal('create-traderef')->show();
    }    

    public function mount(User $user = null)
    {
        $this->user = $user;
        $this->getTraderefs();
    }
}; ?>

<div>


    <flux:modal name="show-traderefs" class="max-w-3xl">

        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">
            <flux:icon name="users" class="size-6 inline-block mr-2 text-lime-600 dark:text-lime-400" />
            {{ $user->organisation }}: Traderefs ({{ $numberOfTraderefs }})
        </h3>
        <div class="block w-full p-2">
            <x-form.flash-message-success />
        </div>

        {{-- Determine the optimal grid based on the number of traderefs --}}
        @php
            $traderefCount = $numberOfTraderefs;
            $gridClasses = match (true) {
                $traderefCount === 1 => 'grid-cols-1',
                $traderefCount === 2 => 'grid-cols-1 md:grid-cols-2',
                $traderefCount === 3 => 'grid-cols-1 md:grid-cols-3',
                $traderefCount >= 4 => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
                default => 'grid-cols-1',
            };
        @endphp

        @if ($traderefCount > 0)
            <div class="grid {{ $gridClasses }} gap-6">
                @foreach ($traderefs as $traderef)
                    <div
                        class="shadow-lg hover:shadow-xl transition duration-300 border border-lime-200 dark:border-slate-700 p-2">
                        <flux:dropdown position="bottom" align="end" class="float-end">
                            <flux:button icon:trailing="ellipsis-horizontal" size="xs" />

                            <flux:menu>
                                <flux:menu.item icon="pencil-square" class="hover:text-cyan-700 hover:bg-cyan-200" wire:click="editTraderef('{{ $traderef->id }}')">
                                    Edit
                                    Traderef
                                </flux:menu.item>
                                <flux:menu.separator />
                                <flux:menu.item variant="danger" icon="trash"
                                    wire:click="deleteTraderef('{{ $traderef->id }}')"
                                    wire:confirm.prompt="Are you sure you want to delete the traderef details?\n\nType DELETE to confirm|DELETE">
                                    Delete Traderef

                                </flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                        <flux:heading>{{ $traderef['full_name'] ?? 'Traderef' }}</flux:heading>

                        <ul class="mx-2 px-2">

                            {{-- Address --}}
                            <li>
                                <flux:icon name="map-pin" class="size-4 text-gray-500 dark:text-gray-400 mr-2" />
                                <div class="">
                                    <span class="font-medium">Address:</span> {{ $traderef['address'] ?? 'N/A' }}
                                </div>
                            </li>

                            {{-- City --}}
                            <li>
                                <flux:icon name="building-office-2"
                                    class="size-4 text-gray-500 dark:text-gray-400 mr-2" />
                                <div class="">
                                    <span class="font-medium">City:</span> {{ $traderef['city'] ?? 'N/A' }}
                                </div>
                            </li>

                            {{-- Country --}}
                            <li>
                                <flux:icon name="flag" class="size-4 text-gray-500 dark:text-gray-400 mr-2" />
                                <div class="">
                                    <span class="font-medium">Country:</span> {{ $traderef['country'] ?? 'N/A' }}
                                </div>
                            </li>

                            {{-- Optional Fields --}}

                            @if (!empty($traderef['phone_number']))
                                <li>
                                    <flux:icon name="phone" class="size-4 text-lime-600 dark:text-lime-400 mr-2" />
                                    <div class="">
                                        <a href="tel:{{ $traderef['phone_number'] }}"
                                            class="text-lime-600 hover:underline dark:text-lime-400">
                                            {{ $traderef['phone_number'] }}
                                        </a>
                                    </div>
                                </li>
                            @endif

                            @if (!empty($traderef['whatsapp']))
                                <li>
                                    <flux:icon name="chat-bubble-oval-left-ellipsis"
                                        class="size-4 text-green-500 mr-2" />
                                    <div class="">
                                        <a href="https://wa.me/{{ $traderef['whatsapp'] }}" target="_blank"
                                            class="text-green-600 hover:underline dark:text-green-400">
                                            WhatsApp
                                        </a>
                                    </div>
                                </li>
                            @endif

                            @if (!empty($traderef['email']))
                                <li>
                                    <flux:icon name="envelope" class="size-4 text-indigo-500 mr-2" />
                                    <div class="">
                                        <a href="mailto:{{ $traderef['email'] }}"
                                            class="text-indigo-600 hover:underline dark:text-indigo-400">
                                            {{ $traderef['email'] }}
                                        </a>
                                    </div>
                                </li>
                            @endif

                        </ul>

                    </div>
                @endforeach
            </div>
        @else
            {{-- Fallback if no traderefs are available --}}
            <flux:callout icon="information-circle">
                <flux:callout.heading>No Trade References Found</flux:callout.heading>
                <flux:callout.text>
                    The company's trade reference information is currently unavailable or has not been provided.
                </flux:callout.text>
            </flux:callout>
        @endif

    </flux:modal>
</div>
