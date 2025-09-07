<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Territory;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Locked;

new class extends Component {

    #[Locked]
    public $slug;
    public $username;

    #[Computed]
    #[On('territory-assigned')]
    public function getUserTerritories()
    {
        $user = User::whereSlug($this->slug)->firstOrFail();

        // Load the territories related to the user along with their related models
        return $user->territories()->with(['countries', 'provinces', 'zimbabweCities'])->get();        
    }

    public function userRemove($territoryId)
    {
        // Validate that the user and territory exist
        $territory = Territory::findOrFail($territoryId);
        $userId = User::whereSlug($this->slug)->pluck('id');

        // Check if the user is associated with the territory
        if ($territory->users()->find($userId)) {
            // Remove the user from the territory
            $territory->users()->detach($userId);  
            $this->dispatch('user-removed');      
        }

    }

    public function userTerritoryAssignmentStatus()
    {
       $user = User::whereSlug($this->slug);

        // Check if the user is assigned to any territories
        $assignedTerritories = $user->territories; // Assuming 'territories' is the relationship name
        dd($assignedTerritories);

        return !$assignedTerritories->isEmpty(); // Returns true if there are assigned territories
    }

    public function mount(?String $createdUser):void
    {
        if($createdUser){
            $this->slug = $createdUser;
            $this->username = User::whereSlug($this->slug)->value('contact_person');
        }
        
    }
}; ?>

<div class="p-6 bg-white shadow-md rounded-lg mb-6 relative">
    @if ($this->getUserTerritories->isEmpty())
        <flux:callout icon="face-frown">
            <flux:callout.heading>No territories</flux:callout.heading>
            <flux:callout.text>
            There are no territories associated with user: {{ $username }}        
            </flux:callout.text>
        </flux:callout>
    @else    
        @foreach ($this->getUserTerritories as $territory)
            <div class="border-b pb-4 mb-4 last:border-0 relative p-8">       
                <div class="mr-2 absolute top-0 right-0">
                    <flux:dropdown position="right" align="end">
                        <flux:button icon:trailing="ellipsis-horizontal"/>

                        <flux:navmenu>
                            <flux:navmenu.item href="#" icon="pencil-square">Edit Territory</flux:navmenu.item>
                            <flux:navmenu.item  icon="trash" variant="danger"                        
                            wire:click="userRemove('{{ $territory->id }}')"
                            wire:confirm.prompt="Are you sure you want to remove the user: {{ $username }} from territory:{{ strtoupper($territory->name) }}? \n\nType REMOVE to confirm your  action|REMOVE">
                            Remove User
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

                {{-- Uncomment if needed
                <div class="mt-4 border-t pt-2">
                    @if ($territory->assignedByUser)
                        <p class="text-sm text-gray-600">
                            Assigned by: <span class="font-semibold">{{ $territory->assignedByUser->name }}</span>
                            <span class="text-gray-400">({{ $territory->pivot->created_at->diffForHumans() }})</span>
                        </p>
                    @else
                        <p class="text-sm text-gray-600">Assigned by: <span class="italic">N/A</span></p>
                    @endif
                </div>
                --}}
            </div>
        @endforeach
    @endif
</div>
