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
    public $territoryId;
    public $zimbabweTerritoryId;

    public $territory;
    public $zimbabweProvinces = [];
    public $zimbabweCities = [];
    public $showProvinces = false;
    public $showCountries = false;
    public $fullySelectedProvinces = [];
    public $partiallySelectedCities = []; 
    public $territoryExistanceMessage =''; 
    public $createOrUpdateMessage = 'Create';

    // Properties to hold the selected territories
    public $selectedTerritories;
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

    public function createTerritory()
    {
      
       $this->updatedSelectedCountry();   
       $this->updatedSelectedCities();
       $countryIds = Country::whereIn('name', $this->selectedCountry)->pluck('id');
       $this->validate();

       if(!$this->territoryId){
            $territory = Territory::create([
                'name' => $this->territory,
            ]);
            session()->flash('message', 'Territory successfully created.');
       }
       else{
            $territory = Territory::findOrFail($this->territoryId);
            $territory->update(['name' => $this->territory]);
            session()->flash('message', 'Territory successfully updated.');
       }

        // Attach countries, provinces, and cities to the territory
        $territory->countries()->sync($countryIds);
        $territory->provinces()->sync($this->fullySelectedProvinces);
        $territory->zimbabweCities()->sync($this->partiallySelectedCities);

        // Reset the fields
        $this->reset(['territory', 'selectedCountry', 'selectedProvinces', 'selectedCities','showProvinces','territoryExistanceMessage']);
        $this->hideTerritorySelectionDetails();        
        $this->redirectRoute('territories.index');      
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
    }  

    public function checkTerritoryStatus()
    {
        $this->resetValidation([]);
        $territoryExists = Territory::whereName($this->territory)->exists();
        if($territoryExists){
            $this->territoryExistanceMessage = "$this->territory territory already exists! Edit the territory?";  
        }
        else{
            $this->showCountries = true;
        }
               
    }

    public function hideTerritorySelectionDetails()
    {
        $this->showCountries = false;
        $this->showProvinces = false; 
    }

    public function mount(?string $territory = null): void
    {        
        if ($territory) {
            $this->territoryId = $territory;
            $territory = Territory::findOrFail($territory);
            $this->territory = $territory->name;
            $this->selectedProvinces = $territory->provinces()->get();
            
            foreach($this->selectedProvinces as $province){
                $this->selectedCities[] =$province->zimbabweCities()->pluck('id')->toArray();
            }
            $this->selectedProvinces = $this->selectedProvinces->pluck('id')->toArray();
            $this->selectedCities = array_merge(...$this->selectedCities);
            $individuallySelectedCities = $territory->zimbabweCities()->pluck('territory_zimbabwe_city.zimbabwe_city_id')->toArray();
            $this->selectedCities = array_merge($this->selectedCities, $individuallySelectedCities);
            $this->selectedCountry = $territory->countries()->pluck('name')->toArray();           
            $this->showCountries = true;
            $this->getProvinces();
            $this->createOrUpdateMessage = "Update";
        }
    }
 

};

?>

<div class="bg-white rounded-lg shadow p-6 sm:p-8 space-y-6">
    <div>
        <h2 class="text-xl font-semibold text-gray-900">{{ $createOrUpdateMessage }} Territory</h2>
    </div>

    <form wire:submit.prevent="createTerritory" class="space-y-6">
        <div>            
            <flux:input label="Territory Name" description="e.g Eastern Region" wire:model.live="territory" @class(['border-red-500'=>$errors->has('selectedCountry'),'mb-2']) wire:change="checkTerritoryStatus"/>    

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

        <div class="flex items-center gap-4">
            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="w-full">{{$createOrUpdateMessage}}</flux:button>
            </div>

            <x-action-message class="me-3" on="territory-created">
               {{$createOrUpdateMessage}}d.
            </x-action-message>
        </div>       
    </form>
</div>