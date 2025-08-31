<?php

use Livewire\Volt\Component;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;

new class extends Component {

    #[Locked]
    public $slug;

    #[Computed]
    public function getUserTerritories()
    {
        $user = User::whereSlug($this->slug)->firstOrFail();
        // Load the territories related to the user along with their related models
        return $user->territories()->with(['countries', 'provinces', 'zimbabweCities'])->get();        
    }

    public function mount(?String $createdUser):void
    {
        if($createdUser){
            $this->slug = $createdUser;
        }
        
    }
}; ?>

<div class="p-6 bg-white shadow-md rounded-lg mb-6 relative">
    @foreach ($this->getUserTerritories as $territory)
        <div class="border-b pb-4 mb-4 last:border-0 relative p-8">       

            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800">
                    {{ $territory->name }}
                </h2>
                <span class="text-sm text-gray-500">
                    Created on {{ $territory->created_at->format('M d, Y') }}
                </span>
            </div>

            <p class="mt-2 text-gray-700">
                <flux:icon.users class="inline-block w-5 h-5 text-gray-600" />
                {{ $territory->users_count }} user{{ $territory->users_count > 1 ? 's' : '' }} in this territory.
            </p>

            @if ($otherUsers = $territory->users->where('id', '!=', auth()->id()))
                <p class="mt-1 text-sm text-gray-600">
                    <span class="font-medium">{{ $otherUsers->count() }} user{{ $otherUsers->count() > 1 ? 's' : '' }}</span> including 
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
                    Provinces: {{ implode(', ', $territory->provinces->pluck('name')->toArray()) }}
                </p>
                <p class="flex items-center text-gray-700">
                    <flux:icon.building-office-2 class="inline-block w-5 h-5 text-gray-500 mr-1" />
                    Cities: {{ implode(', ', $territory->zimbabweCities->pluck('name')->toArray()) }}
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
</div>
