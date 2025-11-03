<?php

namespace App\Livewire;

use Livewire\Volt\Component;
use App\Models\Fleet;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public $fleetsCount = 0;
    public $horsesCount = 0;
    public $trailersCount = 0;
    public $trailerTypes = [];
    public $completionPercentage = 0;
    public $completionText = '0/1';
    public $iconColor = 'amber';
    public $statusItems = [];

    public function mount()
    {
        $this->loadFleetData();
        $this->updateStatus();
    }

    protected function loadFleetData()
    {
        $user = Auth::user();
        $fleets = $user->fleets()->with('trailers')->get();

        $this->fleetsCount = $fleets->count();
        $this->horsesCount = $fleets->sum('horses');
        $this->trailersCount = $fleets->sum('trailer_qty');

      /*  $uniqueTrailers = $fleets
            ->flatMap(function (Fleet $fleet) {
                return $fleet->trailers;
            })
            ->unique('id'); // Ensure uniqueness based on the trailer ID
        // 3. Map the unique Trailer models to the desired array structure
        $this->trailerTypes = $uniqueTrailers
            ->map(function ($trailer) {
                // Access the Enum-casted property
                $trailerTypeEnum = $trailer->name;
                return [
                    'label' => $trailerTypeEnum?->label(), // e.g., "Air Ride Van"
                    'iconName' => $trailerTypeEnum?->iconName(), // e.g., "air-ride-van"
                ];
            })
            ->values()
            ->toArray(); */

        //Get unique trailer types
        $this->trailerTypes = $fleets->flatMap(function($fleet) {
            return $fleet->trailers->map(function($trailer) {
                return [
                    'iconName' => $trailer->name->iconName(),
                    'label' => $trailer->name?->label()
                ];
            });
        })->unique('name')->values()->toArray();
    }

    protected function updateStatus()
    {
        // Calculate completion percentage
        
        $totalItems = 3;
        if ($this->fleetsCount > 0) {
            $this->completionPercentage = 100;
        } else {
            $completionFactors = 0;
            //$totalFactors = 3;
            $this->completionPercentage = 0;
        }

        // Calculate completion text
        if ($this->fleetsCount === 0) {
            $this->completionText = "0/{$totalItems}";
        } else {
            $completedItems = 3;

            // if ($this->horsesCount > 0) $completedItems++;
            // if ($this->trailersCount > 0) $completedItems++;
            // if (!empty($this->trailerTypes)) $completedItems++;

            $this->completionText = "{$completedItems}/{$totalItems}";
        }

        // Set icon color
        if ($this->fleetsCount > 0) {
            $this->iconColor = 'emerald';
        } else {
            $this->iconColor = 'amber';
        }

        // Set status items
        $this->statusItems = $this->getStatusItems();
    }

    protected function getStatusItems(): array
    {
        $items = [];

        // No. of horses status
        if ($this->fleetsCount > 0) {
            $items[] = [
                'label' => "No. of horses - {$this->horsesCount}",
                'status' => 'completed',
            ];
        } else {
            $items[] = [
                'label' => 'No. of horses',
                'status' => 'pending',
            ];
        }

        // No. of trailers status
        if ($this->trailersCount > 0) {
            $items[] = [
                'label' => "No. of trailers - {$this->trailersCount}",
                'status' => 'completed',
            ];
        } else {
            $items[] = [
                'label' => 'No. of trailers',
                'status' => 'pending',
            ];
        }

        // Type of trailers status
        if (!empty($this->trailerTypes)) {
            $trailerTypesList = collect($this->trailerTypes)->pluck('label')->join(', ');
            $items[] = [
                'label' => "Type of Trailers - {$trailerTypesList}",
                'status' => 'completed',
            ];
        } else {
            $items[] = [
                'label' => 'Type of Trailers',
                'status' => 'pending',
            ];
        }

        return $items;
    }

    // Listen for fleet creation/update events
    protected $listeners = ['fleet-created' => 'updateFleetData', 'fleet-updated' => 'updateFleetData'];

    public function updateFleetData()
    {
        $this->loadFleetData();
        $this->updateStatus();
    }
};

?>

<div>
    <x-card.status-progress title="Fleet Information" icon="truck" :iconColor="$iconColor" :completionPercentage="$completionPercentage" :completionText="$completionText"
        :statusItems="$statusItems" showButton="false">
        <!-- Custom content for trailer type icons -->
        @if (!empty($trailerTypes))
            <div class="mt-3 p-3 bg-gray-50 dark:bg-slate-700 rounded-lg">
                <flux:text size="sm" class="font-medium text-gray-700 dark:text-gray-300 mb-2">Trailer Types:
                </flux:text>
                <div class="flex flex-wrap gap-2">
                    @foreach ($trailerTypes as $trailerType)
                        <div
                            class="flex items-center gap-1 px-2 py-1 bg-white dark:bg-slate-600 rounded border border-gray-200 dark:border-slate-500">
                            <x-graphic name="{{ $trailerType['iconName']}}"
                                class="size-24 text-gray-600 dark:text-gray-400" />
                            <span class="text-xs text-gray-700 dark:text-gray-300">{{ $trailerType['label']}}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Add Fleet Button and Modal Trigger -->
        @if ($fleetsCount === 0 || $completionPercentage < 100)
            <flux:modal.trigger name="manage-fleet">
                <flux:button class="w-full mt-4" variant="{{ $fleetsCount > 0 ? 'outline' : 'primary' }}">
                    <flux:icon name="{{ $fleetsCount > 0 ? 'pencil-square' : 'plus' }}" class="w-4 h-4 mr-2" />
                    {{ $fleetsCount > 0 ? 'Manage Fleet' : 'Add Fleet Information' }}
                </flux:button>
            </flux:modal.trigger>
        @else
            <flux:button class="w-full mt-4" variant="outline" disabled>
                <flux:icon name="check-circle" class="w-4 h-4 mr-2" />
                Complete
            </flux:button>
        @endif
    </x-card.status-progress>

    <!-- Include the fleet management modal -->
    <livewire:carrier.fleet.create />
</div>
