<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use App\Models\Freight;
use App\Enums\FreightStatus;
use App\Models\User; 
use Illuminate\Support\Facades\DB;

new class extends Component {

    public  $user;

    public $publishedCount = 0;
    public $draftCount = 0;
    public $expiredCount = 0;
    public $pendingCount = 0;

    #[Computed]
    public function shipmentStatusCount()
    { 
        $user = Auth::user();
        $counts = $user->freights()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $freightStatusCount = [];
        foreach (FreightStatus::cases() as $status) {
            $freightStatusCount[$status->value] = [
                'enum' => $status,
                'count' => $counts[$status->value] ?? 0,
                'label' => $status->label(),
                'color' => $status->color(),
            ];
        }

        return $freightStatusCount;
    }
    
    public function mount(User $user = null)
    {
        $this->user = $user;
    }

}; ?>

<div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-gray-200 dark:border-slate-700">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Shipment Status</h3>
        <flux:icon name="document-text" class="w-6 h-6 text-lime-500" />
    </div>
    <div class="space-y-3">
        @foreach ($this->shipmentStatusCount as $statusData)
        <div class="flex justify-between items-center">
            <span class="text-gray-600 dark:text-gray-400">{{ $statusData['label'] }}</span>
            <span
                class="px-3 py-1 bg-{{ $statusData['color'] }}-100 text-{{ $statusData['color'] }}-800 text-sm rounded-full font-medium dark:bg-gray-700 dark:text-gray-200">{{ $statusData['count'] }}</span>
        </div>            
        @endforeach

        
    </div>
</div>
