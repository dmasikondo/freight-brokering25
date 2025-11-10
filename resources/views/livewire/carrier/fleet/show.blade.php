<?php

use Livewire\Volt\Component;
use App\Models\User;
use Livewire\Attributes\On;

new class extends Component {
    public $user;
    public $fleet;

    #[Computed]
    public function getFleet()
    {
        return [($this->fleet = $this->user->fleets()->with('trailers')->get())];
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
            Showing Fleet
        </h3>
        <div class="block w-full p-2">
            <x-form.flash-message-success />
        </div>
        @if ($fleet->count() > 0)
            <ul class="mx-2 px-2">
                @foreach ($fleet as $fleet)
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
        @endif
    </flux:modal>
</div>
