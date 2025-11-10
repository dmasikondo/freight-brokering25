<?php

use Livewire\Volt\Component;
use App\Models\User;
use Livewire\Attributes\On;

new class extends Component {

    public User $user;    
    public bool $profileComplete = false;
    public string $missingSections = '';

   
    public function mount(User $user)
    {
        $this->user = $user;
        $this->checkProfileCompletion();
    }

    #[On('profile-updated')]
    public function checkProfileCompletion()
    {
        $missing = [];
        
        // Check if user has any fleet
       
        if ($this->user->fleets()->count() === 0) {
            $missing[] = 'fleet';
        }
        
        // Check if user has any directors
        
        if ($this->user->directors()->count() < 2) {
            $missing[] = 'directors';
        }
        
        // Check if user has any trade references
        if ($this->user->traderefs()->count() < 3) {
            $missing[] = 'trade references';
        }

        // Check if user has business location
        if ($this->user->buslocation()->count() ==0) {
            $missing[] = 'business location';
        }        
        
        $this->profileComplete = empty($missing);
        $this->missingSections = implode(', ', $missing);
    }
}; ?>

<div>
    @if (!$profileComplete)
        <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">
                        Your profile is not complete
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>
                            Complete your profile to start accepting loads. 
                            @if($missingSections)
                                Missing: {{ $missingSections }}
                            @endif
                        </p>
                    </div>
                    <div class="mt-4">
                        <div class="flex space-x-3">
                            <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-yellow-700 bg-yellow-100 hover:bg-yellow-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                Complete Profile
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>