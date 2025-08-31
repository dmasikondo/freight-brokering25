<?php

namespace App\Livewire\Territory;

use Livewire\Component;
use App\Models\Province;
use App\Models\ZimbabweCity;
use App\Models\Territory; // Make sure to import the Territory model

class TerritoryAssignment extends Component
{
    public $countries = ['Zimbabwe', 'South Africa'];
    public $selectedCountry;
    public $provinces = [];
    public $selectedProvince;
    public $cities = [];
    public $selectedCity;

    // Method to handle changes in selected country
    public function updatedSelectedCountry($country)
    {
        if ($country === 'Zimbabwe') {
            $this->provinces = Province::all();
        } 

        // Reset province and cities when country changes
        $this->selectedProvince = null;
        $this->cities = [];
        $this->selectedCity = null; // Reset selected city
    }

    // Method to handle changes in selected province
    public function updatedSelectedProvince($provinceId)
    {
        $this->cities = ZimbabweCity::where('province_id', $provinceId)->get();
        $this->selectedCity = null; // Reset selected city
    }

    // Method to assign territory
    public function assignTerritory()
    {
        // Validate the selected values
        $this->validate([
            'selectedCountry' => 'required',
            'selectedProvince' => 'required',
            'selectedCity' => 'required',
        ]);

        // Create a new territory record
        Territory::create([
            'country' => $this->selectedCountry,
            'province_id' => $this->selectedProvince,
            'city_id' => $this->selectedCity,
        ]);

        // Optionally reset the fields after assignment
        $this->reset(['selectedCountry', 'selectedProvince', 'selectedCity', 'provinces', 'cities']);

        // Optionally add a success message
        session()->flash('message', 'Territory assigned successfully!');
    }

    // Render method to return the view
    public function render()
    {
        return view('livewire.territory.territory-assignment', [
            'countries' => $this->countries,
        ]);
    }
}