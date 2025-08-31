<div>
    <h2>Assign Territories</h2>

    <div>
        <label for="country">Select Country:</label>
        <select wire:model="selectedCountry" id="country">
            <option value="">Choose a country</option>
            @foreach($countries as $country)
                <option value="{{ $country }}">{{ $country }}</option>
            @endforeach
        </select>
    </div>

    @if($selectedCountry)
        <div>
            <label for="province">Select Province:</label>
            <select wire:model="selectedProvince" id="province">
                <option value="">Choose a province</option>
                @foreach($provinces as $province)
                    <option value="{{ $province->id }}">{{ $province->name }}</option>
                @endforeach
            </select>
        </div>
    @endif

    @if($selectedProvince)
        <div>
            <label for="city">Select City:</label>
            <select wire:model="selectedCity" id="city">
                <option value="">Choose a city</option>
                @foreach($cities as $city)
                    <option value="{{ $city->id }}">{{ $city->name }}</option>
                @endforeach
            </select>
        </div>
    @endif

    <div>
        <button wire:click="assignTerritory">Assign Territory</button>
    </div>
</div>