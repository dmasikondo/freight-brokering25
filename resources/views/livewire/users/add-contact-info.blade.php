<?php

use Livewire\Volt\Component;
use App\Models\User;

new class extends Component {
    public $user;
    public string $country = '';
    public string $city = '';
    public string $address = '';
    public $zimbabweCities = [];

    protected function rules(): array
    {
        return [
            'country' => ['required', 'string', 'in:Zimbabwe,South Africa'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string'],
        ];
    }
    public function addLocation()
    {
        $validated = $this->validate();
        $this->user->buslocation()->create($validated);
        $this->dispatch('location-updated', $this->user);
        $this->dispatch('show-locationFlashMessage');
        session()->flash('message', 'Location successfully updated!');
        $this->reset();
        \Flux::modals()->close();
    }
    public function mount(User $user)
    {
        $this->user = $user;
        $this->zimbabweCities = \App\Models\ZimbabweCity::orderBy('name')->pluck('name', 'name')->toArray();
    }
}; ?>

<div x-data="{
    country: @entangle('country'),
    city: @entangle('city'),
    address: @entangle('address'),
}" x-cloak>
    <flux:modal name="create-location" class="">
    <h2 class="text-2xl font-bold mb-6" >Where Located?</h2>
        <form wire:submit="addLocation">
            <x-form.select @class(['border-red-500' => $errors->has('country'), 'mb-4']) placeholder="Country" wire:model.live="country"
                :options="['Zimbabwe' => 'Zimbabwe', 'South Africa' => 'South Africa']" />
            <x-form.input-error field="country" />

            <div x-show="country==='Zimbabwe'">
                <x-form.select @class(['border-red-500' => $errors->has('city'), 'mb-4']) placeholder="City" wire:model="city" :options="$zimbabweCities" />
                <x-form.input-error field="city" />
            </div>

            <div x-show="country==='South Africa'">
                <x-form.input placeholder="City" model="city" wire:model="city" @class(['border-red-500' => $errors->has('city'), 'mb-4']) />
                <x-form.input-error field="city" />
            </div>

            <x-form.input placeholder="Street Address" model="address" wire:model="address" @class(['border-red-500' => $errors->has('address')])
                required />
            <x-form.input-error field="address" />

            <flux:button type="submit" variant='primary' class="my-2">Add Location</flux:button>
        </form>
    </flux:modal>

</div>
