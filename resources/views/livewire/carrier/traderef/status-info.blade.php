<?php

namespace App\Livewire;

use Livewire\Volt\Component;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public $user;
    public $referencesCount = 0;
    public $requiredReferences = 3;

    
    protected $listeners = ['traderef-updated' => 'updateReferencesCount', 'show-tradeFlashMessage'=>'flashMessage'];
    // Computed properties for dynamic status
    public function getCompletionPercentage(): int
    {
        if ($this->referencesCount >= $this->requiredReferences) {
            return 100;
        }
        
        return (int) ($this->referencesCount / $this->requiredReferences * 100);
    }
    
    public function getCompletionText(): string
    {
        return "{$this->referencesCount}/{$this->requiredReferences}";
    }
    
    public function getIconColor(): string
    {
        if ($this->referencesCount >= $this->requiredReferences) {
            return 'emerald'; // Green for complete
        } elseif ($this->referencesCount > 0) {
            return 'amber'; // Blue for in-progress
        }
        
        return 'emerald'; // Lime for not started (empty state)
    }
    
    public function getStatusItems(): array
    {
        $items = [];
        
        for ($i = 1; $i <= $this->requiredReferences; $i++) {
            if ($i <= $this->referencesCount) {
                // Reference exists - show as completed
                $items[] = [
                    'label' => "Reference {$i}",
                    'status' => 'completed'
                ];
            } else {
                // Reference doesn't exist - show as pending/not started
                $items[] = [
                    'label' => "Reference {$i}",
                    'status' => 'pending'
                ];
            }
        }
        
        return $items;
    }
    
    public function mount(User $user = null)
    {
        $this->user = $user;
        $this->loadReferencesCount();
    }
    
    protected function loadReferencesCount()
    {
       
        $this->referencesCount = $this->user->traderefs()->count();
    }
    
 

    public function showFlashSuccessMesage()
    {
        session()->flash('message', 'Trade Reference created successfully!');
    }
    
    public function updateReferencesCount()
    {
        $this->loadReferencesCount();
    }

    public function flashMessage()
    {
        session()->flash('message', 'trade Reference record successfully updated!');
    }    
};

?>

<div>
  <!-- Success Message -->
    @if (session()->has('message'))
        <div class="fixed top-4 right-4 z-50">
            <flux:callout icon="check-circle" color="green" class="shadow-lg animate-fade-in">
                <flux:callout.heading class="text-green-900 dark:text-green-100">Success!</flux:callout.heading>
                <flux:callout.text class="text-green-800 dark:text-green-200">
                    {{ session('message') }}
                </flux:callout.text>
            </flux:callout>
        </div>
    @endif

    <!-- Error Message -->
    @if (session()->has('error'))
        <div class="fixed top-4 right-4 z-50">
            <flux:callout icon="x-circle" color="red" class="shadow-lg animate-fade-in">
                <flux:callout.heading class="text-red-900 dark:text-red-100">Error!</flux:callout.heading>
                <flux:callout.text class="text-red-800 dark:text-red-200">
                    {{ session('error') }}
                </flux:callout.text>
            </flux:callout>
        </div>
    @endif    
    <x-card.status-progress
        title="Trade References"
        icon="bookmark"
        :iconColor="$this->getIconColor()"
        :completionPercentage="$this->getCompletionPercentage()"
        :completionText="$this->getCompletionText()"
        :statusItems="$this->getStatusItems()"
        showButton="false"
        buttonText="View Traderefs"
        modalName="show-traderefs"        
    >
        <!-- Add TradeRef Button and Modal Trigger -->
        @if($referencesCount < $requiredReferences)
            <flux:modal.trigger name="create-traderef">
                <flux:button 
                    class="w-full mt-4" 
                    variant="{{ $referencesCount > 0 ? 'outline' : 'primary' }}"
                >
                    <flux:icon name="user-plus" class="w-4 h-4 mr-2" />
                    {{ $referencesCount > 0 ? 'Add TradeRef' : 'Add First TradeRef' }}
                </flux:button>
            </flux:modal.trigger>
        @else
            <flux:button 
                class="w-full mt-4" 
                variant="outline"
                disabled
            >
                <flux:icon name="check-circle" class="w-4 h-4 mr-2" />
                Complete
            </flux:button>
        @endif
    </x-card.status-progress>

    <!-- Include the trade reference creation modal -->
    <livewire:carrier.traderef.create :user="$user" />
    <livewire:carrier.traderef.index :user="$user" />
</div>