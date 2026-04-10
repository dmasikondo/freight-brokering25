<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Territory;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Locked;

new class extends Component {
    #[Computed]
    public function getTerritories()
    {
        $territories = Territory::orderBy('name')
            ->with(['countries', 'provinces', 'zimbabweCities', 'users'])
            ->get();

        return $territories;
    }

    public function deleteTerritory($territoryId)
    {
        // Validate that the user and territory exist
        $territory = Territory::find($territoryId);

        // // Check if the territory exists
        if ($territory) {
            $territory->delete();
            session()->flash('message', 'Territory successfully deleted.');
        } else {
            session()->flash('error', 'Territory could not be deleted.');
        }
    }
}; ?>

<div class="p-6 bg-white shadow-md rounded-lg mb-6 relative">
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            {{-- Main Page Title --}}
            <flux:heading size="xl" class="tracking-tight font-black uppercase">
                Territory Jurisdictions
            </flux:heading>

            {{-- Contextual Subheading --}}
            <div class="mt-1 flex items-center gap-2 text-zinc-500">
                <flux:icon.globe-alt variant="mini" class="size-4" />
                <flux:subheading>
                    Managing {{ $this->getTerritories->count() }} active zones across your logistics network
                </flux:subheading>
            </div>
        </div>

        {{-- Action Button (Optional: if you have a create route) --}}
        @if (Route::has('territories.create'))
            <flux:button icon="plus" variant="primary" href="{{ route('territories.create') }}" wire:navigate>
                New Territory
            </flux:button>
        @endif
    </div>

    <hr class="border-zinc-100 dark:border-zinc-800 mb-8" />
    @if (session()->has('message'))
        <div class="my-2">
            <flux:callout icon="check" color='green'>
                <flux:callout.heading>Territory creation</flux:callout.heading>
                <flux:callout.text color='green'>
                    {{ session('message') }}
                </flux:callout.text>
            </flux:callout>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="my-2">
            <flux:callout icon="bolt-slash" color='orange'>
                <flux:callout.heading>Territory creation</flux:callout.heading>
                <flux:callout.text color='orange'>
                    {{ session('error') }}
                </flux:callout.text>
            </flux:callout>
        </div>
    @endif
    @if ($this->getTerritories->isEmpty())
        <flux:callout icon="face-frown">
            <flux:callout.heading>No territories</flux:callout.heading>
            <flux:callout.text>
                No Territories created yet!
            </flux:callout.text>
        </flux:callout>
    @else
        @foreach ($this->getTerritories as $territory)
            <div class="border-b pb-4 mb-4 last:border-0 relative p-8">
                <div class="mr-2 absolute top-0 right-0">
                    <flux:dropdown position="right" align="end">
                        <flux:button icon:trailing="ellipsis-horizontal" />

                        <flux:navmenu>
                            <flux:navmenu.item href="{{ route('territories.edit', ['territory' => $territory->id]) }}"
                                icon="pencil-square">Edit Territory</flux:navmenu.item>
                            <flux:navmenu.item icon="trash" variant="danger"
                                wire:click="deleteTerritory('{{ $territory->id }}')"
                                wire:confirm.prompt="Are you sure you want to delete the territory: {{ strtoupper($territory->name) }}? \n\nType REMOVE to confirm your  action|REMOVE">
                                Remove Territory
                            </flux:navmenu.item>
                        </flux:navmenu>
                    </flux:dropdown>
                </div>
                <div class="flex items-center justify-between">
                    <x-action-message class="me-3 text-green-400" on="user-removed">
                        {{ __('Removed.') }}
                    </x-action-message>
                    <h2 class="text-xl font-bold text-gray-800 hover:text-lime-600 transition-colors">
                        <a href="{{ route('territories.show', $territory->id) }}" wire:navigate>
                            {{ $territory->name }}
                        </a>
                    </h2>
                    <span class="text-sm text-gray-500">
                        Created on {{ $territory->created_at->format('M d, Y') }}
                    </span>
                </div>

                <p class="mt-2 text-gray-700 flex items-center">
                    <flux:icon.users class="inline-block w-5 h-5 text-gray-600 mr-2" />
                    <span class="font-semibold">{{ $territory->users->count() }}</span>
                    &nbsp;{{ Str::plural('user', $territory->users->count()) }} assigned to this territory.
                </p>

                @if ($territory->users->isNotEmpty())
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach ($territory->users->take(5) as $user)
                            <flux:button variant="subtle" size="sm" href="{{ route('users.show', $user->slug) }}"
                                wire:navigate class="hover:bg-lime-50 border-zinc-200">
                                <div class="flex flex-col items-start text-left leading-tight">
                                    <span
                                        class="text-[10px] font-black text-lime-700 uppercase">{{ $user->organisation }}</span>
                                    <span class="text-[9px] text-zinc-500">{{ $user->contact_person }}</span>
                                </div>
                            </flux:button>
                        @endforeach

                        @if ($territory->users->count() > 5)
                            <flux:badge color="zinc" variant="outline" class="self-center">
                                +{{ $territory->users->count() - 5 }} more
                            </flux:badge>
                        @endif
                    </div>
                @endif

                <div class="mt-3">
                    <p class="flex items-center text-gray-700">
                        <flux:icon.globe-europe-africa class="inline-block w-5 h-5 text-gray-500 mr-1" />
                        Countries: {{ implode(', ', $territory->countries->pluck('name')->toArray()) }}
                    </p>
                    <p class="flex items-center text-gray-700">
                        <flux:icon.map class="inline-block w-5 h-5 text-gray-500 mr-1" />
                        Full Provinces: {{ implode(', ', $territory->provinces->pluck('name')->toArray()) }}
                    </p>
                    <p class="flex items-center text-gray-700">
                        <flux:icon.building-office-2 class="inline-block w-5 h-5 text-gray-500 mr-1" />
                        Towns: {{ implode(', ', $territory->zimbabweCities->pluck('name')->toArray()) }}
                    </p>
                </div>
            </div>
        @endforeach
    @endif
</div>
