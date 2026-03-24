<?php
use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Territory;

new class extends Component {
    public User $user;
    public Territory $territory;
    public bool $isAssigned = false;

    public function mount() {
        $this->isAssigned = $this->user->userTerritoryAssignmentStatus($this->territory->name);
    }

    public function toggleAssignment() {
        if ($this->isAssigned) {
            $this->user->territories()->detach($this->territory->id);
            $this->isAssigned = false;
        } else {
            $this->user->territories()->attach($this->territory->id, [
                'assigned_by_user_id' => auth()->id()
            ]);
            $this->isAssigned = true;
        }
    }
}; ?>

<div>
    <button wire:click="toggleAssignment" 
        @class([
            'w-full flex items-center justify-between p-4 rounded-2xl border transition-all text-left',
            'bg-white dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 shadow-sm' => !$isAssigned,
            'bg-indigo-50 dark:bg-indigo-950/20 border-indigo-200 dark:border-indigo-800 ring-1 ring-indigo-500' => $isAssigned,
        ])>
        <div>
            <p @class([
                'text-[10px] font-black uppercase tracking-tight',
                'text-zinc-400' => !$isAssigned,
                'text-indigo-600 dark:text-indigo-400' => $isAssigned,
            ])>{{ $territory->name }}</p>
            <p class="text-[9px] text-zinc-500">{{ $territory->zimbabweCities->count() }} Cities</p>
        </div>
        
        <div @class([
            'size-5 rounded-full flex items-center justify-center transition-colors',
            'bg-zinc-100 dark:bg-zinc-800' => !$isAssigned,
            'bg-indigo-600 text-white' => $isAssigned,
        ])>
            @if($isAssigned)
                <flux:icon.check variant="mini" class="size-3" />
            @else
                <flux:icon.plus variant="mini" class="size-3 text-zinc-400" />
            @endif
        </div>
    </button>
</div>