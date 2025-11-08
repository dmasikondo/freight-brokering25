<?php

namespace App\Livewire;

use Livewire\Volt\Component;
use App\Models\Contact;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

new class extends Component {

    public $user;
    public $directorsCount = 0;
    public $requiredDirectors = 2;
    
    // Computed properties for dynamic status
    public function getCompletionPercentage(): int
    {
        if ($this->directorsCount >= $this->requiredDirectors) {
            return 100;
        }
        
        return (int) ($this->directorsCount / $this->requiredDirectors * 100);
    }
    
    public function getCompletionText(): string
    {
        return "{$this->directorsCount}/{$this->requiredDirectors}";
    }
    
    public function getIconColor(): string
    {
        if ($this->directorsCount >= $this->requiredDirectors) {
            return 'lime'; // Green for complete
        } elseif ($this->directorsCount > 0) {
            return 'blue'; // Blue for in-progress
        }
        
        return 'lime'; // Lime for not started (empty state)
    }
    
    public function getStatusItems(): array
    {
        $items = [];
        
        for ($i = 1; $i <= $this->requiredDirectors; $i++) {
            if ($i <= $this->directorsCount) {
                // Director exists - show as completed
                $items[] = [
                    'label' => "Director {$i}",
                    'status' => 'completed'
                ];
            } else {
                // Director doesn't exist - show as pending/not started
                $items[] = [
                    'label' => "Director {$i}",
                    'status' => 'pending'
                ];
            }
        }
        
        return $items;
    }
    
    public function mount(User $user = null)
    {
        $this->user = $user;
        $this->loadDirectorsCount();
    }
    
    protected function loadDirectorsCount()
    {        
        $this->directorsCount = $this->user->directors()->count();
    }
    
    // Listen for director creation events
    protected $listeners = ['director-created' => 'updateDirectorsCount', 'contactSaved'=>'showFlashSuccessMesage'];

    public function showFlashSuccessMesage()
    {
        session()->flash('message', 'Director created successfully!');
    }
    
    public function updateDirectorsCount()
    {
        $this->loadDirectorsCount();
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
        title="Directors"
        icon="users"
        :iconColor="$this->getIconColor()"
        :completionPercentage="$this->getCompletionPercentage()"
        :completionText="$this->getCompletionText()"
        :statusItems="$this->getStatusItems()"
        showButton="false"
    >
        <!-- Add Director Button and Modal Trigger -->
        @if($directorsCount < $requiredDirectors)
            <flux:modal.trigger name="create-director">
                <flux:button 
                    class="w-full mt-4" 
                    variant="{{ $directorsCount > 0 ? 'outline' : 'primary' }}"
                >
                    <flux:icon name="user-plus" class="w-4 h-4 mr-2" />
                    {{ $directorsCount > 0 ? 'Add Director' : 'Add First Director' }}
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

    <!-- Include the director creation modal -->
    <livewire:carrier.director.create />
</div>