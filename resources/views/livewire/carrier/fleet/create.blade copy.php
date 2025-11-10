<?php

namespace App\Livewire;

use Livewire\Volt\Component;
use Livewire\Attributes\Validate;
use App\Models\Fleet;
use App\Models\Trailer;
use App\Models\User;
use App\Enums\TrailerType;
use Illuminate\Support\Facades\Auth;

new class extends Component {

    public $user;

    #[Validate('required|integer|min:0', message: 'Number of trailers is required')]
    public int $trailer_qty = 0;

    #[Validate('required|integer|min:0', message: 'Number of horses is required')]
    public int $horses = 0;

    #[Validate('required|array|min:1', message: 'Please select at least one trailer type')]
    public array $selectedTrailerTypes = [];

    public $existingFleet = null;
    public $availableTrailerTypes = [];

    public function mount(User $user = null)
    {
        $this->user = $user;
        $this->loadExistingFleet();
        $this->availableTrailerTypes = TrailerType::cases();
    }

    protected function loadExistingFleet()
    {        
        $this->existingFleet = $this->user?->fleets()->with('trailers')->first();

        if ($this->existingFleet) {
            $this->trailer_qty = $this->existingFleet->trailer_qty;
            $this->horses = $this->existingFleet->horses;
            
            // Get the trailer enum values and convert to string values
            $this->selectedTrailerTypes = $this->existingFleet->trailers
                ->pluck('name') // This returns TrailerType objects due to the cast
                ->map(function (TrailerType $trailerType) {
                    return $trailerType->value; // Convert to string value
                })
                ->toArray();
        }

        
    }

    public function saveFleet()
    {
        $this->validate();

        try {
            
            // Get or create trailer records for selected types
            $trailerIds = [];
            foreach ($this->selectedTrailerTypes as $trailerTypeValue) {
                $trailer = Trailer::firstOrCreate([
                    'name' => $trailerTypeValue
                ]);
                $trailerIds[] = $trailer->id;
            }

            if ($this->existingFleet) {
                // Update existing fleet
                $this->existingFleet->update([
                    'trailer_qty' => $this->trailer_qty,
                    'horses' => $this->horses,
                ]);
                
                // Sync trailers (more efficient than attach/detach)
                $this->existingFleet->trailers()->sync($trailerIds);
                
                $message = 'Fleet information updated successfully!';
            } else {
                // Create new fleet
                $fleet = $this->user->fleets()->create([
                    'trailer_qty' => $this->trailer_qty,
                    'horses' => $this->horses,
                ]);
                
                // Attach trailers using sync for efficiency
                $fleet->trailers()->sync($trailerIds);
                
                $message = 'Fleet information created successfully!';
            }

            // Reset form and close modal
           // $this->resetExcept(['availableTrailerTypes']);
           $this->reset();
            $this->loadExistingFleet(); // Reload in case it was created
            
            // Dispatch events
            $this->dispatch('fleet-updated','');
            
            \Flux::modals()->close();              
            // Show success message
            session()->flash('message', $message);

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to save fleet information. Please try again.');
        }
    }

    public function cancel()
    {
        $this->resetExcept(['availableTrailerTypes']);
        $this->loadExistingFleet(); // Reload original data
        $this->dispatch('close-modal', name: 'manage-fleet');
    }

    public function toggleTrailerType($trailerTypeValue)
    {
        // Convert to string to ensure consistency
        $value = (string) $trailerTypeValue;
        
        if (in_array($value, $this->selectedTrailerTypes)) {
            $this->selectedTrailerTypes = array_diff($this->selectedTrailerTypes, [$value]);
        } else {
            $this->selectedTrailerTypes[] = $value;
        }
    }

    // Helper to get TrailerType enum from string value
    public function getTrailerTypeFromValue($value): ?TrailerType
    {
        try {
            return TrailerType::from((string) $value);
        } catch (\ValueError $e) {
            return null;
        }
    }

    // Add render method to ensure variables are passed to the view

};

?>
<div>
    <!-- Fleet Management Modal -->
    <flux:modal name="manage-fleet" class="max-w-2xl">
        <form wire:submit="saveFleet" class="space-y-6">
            <!-- Header -->
            <div class="text-center">
                <flux:heading size="lg">
                    {{-- {{ $user->organisation }}:  --}}
                    {{ $existingFleet ? 'Update Fleet Information' : 'Add Fleet Information' }}
                </flux:heading>
                <flux:text class="mt-2">
                    {{ $existingFleet ? 'Update your fleet details and trailer types.' : 'Enter your fleet details and trailer types.' }}
                </flux:text>
            </div>

            <!-- Fleet Quantities -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Number of Horses -->
                <div>
                    <flux:input 
                        label="Number of Horses" 
                        placeholder="Enter number of horses"
                        type="number"
                        min="0"
                        wire:model="horses"
                        required
                    />
                </div>

                <!-- Number of Trailers -->
                <div>
                    <flux:input 
                        label="Number of Trailers" 
                        placeholder="Enter number of trailers"
                        type="number"
                        min="0"
                        wire:model="trailer_qty"
                        required
                    />
                </div>
            </div>

            <!-- Trailer Types Selection -->
            <div class="border-t border-gray-200 dark:border-slate-700 pt-4">
                <flux:heading size="sm" class="text-gray-900 dark:text-white mb-4">
                    Trailer Types (Select all that apply)
                </flux:heading>
                
                @error('selectedTrailerTypes')
                    <flux:text class="text-red-600 text-sm mt-1 mb-4">{{ $message }}</flux:text>
                @enderror

                <!-- Trailer Type Grid -->
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                    @foreach($availableTrailerTypes as $trailerType)
                        @php
                            $isSelected = in_array($trailerType->value, $selectedTrailerTypes);
                            $iconName = $trailerType->iconName();
                        @endphp
                        
                        <button 
                            type="button"
                            wire:click="toggleTrailerType('{{ $trailerType->value }}')"
                            class="p-3 border rounded-lg text-left transition-all duration-200 {{ 
                                $isSelected 
                                ? 'border-lime-500 bg-lime-50 dark:bg-lime-900/20 dark:border-lime-600' 
                                : 'border-gray-300 dark:border-slate-600 hover:border-lime-400 dark:hover:border-lime-500' 
                            }}"
                        >
                            <div class="flex items-center gap-2">
                                <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-md {{ 
                                    $isSelected 
                                    ? 'bg-lime-100 dark:bg-lime-800' 
                                    : 'bg-gray-100 dark:bg-slate-700' 
                                }}">
                                    <x-graphic 
                                        name="{{ $iconName }}" 
                                        class="w-4 h-4 {{ 
                                            $isSelected 
                                            ? 'text-lime-600 dark:text-lime-400' 
                                            : 'text-gray-600 dark:text-gray-400' 
                                        }}" 
                                    />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ $trailerType->label() }}
                                    </div>
                                    <div class="flex items-center mt-1">
                                        <div class="w-3 h-3 rounded-full border {{ 
                                            $isSelected 
                                            ? 'bg-lime-500 border-lime-500' 
                                            : 'bg-white border-gray-400 dark:bg-slate-600 dark:border-slate-500' 
                                        }}"></div>
                                        <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">
                                            {{ $isSelected ? 'Selected' : 'Select' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </button>
                    @endforeach
                </div>

                <!-- Selected Trailers Summary -->
                @if(count($selectedTrailerTypes) > 0)
                    <div class="mt-4 p-3 bg-lime-50 dark:bg-lime-900/20 rounded-lg border border-lime-200 dark:border-lime-700">
                        <flux:text size="sm" class="font-medium text-lime-800 dark:text-lime-200 mb-2">
                            Selected Trailer Types ({{ count($selectedTrailerTypes) }}):
                        </flux:text>
                        <div class="flex flex-wrap gap-2">
                           
                            @foreach($selectedTrailerTypes as $selectedType)
                                @php
                                    $trailerType = $this->getTrailerTypeFromValue($selectedType);
                                @endphp
                                @if($trailerType)
                                    <div class="flex items-center gap-1 px-2 py-1 bg-white dark:bg-slate-600 rounded-full border border-lime-300 dark:border-lime-600">
                                        <x-graphic name="{{ $trailerType->iconName() }}" class="w-3 h-3 text-lime-600 dark:text-lime-400" />
                                        <span class="text-xs text-lime-700 dark:text-lime-300">{{ $trailerType->label() }}</span>
                                        <button 
                                            type="button"
                                            wire:click="toggleTrailerType('{{ $selectedType }}')"
                                            class="ml-1 text-lime-500 hover:text-lime-700 dark:hover:text-lime-300"
                                        >
                                            <flux:icon name="x-mark" class="w-3 h-3" />
                                        </button>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3 pt-4 border-t border-gray-200 dark:border-slate-700">
                <flux:spacer />

                <flux:button type="button" variant="outline" wire:click="cancel" wire:loading.attr="disabled">
                    Cancel
                </flux:button>

                <flux:button icon="user-plus" type="submit" variant="primary" wire:loading.attr="disabled">
                    Add Fleet Information
                </flux:button>
                


            </div>
        </form>
    </flux:modal>

    <!-- Success/Error Messages -->
    @if (session()->has('message'))
        <div class="fixed top-4 right-4 z-50">
            <flux:callout icon="check-circle" color="green" class="shadow-lg">
                <flux:callout.heading>Success!</flux:callout.heading>
                <flux:callout.text>
                    {{ session('message') }}
                </flux:callout.text>
            </flux:callout>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="fixed top-4 right-4 z-50">
            <flux:callout icon="x-circle" color="red" class="shadow-lg">
                <flux:callout.heading>Error!</flux:callout.heading>
                <flux:callout.text>
                    {{ session('error') }}
                </flux:callout.text>
            </flux:callout>
        </div>
    @endif
</div>