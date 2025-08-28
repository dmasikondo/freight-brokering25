<div>
    <div class="mb-1 w-full">
        <div class="mb-4">
            <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">All users</h1>
        </div>
        <div class="sm:flex">
            <div class="hidden sm:flex items-center sm:divide-x sm:divide-gray-100 mb-3 sm:mb-0">
                <form class="lg:pr-3" wire:submit.prevent>
                    <label for="users-search" class="sr-only">Search</label>
                    <div class="mt-1 relative lg:w-64 xl:w-96">
                        <input type="text" wire:model.live="search" id="users-search" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-cyan-600 focus:border-cyan-600 block w-full p-2.5" placeholder="Search for users">
                    </div>
                </form>
                <div class="flex space-x-1 pl-0 sm:pl-2 mt-3 sm:mt-0">
                    <a href="#" class="text-gray-500 hover:text-gray-900 cursor-pointer p-1 hover:bg-gray-100 rounded inline-flex justify-center">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"></path></svg>
                    </a>
                    <a href="#" class="text-gray-500 hover:text-gray-900 cursor-pointer p-1 hover:bg-gray-100 rounded inline-flex justify-center">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                    </a>
                    <a href="#" class="text-gray-500 hover:text-gray-900 cursor-pointer p-1 hover:bg-gray-100 rounded inline-flex justify-center">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                    </a>
                    <a href="#" class="text-gray-500 hover:text-gray-900 cursor-pointer p-1 hover:bg-gray-100 rounded inline-flex justify-center">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path></svg>
                    </a>
                </div>
            </div>
            <div class="flex items-center space-x-2 sm:space-x-3 ml-auto">
                <a href="{{ route('users.create') }}" class="cursor-hand">
                    <button type="button" data-modal-toggle="add-user-modal" class="w-1/2 text-white bg-cyan-600 hover:bg-cyan-700 focus:ring-4 focus:ring-cyan-200 font-medium inline-flex items-center justify-center rounded-lg text-sm px-3 py-2 text-center sm:w-auto">
                        <svg class="-ml-1 mr-2 h-6 w-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path></svg>
                        Add user
                    </button>
                </a>
                <a href="#" class="w-1/2 text-gray-900 bg-white border border-gray-300 hover:bg-gray-100 focus:ring-4 focus:ring-cyan-200 font-medium inline-flex items-center justify-center rounded-lg text-sm px-3 py-2 text-center sm:w-auto">
                    <svg class="-ml-1 mr-2 h-6 w-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd"></path></svg>
                    Export
                </a>
            </div>
        </div>
    </div>
    @foreach($this->users as $user)
    <div class="mt-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-3 border-t hover:bg-gray-200 w-full">
            <div class="flex items-center">
                {{-- Dynamically render icon based on user roles only --}}
                <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0
                @foreach($user->roles as $role)
                    @if($role->name == 'carrier')
                        bg-orange-500
                    @elseif($role->name == 'shipper')
                        bg-blue-500
                    @elseif($role->name == 'logistics_associate')
                        bg-purple-500
                    @else
                        bg-yellow-400
                    @endif
                @endforeach
                ">
                    {{-- Use x-graphic for Flux icons based on role name --}}
                    @foreach($user->roles as $role)
                        @php
                            $icon = match($role->name) {
                                'marketing logistics associate' => 'megaphone',
                                'procurement logistics associate' => 'clipboard-document-list',
                                'operations logistics associate' => 'cursor-arrow-ripple',
                                'admin' => 'cog-6-tooth',
                                'superadmin' => 'lock-closed',
                                'carrier' => 'truck',
                                'shipper' => 'cube',
                                default => 'user-circle' // Default icon
                            };
                        @endphp
                        <flux:icon. :name="$icon" class="size-6 text-white" />
                        @break
                    @endforeach
                </div>
                <div class="flex flex-col ml-2">
                    <div class="flex items-center gap-2">
                        <div class="text-sm font-bold leading-snug text-gray-900">
                            <a href="{{ route('users.show',['user'=>$user]) }}">
                                {!! $this->highlight($user->contact_person, $this->search) !!}
                            </a>
                        </div>
                        @if (auth()->user()->slug==$user->slug)
                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gradient-to-r from-pink-500 via-red-500 to-yellow-500 text-white">Me</span>
                        @endif
                        {{-- New: Combined role name and classification on a single line --}}
                        @foreach($user->roles as $role)
                            @if ($role->name)
                                <div class="flex items-center gap-1 text-sm text-gray-600">
                                    {{ ucwords(str_replace('_', ' ', $role->name)) }}
                                    @if ($role->pivot?->classification)
                                        <span class="mx-1">•</span>
                                        <x-graphic :name="match($role->pivot->classification) { 'real_owner' => 'shield-check', 'broker_agent' => 'exchange', default => '' }"
                                            class="size-4 {{ $role->pivot->classification === 'real_owner' ? 'text-yellow-400' : 'text-blue-400' }}" />
                                        <span>{{ $role->pivot->classification === 'real_owner' ? 'Real Owner' : 'Broker / Agent' }}</span>
                                    @endif
                                </div>
                                @break
                            @endif
                        @endforeach
                    </div>
                    <div class="text-xs leading-snug text-gray-600 mt-1">
                        &#64{!! $this->highlight($user->email, $this->search) !!}
                    </div>
                    {{-- New: The following content will now be placed below the email on smaller screens --}}
                    <div class="flex flex-col ml-0 sm:hidden mt-2">
                        @if ($user->createdBy)
                            <div class="flex items-center text-sm font-bold leading-snug text-gray-900">
                                <flux:icon.user-plus class="size-4 mr-1 text-gray-500" />
                                <span>Registered by {!! $this->highlight($user->createdBy?->contact_person, $this->search) !!}</span>
                            </div>
                        @else
                            <div class="flex items-center text-sm font-bold leading-snug text-gray-900">
                                <span>Registered</span>
                            </div>
                        @endif
                        <div class="text-xs leading-snug text-gray-600">
                            {{$user->created_at->diffForHumans()}}
                        </div>
                    </div>
                </div>
            </div>
            {{-- This content is hidden on smaller screens and only appears on sm: and above --}}
            <div class="hidden sm:flex flex-col ml-0 sm:ml-2 mt-2 sm:mt-0">
                @if ($user->createdBy)
                <div class="flex items-center text-sm font-bold leading-snug text-gray-900">
                    <x-graphic name="user-plus" class="size-4 mr-1 text-gray-500" />
                    <span>Registered by {!! $this->highlight($user->createdBy?->contact_person, $this->search) !!}</span>
                </div>
                @else
                <div class="flex items-center text-sm font-bold leading-snug text-gray-900">
                    <span>Registered</span>
                </div>
                @endif
                <div class="text-xs leading-snug text-gray-600">
                    {{$user->created_at->diffForHumans()}}
                </div>
            </div>
            {{-- Sticky action dots horizontal --}}
            <div class="sm:sticky sm:top-0 z-40 py-4 sm:pr-6 text-right w-full sm:w-auto">
                <x-dropdown>
                    <x-slot name="trigger">
                        <button class="flex items-center justify-center p-2 text-gray-400 hover:text-gray-500 hover:bg-gray-100 rounded-full transition-colors duration-150 ease-in-out">
                            <x-graphic name="dots-horizontal" class="size-6" />
                            <span class="sr-only">Open options</span>
                        </button>
                    </x-slot>

                    <x-dropdown-item wire:loading.class="animate-pulse" wire:click="userEdit('{{$user->slug}}')">
                        <x-graphic name="edit" class="size-4" />
                        <span>Edit</span>
                    </x-dropdown-item>

                    <x-dropdown-item wire:loading.class="animate-pulse" wire:click="userActivation('{{$user->slug}}')">
                        <x-graphic name="arrow-path" class="size-4" />
                        <span>
                            {{ $user->must_reset ? 'Activate' : 'Deactivate' }}
                        </span>
                    </x-dropdown-item>

                    <x-dropdown-item
                        wire:click="userDelete('{{$user->slug}}')"
                        wire:confirm.prompt="Are you sure you want to delete the user {{ strtoupper($user->contact_person) }}? You will not be able to retrieve the details back. \n\nType DELETE to confirm your deleting action|DELETE"
                    >
                        <x-graphic name="trash" class="size-4 text-red-600 group-hover:text-red-800" />
                        <span class="text-red-600 group-hover:text-red-800">Delete</span>
                    </x-dropdown-item>

                    <div wire:loading>
                        <x-dropdown-item class="animate-pulse">
                            <div class="flex items-center space-x-2">
                                <x-graphic name="arrow-path" class="size-4 animate-spin text-gray-400" />
                                <span class="text-gray-400">Loading...</span>
                            </div>
                        </x-dropdown-item>
                    </div>
                </x-dropdown>
            </div>
            {{-- ./ Sticky action dots horizontal --}}
        </div>
    </div>
    @endforeach
</div>
