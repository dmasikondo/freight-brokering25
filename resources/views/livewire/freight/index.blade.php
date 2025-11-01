<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use App\Models\Freight;

new class extends Component {
    #[Locked]
    public $freightId;

    #[Computed]
    public function getFreights()
    {
        return Freight::orderBy('updated_at')
            ->with(['goods', 'contacts', 'createdBy'])
            ->get();
    }

    public function deleteFreight($freightId)
    {
        $this->freightId = $freightId;
        $freight = Freight::findOrFail($this->freightId);
        $freight->delete();
        session()->flash('message', 'The freight was successfully deleted');
    }
}; ?>

<div class="space-y-6">
    <!-- Success Message -->
    @if (session()->has('message'))
        <div class="animate-fade-in">
            <flux:callout icon="check-circle" color="green" class="shadow-lg">
                <flux:callout.heading class="text-green-900 dark:text-green-100">Success!</flux:callout.heading>
                <flux:callout.text class="text-green-800 dark:text-green-200">
                    {{ session('message') }}
                </flux:callout.text>
            </flux:callout>
        </div>
    @endif

    <!-- Empty State -->
    @if ($this->getFreights->isEmpty())
        <div class="text-center py-12">
            <div class="w-24 h-24 mx-auto mb-4 bg-gray-100 dark:bg-slate-800 rounded-full flex items-center justify-center">
                <flux:icon name="truck" class="w-12 h-12 text-gray-400 dark:text-gray-500" />
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No Freight Available</h3>
            <p class="text-gray-600 dark:text-gray-400 max-w-md mx-auto">
                Start by creating your first freight shipment to get started with logistics management.
            </p>
            <button class="mt-4 px-6 py-2 bg-lime-500 text-white rounded-lg hover:bg-lime-600 transition-colors inline-flex items-center gap-2">
                <flux:icon name="plus" class="w-4 h-4" />
                Create First Freight
            </button>
        </div>
    @else
        <!-- Freight Grid -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            @foreach ($this->getFreights as $freight)
                <div class="group relative bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 shadow-sm hover:shadow-lg transition-all duration-300 hover:border-lime-300 dark:hover:border-lime-600">
                    
                    <!-- Header with Status and Actions -->
                    <div class="flex items-start justify-between p-6 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-lime-100 dark:bg-lime-900 rounded-lg flex items-center justify-center">
                                <flux:icon name="cube" class="w-6 h-6 text-lime-600 dark:text-lime-400" />
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white text-lg">
                                    {{ implode(', ', $freight->goods->pluck('name')->toArray()) }}
                                </h3>
                                <p class="text-gray-600 dark:text-gray-400 text-sm">{{ $freight->name }}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <flux:badge size="sm" :color="$freight->status->color()" class="font-medium">
                                {{ $freight->status->label() }}
                            </flux:badge>
                            
                            @auth
                                <flux:dropdown position="bottom" align="end">
                                    <flux:button 
                                        icon:trailing="ellipsis-horizontal" 
                                        variant="ghost" 
                                        size="sm"
                                        class="opacity-0 group-hover:opacity-100 transition-opacity"
                                    />
                                    <flux:navmenu>
                                        <flux:navmenu.item 
                                            href="{{ route('freights.edit', ['freight' => $freight->id]) }}" 
                                            icon="pencil-square"
                                        >
                                            Edit Freight
                                        </flux:navmenu.item>
                                        <flux:navmenu.item 
                                            icon="trash" 
                                            variant="danger"
                                            wire:click="deleteFreight('{{ $freight->id }}')"
                                            wire:confirm.prompt="Are you sure you want to delete the freight: {{ strtoupper($freight->name) }}? \n\nType REMOVE to confirm your action|REMOVE"
                                        >
                                            Remove Freight
                                        </flux:navmenu.item>
                                    </flux:navmenu>
                                </flux:dropdown>
                            @endauth
                        </div>
                    </div>

                    <!-- Hazardous Warning -->
                    @if ($freight->is_hazardous)
                        <div class="px-6 pb-3">
                            <div class="inline-flex items-center gap-2 px-3 py-1 bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-200 rounded-full text-sm">
                                <flux:icon name="exclamation-triangle" class="w-4 h-4" />
                                Hazardous Goods
                            </div>
                        </div>
                    @endif

                    <!-- Weight Separator -->
                    <div class="px-6 pb-4">
                        <div class="flex items-center gap-3 text-sm text-gray-600 dark:text-gray-400">
                            <flux:icon name="scale" class="w-4 h-4 text-gray-400" />
                            <span class="font-medium text-gray-900 dark:text-white">{{ $freight->weight }}</span>
                            <div class="flex-1 border-t border-gray-200 dark:border-slate-600"></div>
                        </div>
                    </div>

                    <!-- Route Information -->
                    <div class="px-6 space-y-4">
                        <!-- Description -->
                        @if ($freight->description)
                            <div class="flex items-start gap-3">
                                <flux:icon name="document-text" class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" />
                                <p class="text-gray-700 dark:text-gray-300 text-sm leading-relaxed">
                                    {{ $freight->description }}
                                </p>
                            </div>
                        @endif

                        <!-- Pickup Location -->
                        <div class="flex items-start gap-3">
                            <flux:icon name="map-pin" class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" />
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">Pickup Location</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ $freight->pickup_address }}, {{ $freight->cityfrom }}, {{ $freight->countryfrom }}
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Location -->
                        <div class="flex items-start gap-3">
                            <flux:icon name="flag" class="w-4 h-4 text-orange-500 mt-0.5 flex-shrink-0" />
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">Delivery Location</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ $freight->delivery_address }}, {{ $freight->cityto }}, {{ $freight->countryto }}
                                </div>
                            </div>
                        </div>

                        <!-- Timeline -->
                        <div class="flex items-start gap-3">
                            <flux:icon name="calendar" class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" />
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">Timeline</div>
                                <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 text-sm text-gray-600 dark:text-gray-400">
                                    <div class="flex items-center gap-1">
                                        <flux:icon name="arrow-right" class="w-3 h-3" />
                                        <span>{{ $freight->datefrom->isoFormat('MMM Do YYYY') }}</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <flux:icon name="arrow-left" class="w-3 h-3" />
                                        <span>{{ $freight->dateto->isoFormat('MMM Do YYYY') }}</span>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-500">
                                        ({{ $freight->dateto->diffForHumans() }})
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bidding Section -->
                    <div class="px-6 pt-4 border-t border-gray-100 dark:border-slate-700">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                <flux:icon name="scale" class="w-4 h-4 text-purple-500" />
                                <span>Open for Bids</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <!-- View Bids Button -->
                                <flux:button 
                                    variant="outline" 
                                    size="sm"
                                    class="border-purple-300 text-purple-600 hover:bg-purple-50 dark:border-purple-600 dark:text-purple-400 dark:hover:bg-purple-900/20"
                                >
                                    <flux:icon name="eye" class="w-4 h-4" />
                                    View Bids
                                </flux:button>
                                
                                <!-- Bid Now Button -->
                                <flux:button 
                                    size="sm"
                                    class="bg-purple-500 text-white hover:bg-purple-600 transition-colors"
                                    wire:click="placeBid('{{ $freight->id }}')"
                                >
                                    <flux:icon name="scale" class="w-4 h-4" />
                                    Bid Now
                                </flux:button>
                            </div>
                        </div>
                        
                        <!-- Bid Statistics -->
                        <div class="mt-3 flex items-center gap-4 text-xs text-gray-500 dark:text-gray-500">
                            <div class="flex items-center gap-1">
                                <flux:icon name="users" class="w-3 h-3" />
                                <span>5 carriers bidding</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <flux:icon name="clock" class="w-3 h-3" />
                                <span>Closes in 2 days</span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer with Creator Info -->
                    @auth
                        <div class="px-6 pt-4 pb-6 border-t border-gray-100 dark:border-slate-700 mt-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <flux:avatar 
                                        name="{{ $freight->createdBy->contact_person }}" 
                                        color="auto" 
                                        size="sm"
                                    />
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $freight->createdBy->contact_person }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-500">
                                            Updated {{ $freight->updated_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Quick Actions -->
                                <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <flux:button 
                                        variant="ghost" 
                                        size="sm"
                                        href="{{ route('freights.edit', ['freight' => $freight->id]) }}"
                                    >
                                        <flux:icon name="pencil-square" class="w-4 h-4" />
                                    </flux:button>
                                    <flux:button 
                                        variant="ghost" 
                                        size="sm"
                                        wire:click="deleteFreight('{{ $freight->id }}')"
                                        wire:confirm.prompt="Are you sure you want to delete the freight: {{ strtoupper($freight->name) }}? \n\nType REMOVE to confirm your action|REMOVE"
                                    >
                                        <flux:icon name="trash" class="w-4 h-4 text-red-500" />
                                    </flux:button>
                                </div>
                            </div>
                        </div>
                    @endauth
                </div>
            @endforeach
        </div>

        <!-- Load More / Pagination -->
        <div class="flex justify-center mt-8">
            <flux:button variant="outline" class="border-lime-300 text-lime-600 hover:bg-lime-50 dark:border-lime-600 dark:text-lime-400 dark:hover:bg-lime-900/20">
                <flux:icon name="arrow-down" class="w-4 h-4" />
                Load More Freights
            </flux:button>
        </div>
    @endif
</div>
