<?php

namespace App\Livewire;

use Livewire\Volt\Component;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Locked;
use App\Models\User;
use App\Models\Contact;

new class extends Component {
    #[Validate('required|string|max:255')]
    public string $full_name = '';

    #[Validate('required|string|max:500')]
    public string $address = '';

    #[Validate('required|string|max:255')]
    public string $city = '';

    #[Validate('required|string|max:255')]
    public string $country = '';

    #[Validate('nullable|email|max:255')]
    public ?string $email = null;

    #[Validate('nullable|string|max:20')]
    public ?string $whatsapp = null;

    #[Validate('nullable|string|max:20')]
    public ?string $phone = null;

    #[Locked]
    public $userId;

    #[Locked]
    public $directorId;

    public $user;

    protected $listeners = ['editing-director' =>'mount'];

    public function createDirector()
    {
        $validatedContacts = $this->validate();
        $validatedContacts['type'] = 'director';
        $validatedContacts['phone_number'] = $validatedContacts['phone'];
        try {
            $this->user->directors()->updateOrCreate(['contacts.id'=>$this->directorId], $validatedContacts);

            // Close modal
            $this->dispatch('profile-updated');
            $this->dispatch('show-flashMessage');
            \Flux::modals()->close();

            // Reset form
           $this->reset();            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to create director. Please try again.');
        }
    }

    public function cancel()
    {
        $this->reset();
        $this->dispatch('close-modal', name: 'create-director');
    }

    public function mount(User $user = null, $directorId = null)
    {
        $this->user = $user;
        if ($directorId) {
            $this->directorId = $directorId;
            $director = Contact::findOrFail($this->directorId);
            $this->full_name = $director->full_name;
            $this->country = $director->country;
            $this->city = $director->city;
            $this->address = $director->address;
            $this->email = $director->email;
            $this->whatsapp = $director->whatsapp;
            $this->phone = $director->phone_number;
        }
    }
};

?>

<div>
    <!-- Create Director Modal -->
    <flux:modal name="create-director" class="max-w-2xl">
        <form wire:submit="createDirector" class="space-y-6">
            <!-- Header -->
            <div class="text-center">
                <flux:heading size="lg">Add New Director</flux:heading>
                <flux:text class="mt-2">Enter the director's personal and contact information.</flux:text>
            </div>

            <!-- Full Name -->
            <flux:input label="Full Name" placeholder="Enter full name" wire:model="full_name" required />
            <!-- Address -->
            <flux:textarea rows="auto" label="Address" placeholder="Enter complete address" wire:model="address"
                required />

            <!-- City and Country -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <flux:input label="City" placeholder="Enter city" wire:model="city" required />
                </div>

                <div>
                    <flux:input label="Country" placeholder="Enter country" wire:model="country" required />
                </div>
            </div>

            <!-- Contact Information -->
            <div class="border-t border-gray-200 dark:border-slate-700 pt-4">
                <flux:heading size="sm" class="text-gray-900 dark:text-white mb-4">Contact Information (Optional)
                </flux:heading>

                <div class="space-y-4">
                    <!-- Email -->
                    <flux:input label="Email Address" placeholder="email@example.com" type="email"
                        wire:model="email" />

                    <!-- Phone and WhatsApp -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <flux:input label="Phone Number" placeholder="+1234567890" wire:model="phone" />
                            <flux:error name="phone" />
                        </div>

                        <div>
                            <flux:input label="WhatsApp Number" placeholder="+1234567890" wire:model="whatsapp" />
                            <flux:error name="whatsapp" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3 pt-4">
                <flux:spacer />

                <flux:button type="button" variant="outline" wire:click="cancel" wire:loading.attr="disabled">
                    Cancel
                </flux:button>

                <flux:button icon="user-plus" type="submit" variant="primary" wire:loading.attr="disabled">
                    {{$directorId? 'Edit Director': 'Save Director' }}
                </flux:button>
            </div>
        </form>
    </flux:modal>


</div>
