<?php

use Livewire\Volt\Component;
use App\Enums\ShipmentStatus;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public $totalFreights = 0;
    public $totalInTransit = 0;
    public $totalLoading = 0;
    public $totalDelivered = 0;
    public $user;

    public function loadStats()
    {
        $user = Auth::user();

        // Method 1: Using raw SQL with proper string quoting
        $counts = $user
            ->freights()
            ->selectRaw(
                "COUNT(*) as total, 
                         SUM(CASE WHEN shipment_status = '" .ShipmentStatus::INTRANSIT->value ."' THEN 1 ELSE 0 END) as in_transit,
                         SUM(CASE WHEN shipment_status = '" .ShipmentStatus::DELIVERED->value ."' THEN 1 ELSE 0 END) as delivered,
                         SUM(CASE WHEN shipment_status = '" .ShipmentStatus::LOADING->value ."' THEN 1 ELSE 0 END) as loading
            ")->first();

        $this->totalFreights = $counts->total ?? 0;
        $this->totalInTransit = $counts->in_transit ?? 0;
        $this->totalLoading = $counts->loading ?? 0;
        $this->totalDelivered = $counts->delivered ?? 0;
    }

    public function mount(User $user = null)
    {
        $this->user = $user;
        $this->loadStats();
    }
}; ?>

<div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-gray-200 dark:border-slate-700">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Total Shipments</h3>
        <flux:icon name="cube" class="w-6 h-6 text-blue-500" />
    </div>
    <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ $totalFreights }}</div>
    <div class="space-y-1 text-sm">
        <div class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-400">Loading</span>
            <span class="font-semibold text-amber-600">{{ $totalLoading }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-400">In Transit</span>
            <span class="font-semibold text-green-600">{{ $totalInTransit }}</span>
        </div>        
        <div class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-400">Delivered</span>
            <span class="font-semibold text-red-600">{{ $totalDelivered }}</span>
        </div>
    </div>
</div>
