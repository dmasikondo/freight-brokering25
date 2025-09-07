<?php

use App\Models\Territory;
use App\Models\User;
use App\Models\Province;
use App\Models\Country;
use App\Models\ZimbabweCity;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Illuminate\Support\Facades\Session;
use Livewire\Volt\Component;

new class extends Component
{
    #[Locked]
    public $userId;
    public $username;
    public $territory;
    public $territoryExistanceMessage;

    #[Computed]
    public function territories()
    {
       return Territory::all();
    }

    protected function rules(): array
    {
        return [
            'territory' => ['required', 'string', 'max:255'],
        ];
    }

    public function assignTerritory()
    { 
        $this->validate();

        $territory = Territory::whereName($this->territory)->firstOrFail();
        $user = User::find($this->userId)->first();
        $userAlreadyAssignedToTerritory = $user->territories()->where('name', $this->territory)->exists();
        //dd($userAlreadyAssignedToTerritory);
        if(!$userAlreadyAssignedToTerritory){
        // // Assign the territory to the user
        $territory->users()->attach($this->userId, ['assigned_by_user_id' => auth()->id()]); 
        // Reset the fields
        $this->reset(['territory']);        
        $this->dispatch('territory-assigned');               
        }
        else{
            $this->territoryExistanceMessage = 'Can not reassign user to the same territory';
        }       
     
    } 

    public function isUserAssignedToTerritory()
    {
        $this->resetValidation([]);
        $territoryExists = Territory::whereName($this->territory)->exists();
        $user = User::find($this->userId)->first();
        if($territoryExists){
            $isAssignedToUser = $user->territories()->where('name', $this->territory)->exists();
            //if existing territory is assigned to a user
            if($isAssignedToUser){
                $this->territoryExistanceMessage = "$this->username is already assigned to $this->territory territory";  
                return true;
            }
            else{
                 $this->territoryExistanceMessage = "";
                return false;
            }
        }
               
    }


    public function mount(?string $createdUser): void
    {
        if($createdUser){
            $this->userId = User::whereSlug($createdUser)->pluck('id');
            $this->username = User::whereSlug($createdUser)->value('contact_person');
        }
    }
 



};

?>

<div class="bg-white rounded-lg shadow p-6 sm:p-8 space-y-6">
    <div>
        <h2 class="text-xl font-semibold text-gray-900">Assign Territory</h2>
        <p class="mt-2 text-gray-600">Select A territory to assign: {{ $username }}</p>
    </div>    

    <form wire:submit.prevent="assignTerritory" class="space-y-6">
        <div>
            <flux:fieldset>
                <flux:legend>Territory</flux:legend>
                <flux:radio.group wire:model.live="territory" wire:change="isUserAssignedToTerritory">
                @foreach ($this->territories as $territory)
                    <flux:radio value="{{ $territory->name }}" label="{{ $territory->name }}"/>
                @endforeach
                </flux:radio.group>
            </flux:fieldset>

            <div class="">
                <flux:text color="red">{{$territoryExistanceMessage}}</flux:text>
            </div>         
       
             <flux:separator class="my-2" />    
        </div>

        <div class="space-y-2 mt-6">
            <flux:button variant="primary" type="submit" class="w-full">
                {{ __('Assign') }}
            </flux:button>
        </div>
        <x-action-message class="me-3 font-medium !dark:text-green-400 !text-green-600" on="territory-assigned">
            {{ __('Assigned.') }}
        </x-action-message>         
    </form>
</div>