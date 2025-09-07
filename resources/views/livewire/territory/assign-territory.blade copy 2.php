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
    public $zimbabweTerritoryId;
    public $territory;
    public $zimbabweProvinces = [];
    public $zimbabweCities = [];
    public $showProvinces = false;
    public $showCountries = false;
    public $fullySelectedProvinces = [];
    public $partiallySelectedCities = []; 
    public $territoryExistanceMessage =''; 
    public $isTerritoryAssignedToUser = false;  

    // Properties to hold the selected territories
    public $selectedTerritories = [];
     public $selectedCountry= [];
    public $selectedProvinces = [];
    public $selectedCities = [];
    public $showSuccess = false;



    public function getProvinces()
    {
        $this->showProvinces = true;
        $this->zimbabweProvinces = Province::with('zimbabweCities')->get(['id','name']);
    }

    #[Computed]
    public function countries()
    {
        return Country::get(['name','id']);
    }

    #[Computed]
    public function territories()
    {
       return Territory::all();
    }

    protected function rules(): array
    {
        return [
            'territory' => ['required', 'string', 'max:255'],
            'selectedCountry' => ['required'],
        ];
    }

    public function assignTerritory()
    {
      
       $this->updatedSelectedCountry();   
       $this->updatedSelectedCities();
       $countryIds = Country::whereIn('name', $this->selectedCountry)->pluck('id');
       $this->validate();

        // Create a new territory
        $territory = Territory::create([
            'name' => $this->territory,
        ]);

        // Attach countries, provinces, and cities to the territory
        $territory->countries()->attach($countryIds);
        $territory->provinces()->attach($this->fullySelectedProvinces);
        $territory->zimbabweCities()->attach($this->partiallySelectedCities);

        // // Assign the territory to the user
        $territory->users()->attach($this->userId, ['assigned_by_user_id' => auth()->id()]);         
        // Reset the fields
        $this->reset(['territory', 'selectedCountry', 'selectedProvinces', 'selectedCities','showProvinces','territoryExistanceMessage']);
        $this->hideTerritorySelectionDetails();        
        $this->dispatch('territory-assigned');       
    }

    public function updatedSelectedCountry()
    {
        // Check if Zimbabwe is selected
        if (in_array('zimbabwe', $this->selectedCountry)) {
            $this->zimbabweProvinces = Province::with('zimbabweCities')->get(['id','name']);
            $this->showProvinces = true;
        } 
        else{
            $this->showProvinces = false;
            $this->selectedProvinces = [];
        }
    }

    public function updatedSelectedCities()
    {
        // Clear the explicit arrays to rebuild them based on the new selections.
        $this->fullySelectedProvinces = [];
        $this->partiallySelectedCities = [];

        // This is a dynamic check. We loop through all provinces.
        foreach ($this->zimbabweProvinces as $province) {
            $allCityIdsInProvince = $province->zimbabweCities->pluck('id')->toArray();
            $selectedCityIdsInProvince = array_intersect($this->selectedCities, $allCityIdsInProvince);

            // Handle provinces where all cities are selected
            if (count($allCityIdsInProvince) > 0 && count($allCityIdsInProvince) === count($selectedCityIdsInProvince)) {
                $this->fullySelectedProvinces[] = $province->id;
            } else {
                // Handle cities where the whole province is NOT selected
                $this->partiallySelectedCities = array_merge($this->partiallySelectedCities, $selectedCityIdsInProvince);
            }
        }
        
        // This keeps the province checkbox in sync with the state of its cities
        $this->selectedProvinces = collect($this->selectedProvinces)
            ->reject(fn($id) => !in_array($id, $this->fullySelectedProvinces))
            ->merge($this->fullySelectedProvinces)
            ->unique()
            ->values()
            ->toArray();

        // dd('the fullySelectedProvinces are '.implode(', ',$this->fullySelectedProvinces).' and the partial cities are '.implode(', ',$this->partiallySelectedCities));
    }  

    #[Computed]
    public function territoryAlreadyAssigned()
    {
        $user = User::where('id',$this->userId)->firstOrFail();        
        $this->resetValidation([]);
        if($user->userTerritoryAssignmentStatus($this->territory)){
            $this->showContries = false;
            $this->showProvinces = false;
            $this->territoryExistanceMessage = 'Is already assigned to this territory';
            $this->reset(['selectedCountry', 'selectedProvinces', 'selectedCities','showProvinces', 'showCountries']);
        }
        else{
            $this->showCountries = true;
            return false;            
        }

    }


    public function checkTerritoryStatus()
    {
        $this->resetValidation([]);
        $territoryExists = Territory::whereName($this->territory)->exists();
        $user = User::find($this->userId)->first();
        if($territoryExists){
            $isAssignedToUser = $user->territories()->where('name', $this->territory)->exists();
            //if existing territory is assigned to a user
            if($isAssignedToUser){
                $this->hideTerritorySelectionDetails();
                $this->isTerritoryAssignedToUser = true;
                $this->territoryExistanceMessage = "$this->username is already assigned to $this->territory territory";                
                $this->reset(['selectedCountry', 'selectedProvinces', 'selectedCities','showProvinces', 'showCountries']); 
                return "assigned"               ;
            }
            //existing territory is not assigned to user
            else{                
                $this->reset(['selectedCountry', 'selectedProvinces', 'selectedCities','showProvinces', 'showCountries']);  
                    $this->territoryExistanceMessage = "You want to assign $this->territory territory to: $this->username?";
                    $this->showCountries = true;
                    $this->showProvinces = false;

            }
        }
        elseif(!$this->territory){ 
            $this->territoryExistanceMessage = '';
            $this->hideTerritorySelectionDetails();
        }        
        else{
            $this->territoryExistanceMessage = '';
            $this->showCountries = true;  
             $this->showProvinces = false;              
        }
               
    }

    public function hideTerritorySelectionDetails()
    {
        $this->showCountries = false;
        $this->showProvinces = false; 
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
        <p class="mt-2 text-gray-600">Select / Create a territory to assign: {{ $username }}</p>
    </div>

    @if ($showSuccess)
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded" role="alert">
            <p>Territory assigned successfully!</p>
        </div>
    @endif

    <form wire:submit.prevent="assignTerritory" class="space-y-6">
        <div>
            <datalist id="territory_name">
                @foreach($this->territories as $territory)
                <option value="{{ $territory->name }}">
                @endforeach
            </datalist>
            <flux:input list="territory_name" label="Territory Name" description="e.g Eastern Region" wire:model.live="territory" @class(['border-red-500'=>$errors->has('selectedCountry'),'mb-2']) wire:change="checkTerritoryStatus"/>    

        {{-- {{ $this->checkTerritoryStatus }} --}}
            <div class="">
                <flux:text color="red">{{$territoryExistanceMessage}}</flux:text>
            </div>         
       
             <flux:separator class="my-2" />
        

        @if($showCountries)
            <label class="block text-sm font-medium text-gray-700 mb-2">Select Country</label>        
            <div @class(['border-red-500'=>$errors->has('selectedCountry'), 'space-y-2'])>
            @foreach ($this->countries as $country)
                <div class="flex items-center">
                    <input id="{{ $country->name }}" type="checkbox" wire:model.live="selectedCountry" value="{{ $country->name }}" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                    <label for="{{ $country->name }}" class="ml-3 block text-sm font-medium text-gray-700">{{$country->name}}</label>
                </div>
            @endforeach
                <x-form.input-error field="selectedCountry"/>              
            </div>
        @endif
        </div>


        @if ($showProvinces)
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Province(s) / Town(s)</label>
                <div class="space-y-2">
                    @foreach ($zimbabweProvinces as $province)
                        <div class="flex items-center border-b border-amber-200 my-4">
                            <flux:checkbox.group class="my-4">
                                <flux:checkbox.all label="{{ $province->name }}" value="{{ $province->name }}"/>
                                    <p><span class="my-1">==========================</span></p>
                            @foreach ($province->zimbabweCities as $city)
                                <flux:checkbox label="{{ $city->name }}" value="{{ $city->id }}" class="text-sm text-red-400" wire:model="selectedCities"/>
                            @endforeach    
                            </flux:checkbox.group> 
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="space-y-2 mt-6">
            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"  >Assign</button>
        </div>
        <x-action-message class="me-3 font-medium !dark:text-green-400 !text-green-600" on="territory-assigned">
            {{ __('Assigned.') }}
        </x-action-message>         
    </form>
</div>