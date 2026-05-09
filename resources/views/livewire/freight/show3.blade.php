<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use App\Models\Freight;
use App\Enums\FreightStatus;

new class extends Component {

    // Store only the key, not the model
    public string $freightUuid;
    public $selectedFreightStatus;

    public function mount(Freight $freight): void
    {
        $this->freightUuid = $freight->uuid;
        $this->selectedFreightStatus = $freight->status->value;
    }

    // Reload fresh on every request
    #[Computed]
    public function freight(): Freight
    {
        return   Freight::where('uuid', $this->freightUuid)
            ->with(['shipper', 'creator', 'activityLogs', 'contacts', 'goods'])
            ->firstOrFail();
    }

    public function updateFreightStatus($status): void
    {
        //$this->authorize('updateStatus', $this->freight);
        $this->freight->update(['status' => $status]);
        $this->selectedFreightStatus = $status;
        session()->flash('status', 'Freight status updated successfully.');
    }
}; ?>

<div class="p-6 lg:p-12 max-w-7xl mx-auto">
@php
 //dd('the freight id is '.$this->freight->id);
@endphp
    <livewire:tender.offer-panel 
    :tenderableId="$this->freight->id" 
    tenderableType="App\Models\Freight" />
</div>