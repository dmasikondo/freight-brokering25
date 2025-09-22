<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use App\Models\Freight;

new class extends Component {

    #[Computed]
    public function getFreights()
    {
       return Freight::orderBy('updated_at')->with(['goods', 'contacts', 'createdBy'])->get();      
    }   
    
    
}; ?>

<div class="rounded-lg mb-6 relative">
    @if(session()->has('message'))
    <div class="my-2">
        <flux:callout icon="check" color='green'>
            <flux:callout.heading>Freight Upload!</flux:callout.heading>
            <flux:callout.text color='green'>
            {{ session('message') }}     
            </flux:callout.text>
        </flux:callout>          
    </div>
    @endif
    @if ($this->getFreights->isEmpty())
        <flux:callout icon="face-frown">
            <flux:callout.heading>No Freight</flux:callout.heading>
            <flux:callout.text>
            No Freight created yet!       
            </flux:callout.text>
        </flux:callout>
    @else 
    <div class="flex flex-col lg:flex-row lg:flex-wrap lg:gap-4">
        @foreach ($this->getFreights as $freight)
        <div class="w-full lg:w-5/12 mx-2 border-b pb-4 mb-4 border border-gray-300 rounded-lg shadow-md last:border-0 relative p-8">       
            <div class="mr-2 absolute top-0 right-0">
                <flux:dropdown position="right" align="end">
                    <flux:button icon:trailing="ellipsis-horizontal"/>

                    <flux:navmenu>
                        <flux:navmenu.item href="{{ route('freights.edit',['freight'=>$freight->id]) }}" icon="pencil-square">Edit Freight</flux:navmenu.item>
                        <flux:navmenu.item  icon="trash" variant="danger"                        
                        wire:click="deleteFreight('{{ $freight->id }}')"
                        wire:confirm.prompt="Are you sure you want to delete the freight: {{ strtoupper($freight->name) }}? \n\nType REMOVE to confirm your  action|REMOVE">
                        Remove Freight
                        </flux:navmenu.item>
                    </flux:navmenu>
                </flux:dropdown>                  
            </div>
            <div class="">
                <flux:badge size='sm' :color="$freight->status->color()" >{{ $freight->status->label() }}</flux:badge>
                <h2>
                    <flux:heading size='xl'>
                        {{ implode(', ', $freight->goods->pluck('name')->toArray()) }} 
                    </flux:heading>
                </h2>

                <div class="flex flex-row-reverse justify-between space-x-2">
                    
                    @if($freight->is_hazardous)
                    <p title='Hazardous goods'>
                        <flux:icon.exclamation-triangle variant='mini' class='text-amber-400'/>
                    </p>
                    @endif
                    <p>
                        <flux:text>{{$freight->name}}</flux:text>
                    </p>                    
                </div>
                <flux:separator text="{{$freight->weight}}" />

                <div class="my-2 space-y-2 space-x-2">
                    <p class='flex space-x-2'> <flux:icon.clipboard-document-list/> {{ $freight->description }}</p>
                    <p class='flex space-x-2'>                                               
                        <div class="flex space-x-2">
                            <x-graphic name='location-marker' class='size-3.5 text-green-400'/> 
                            <flux:icon.arrow-uturn-right/> 
                            <span>{{$freight->pickup_address}}, </span>
                            <span>{{$freight->cityfrom}} </span>
                            <flux:separator vertical class=" text-amber-800" />
                            <span>{{$freight->countryfrom}}</span>
                        </div>

                    </p>

                    <p class='flex space-x-2'> 
                        <div class="flex space-x-2">
                            <x-graphic name='location-marker' class='size-3.5 text-orange-400'/>
                            <span>{{$freight->delivery_address}}, </span>
                            <span>{{$freight->cityto}} </span>
                            <flux:separator vertical class=" text-amber-800" />
                            <span>{{$freight->countryto}}</span>
                            <flux:icon.arrow-uturn-left/>
                            <span class="text-xs font-extralight">Destination</span>
                        </div>
                    </p>   
                    <p class='flex space-x-2'> 
                        <flux:icon.calendar-date-range class="text-teal-400"/>
                        <span>{{$freight->datefrom->isoFormat('MMM Do YYYY')}} </span>
                        <flux:icon.arrow-trending-down/>
                        <span>{{$freight->dateto->isoFormat('MMM Do YYYY')}} </span>
                        <span class="text-xs">{{$freight->dateto->diffForHumans()}} </span>
                    </p> 
                    
                    <div class="flex space-x-2">
                        <div>
                            <flux:avatar name='{{ $freight->createdBy->contact_person }}' color='auto'/>                            
                        </div>
                        <div>
                            <flux:text>{{ $freight->createdBy->contact_person }}</flux:text>
                            <flux:text size='sm' variant='subtle'>{{$freight->updated_at->diffForHumans()}}</flux:text>
                        </div>
                         
                    </div>


                </div>
            </div>

                        

        </div>    
        @endforeach
    
    @endif
</div>
