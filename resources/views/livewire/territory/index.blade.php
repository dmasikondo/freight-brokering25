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
        $territories = Territory::orderBy('name')->with(['countries', 'provinces', 'zimbabweCities','users'])->get();

        return $territories;      
    }

    public function deleteTerritory($territoryId)
    {
        // Validate that the user and territory exist
        // $territory = Territory::findOrFail($territoryId);
        // $userId = User::whereSlug($this->slug)->pluck('id');

        // // Check if the user is associated with the territory
        // if ($territory->users()->find($userId)) {
        //     // Remove the user from the territory
        //     $territory->users()->detach($userId);  
        //     $this->dispatch('user-removed');      
        // }

    }

    

   
}; ?>

<div class="p-6 bg-white shadow-md rounded-lg mb-6 relative">
    @if(session()->has('message'))
    <div class="my-2">
        <flux:callout icon="check" color='green'>
            <flux:callout.heading>Territory creation</flux:callout.heading>
            <flux:callout.text color='green'>
            {{ session('message') }}     
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
                        <flux:button icon:trailing="ellipsis-horizontal"/>

                        <flux:navmenu>
                            <flux:navmenu.item href="{{ route('territories.edit',['territory'=>$territory->id]) }}" icon="pencil-square">Edit Territory</flux:navmenu.item>
                            <flux:navmenu.item  icon="trash" variant="danger"                        
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
                    <h2 class="text-xl font-semibold text-gray-800">
                        {{ $territory->name }}
                    </h2>
                    <span class="text-sm text-gray-500">
                        Created on {{ $territory->created_at->format('M d, Y') }}
                    </span>
                </div>

                <p class="mt-2 text-gray-700">
                    <flux:icon.users class="inline-block w-5 h-5 text-gray-600" />
                    {{ $territory->users->count() }} users{{ $territory->users_count > 1 ? 's' : '' }} in this territory.
                </p>

                @if ($otherUsers = $territory->users->where('id', '!=', auth()->id()))
                    <p class="mt-1 text-sm text-gray-600">
                        <span class="font-medium">{{ $otherUsers->count() }} {{Str::plural('user', $otherUsers->count())}}</span> including 
                        @foreach ($otherUsers->take(2) as $user)
                            <a href="{{ route('users.show', $user->slug) }}" class="text-blue-600 hover:underline">{{ $user->name }}</a>{{ !$loop->last ? ', ' : '' }}
                        @endforeach
                        @if ($otherUsers->count() > 2)
                            and {{ $otherUsers->count() - 2 }} more.
                        @endif
                    </p>
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