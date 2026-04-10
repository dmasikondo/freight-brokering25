<?php

use App\Models\Territory;
use App\Models\Province;
use App\Models\Country;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;
use Illuminate\Support\Collection;

new class extends Component {
    #[Locked]
    public $territoryId;

    public $territory;
    public $createOrUpdateMessage = 'Create';
    public $territoryExistanceMessage = '';

    // UI Toggles
    public $showCountries = false;
    public $showProvinces = false;

    // Selection Data
    public $selectedCountry = []; // Array of names
    public $selectedCities = []; // Array of City IDs (as strings)
    public $zimbabweProvinces = []; // Array for hydration speed

    public function mount(?string $territory = null): void
    {
        if ($territory) {
            $this->territoryId = $territory;
            $t = Territory::with(['provinces.zimbabweCities', 'zimbabweCities', 'countries'])->findOrFail($territory);

            $this->territory = $t->name;
            $this->createOrUpdateMessage = 'Update';
            $this->showCountries = true;

            $this->selectedCountry = $t->countries->pluck('name')->toArray();

            // Map existing cities (ensuring string IDs for checkbox consistency)
            $cityIdsFromProvinces = $t->provinces->flatMap->zimbabweCities->pluck('id')->toArray();
            $individualCityIds = $t->zimbabweCities->pluck('id')->toArray();
            $this->selectedCities = array_unique(array_map('strval', array_merge($cityIdsFromProvinces, $individualCityIds)));

            $this->updatedSelectedCountry();
        }
    }

    public function updatedSelectedCountry()
    {
        $selected = collect($this->selectedCountry)->map(fn($v) => strtolower($v));

        if ($selected->contains('zimbabwe')) {
            // Load provinces ordered by name
            // AND load nested cities ordered by name
            $this->zimbabweProvinces = Province::with([
                'zimbabweCities' => function ($query) {
                    $query->select('id', 'province_id', 'name')->orderBy('name', 'asc');
                },
            ])
                ->orderBy('name')
                ->get()
                ->toArray();

            $this->showProvinces = true;
        } else {
            $this->showProvinces = false;
            $this->selectedCities = [];
        }
    }

    /**
     * Toggles all cities within a specific province
     */
    public function toggleProvince($provinceId)
    {
        $province = collect($this->zimbabweProvinces)->firstWhere('id', $provinceId);
        if (!$province) {
            return;
        }

        $cityIds = collect($province['zimbabwe_cities'])->pluck('id')->map(fn($id) => (string) $id)->toArray();
        $currentSelected = collect($this->selectedCities);

        // Check if all cities in this province are already selected
        $allSelected = collect($cityIds)->every(fn($id) => $currentSelected->contains($id));

        if ($allSelected) {
            // Remove all cities of this province
            $this->selectedCities = $currentSelected->diff($cityIds)->values()->toArray();
        } else {
            // Add all cities (merge and unique)
            $this->selectedCities = $currentSelected->merge($cityIds)->unique()->values()->toArray();
        }
    }

    public function checkTerritoryStatus()
    {
        $this->resetValidation();
        $exists = Territory::whereName($this->territory)->exists();

        if ($exists && !$this->territoryId) {
            $this->territoryExistanceMessage = "{$this->territory} territory already exists!";
            $this->showCountries = false;
        } else {
            $this->territoryExistanceMessage = '';
            $this->showCountries = true;
        }
    }

    public function createTerritory()
    {
        $this->validate(
            [
                'territory' => ['required', 'string', 'max:255'],
                'selectedCountry' => ['required', 'array', 'min:1'],
            ],
            [
                'selectedCountry.required' => 'Please select at least one country for this territory.',
                'selectedCountry.min' => 'You must select at least one country.',
            ],
        );

        $fullySelectedProvinces = [];
        $partiallySelectedCities = [];

        if (collect($this->selectedCountry)->map(fn($v) => strtolower($v))->contains('zimbabwe')) {
            $provinces = Province::with('zimbabweCities')->get();

            foreach ($provinces as $province) {
                $allCityIds = $province->zimbabweCities->pluck('id')->map(fn($id) => (string) $id)->toArray();
                $selectedInProvince = array_intersect($this->selectedCities, $allCityIds);

                if (count($allCityIds) > 0 && count($allCityIds) === count($selectedInProvince)) {
                    $fullySelectedProvinces[] = $province->id;
                } else {
                    $partiallySelectedCities = array_merge($partiallySelectedCities, $selectedInProvince);
                }
            }
        }

        $countryIds = Country::whereIn('name', $this->selectedCountry)->pluck('id');

        $territory = $this->territoryId ? Territory::findOrFail($this->territoryId) : new Territory();
        $territory->name = $this->territory;
        $territory->save();

        $territory->countries()->sync($countryIds);
        $territory->provinces()->sync($fullySelectedProvinces);
        $territory->zimbabweCities()->sync($partiallySelectedCities);

        session()->flash('message', 'Territory saved successfully.');
        return $this->redirectRoute('territories.index', navigate: true);
    }
};
?>

<div
    class="bg-white dark:bg-zinc-900 rounded-[2rem] shadow-sm border border-zinc-100 dark:border-zinc-800 p-8 space-y-8">
    {{-- 1. Header Section --}}
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" class="font-black uppercase tracking-tight text-zinc-800 dark:text-white">
                {{ $createOrUpdateMessage }} Territory
            </flux:heading>
            <flux:subheading>Define operational zones and regional jurisdictions</flux:subheading>
        </div>
        <flux:button href="{{ route('territories.index') }}" variant="ghost" icon="arrow-left" wire:navigate>
            Back to Index
        </flux:button>
    </div>

    <form wire:submit.prevent="createTerritory" class="space-y-8">
        {{-- 2. Territory Name Input --}}
        <div class="space-y-2">
            <flux:input label="Territory Name" placeholder="e.g. Mashonaland Cluster" wire:model.live.blur="territory"
                wire:change="checkTerritoryStatus" :invalid="$errors->has('territory')" />
            @if ($territoryExistanceMessage)
                <p class="text-[10px] text-amber-600 font-black uppercase tracking-widest animate-pulse">
                    {{ $territoryExistanceMessage }}
                </p>
            @endif
            <flux:error name="territory" />
        </div>

        @if ($showCountries)
            <flux:separator />

            {{-- 3. Country Selection Grid --}}
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <flux:label class="font-bold uppercase text-[10px] text-zinc-400 tracking-widest">
                        Step 1: Select Regional Scope
                    </flux:label>

                    @error('selectedCountry')
                        <span class="text-[10px] font-black text-red-500 uppercase tracking-widest">
                            Selection Required
                        </span>
                    @enderror
                </div>

                <div @class([
                    'grid grid-cols-2 md:grid-cols-4 gap-3 p-1 rounded-2xl transition-all',
                    'ring-2 ring-red-500/20 p-2 bg-red-50/30' => $errors->has(
                        'selectedCountry'),
                ])>
                    @foreach (App\Models\Country::orderBy('name')->get() as $country)
                        <label wire:key="country-{{ $country->id }}"
                            class="group flex items-center gap-3 p-4 border border-zinc-100 dark:border-zinc-800 rounded-2xl hover:bg-zinc-50 dark:hover:bg-zinc-800 cursor-pointer transition-all">
                            <input type="checkbox" wire:model.live="selectedCountry" value="{{ $country->name }}"
                                class="rounded text-lime-600 focus:ring-lime-500 border-zinc-300 dark:bg-zinc-800">
                            <span
                                class="text-xs font-bold text-zinc-700 dark:text-zinc-300 group-hover:text-lime-600 uppercase tracking-tighter transition-colors">
                                {{ $country->name }}
                            </span>
                        </label>
                    @endforeach
                </div>
                <flux:error name="selectedCountry" />
            </div>

            {{-- 4. Zimbabwe Specific Jurisdictions --}}
            @if ($showProvinces)
                <div class="p-8 bg-zinc-50 dark:bg-zinc-950 rounded-[2.5rem] border border-zinc-100 dark:border-zinc-800 space-y-8"
                    x-transition>
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-lime-600 rounded-lg">
                            <flux:icon.map-pin variant="mini" class="text-white" />
                        </div>
                        <flux:heading size="sm">Step 2: Define Zimbabwe Scope</flux:heading>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach ($zimbabweProvinces as $province)
                            <div class="bg-white dark:bg-zinc-900 p-6 rounded-[2rem] border border-zinc-200 dark:border-zinc-800 shadow-sm"
                                wire:key="prov-{{ $province['id'] }}">
                                <div
                                    class="mb-4 pb-2 border-b border-zinc-50 dark:border-zinc-800 flex items-center justify-between">
                                    {{-- Province Select All Toggle --}}
                                    <label class="flex items-center gap-2 cursor-pointer group">
                                        <input type="checkbox" wire:click="toggleProvince({{ $province['id'] }})"
                                            @php
$pCityIds = collect($province['zimbabwe_cities'])->pluck('id')->map(fn($id) => (string)$id);
                                                $selInP = collect($selectedCities)->intersect($pCityIds);
                                                $isFull = $selInP->count() === $pCityIds->count() && $pCityIds->count() > 0; @endphp
                                            {{ $isFull ? 'checked' : '' }}
                                            class="rounded border-zinc-300 text-lime-600 focus:ring-lime-500 shadow-sm">
                                        <span
                                            class="text-xs font-black text-lime-600 group-hover:text-lime-700 uppercase tracking-widest transition-colors">
                                            {{ $province['name'] }}
                                        </span>
                                    </label>

                                    @if ($selInP->count() > 0 && !$isFull)
                                        <span
                                            class="text-[9px] font-black text-amber-500 bg-amber-50 px-2 py-0.5 rounded-full uppercase tracking-tighter">
                                            Partial Selection
                                        </span>
                                    @endif
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    @foreach ($province['zimbabwe_cities'] as $city)
                                        <label class="flex items-center gap-2 group cursor-pointer"
                                            wire:key="city-{{ $city['id'] }}">
                                            <input type="checkbox" wire:model.live="selectedCities"
                                                value="{{ $city['id'] }}"
                                                class="rounded text-emerald-500 border-zinc-300 dark:border-zinc-700 shadow-sm focus:ring-emerald-500">
                                            <span
                                                class="text-[11px] font-medium text-zinc-500 group-hover:text-zinc-900 dark:group-hover:text-white transition-colors">
                                                {{ $city['name'] }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif

        {{-- 5. Submission Section --}}
        <div class="flex items-center gap-4 pt-6">
            <flux:button variant="primary" type="submit"
                class="px-12 py-3 !rounded-2xl shadow-xl shadow-lime-500/20 font-bold uppercase tracking-widest text-xs">
                {{ $createOrUpdateMessage }} Territory
            </flux:button>

            <x-action-message on="message" class="text-emerald-600 font-bold uppercase text-[10px] tracking-widest">
                {{ session('message') }}
            </x-action-message>
        </div>
    </form>
</div>
