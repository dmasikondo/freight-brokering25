<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Fleet;
use Livewire\Attributes\On;

new class extends Component {
    public $user;
    public $fleet;

    #[Locked]
    public $fleetId;

    protected $listeners = ['fleet-updated'=>'getFleet'];

    #[Computed]
    public function getFleet()
    {
        return [($this->fleet = $this->user->fleets()->with('trailers')->get())];
    }

    public function deleteFleet($fleetId)
    {
        $this->fleetId = $fleetId;
        $fleet = Fleet::findOrFail($this->fleetId);
        $fleet->delete();
        $this->dispatch('fleet-updated');
        $this->dispatch('show-flashFleetMessage');
        $this->dispatch('reload-status');
        session()->flash('message', 'Fleet record successfully deleted!');
       $this->redirectRoute('dashboard');
    }

    public function mount(User $user = null)
    {
        $this->user = $user;
        $this->getFleet();
    }
}; ?>

<div>
    <flux:modal name="show-fleet" class="max-w-2xl">

        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">
            <flux:icon name="truck" class="size-6 inline-block mr-2 text-lime-600 dark:text-lime-400" />
             {{ $user?->organisation }}: Fleet Info
        </h3>
        <div class="block w-full p-2">
            <x-form.flash-message-success />
        </div>
        @if ($fleet->count() > 0)
            <ul class="mx-2 px-2">
              
                @foreach ($fleet as $fleet)
                        <flux:dropdown position="bottom" align="end" class="float-end">
                            <flux:button icon:trailing="ellipsis-horizontal" size="xs" />

                            <flux:menu>
                                <flux:menu.item icon="pencil-square" class="hover:text-cyan-700 hover:bg-cyan-200" wire:click="editFleet('{{ $fleet['id'] }}')">
                                    Edit                                    
                                </flux:menu.item>
                                <flux:menu.separator />
                                <flux:menu.item variant="danger" icon="trash"
                                    wire:click="deleteFleet('{{ $fleet['id'] }}')"
                                    wire:confirm.prompt="Are you sure you want to delete this fleet information?\n\nType DELETE to confirm|DELETE">
                                    Delete Fleet Info

                                </flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>                  
                    <li>
                        <flux:icon name="truck" class="size-12 text-gray-500 dark:text-gray-400 mr-2" />
                        <div class="">
                            <span class="font-medium">Horses:</span> {{ $fleet['horses'] }}
                        </div>

                    </li>
                    <li>
                        <span class="font-medium">Trailers:</span> {{ $fleet['trailer_qty'] }}
                    </li>
                    <flux:separator />
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                        @foreach ($fleet['trailers'] as $trailer)
                            <div class="flex items-center justify-between gap-2">
                                <div class="size-24 w-full rounded-md ">
                                    <x-graphic name="{{ $trailer->name->iconName() }}" class="size-24" />
                                </div>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $trailer->name->label() }}
                                </div>
                            </div>
                        @endforeach
                    </div>

            </ul>
        @endforeach
        @else
            {{-- Fallback if no fleet data is available --}}
            <flux:callout icon="information-circle">
                <flux:callout.heading>No Fleet Data Found</flux:callout.heading>
                <flux:callout.text>
                   The fleet information is currently unavailable or has not been provided.
                </flux:callout.text>
            </flux:callout>        
        @endif
    </flux:modal>
</div>
