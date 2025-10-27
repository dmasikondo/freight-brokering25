<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                <x-app-logo />
            </a>

            <flux:navlist variant="outline">
                <flux:navlist.group :heading="__('Platform')" class="grid">
                    <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>

            <flux:navlist variant="outline">
                <flux:navlist.item icon="user-group" :href="route('about-us')" :current="request()->routeIs('about-us')" wire:navigate>
                    {{ __('About Us') }}
                </flux:navlist.item>   
            </flux:navlist>            

            <flux:navlist.group  variant="outline" icon="home" heading="Services"  :current="request()->routeIs('consultancy') || request()->routeIs('lanes.index') || request()->routeIs('freights.index')" expandable>
                <flux:navlist.item icon="truck" :href="route('lanes.index')" :current="request()->routeIs('lanes.index')" wire:navigate>
                    {{ __('Available Vehicles') }}
                </flux:navlist.item>
                <flux:navlist.item icon="cube"  :href="route('freights.index')" :current="request()->routeIs('freights.index')" wire:navigate>
                    {{__('Available Loads') }}
                </flux:navlist.item>
                <flux:navlist.item icon="arrow-trending-up"  :href="route('consultancy')" :current="request()->routeIs('consultancy')" wire:navigate>
                    {{__('Consultancy') }}
                </flux:navlist.item>                
            </flux:navlist.group>
            
            <flux:navlist variant="outline">
                <flux:navlist.item icon="question-mark-circle" :href="route('faq')" :current="request()->routeIs('faq')" wire:navigate>
                    {{ __('F.A.Q') }}
                </flux:navlist.item>   
            </flux:navlist>
            
            

            <flux:spacer />

            <flux:navlist variant="outline">
                <flux:navlist.group :heading="'Vehicles'">
                    <flux:navlist.item  href="#" :current="request()->routeIs('dashboard')" wire:navigate>
                    <span class="text-sm mr-2"> &#128667; </span> 
                    <span class="">My Vehicles</span>
                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>

            <flux:navlist.item icon="scale" :href="route('terms')" :current="request()->routeIs('terms')" wire:navigate>
                {{ __('Terms') }}
            </flux:navlist.item>
            <flux:navlist variant="outline">
                <flux:navlist.item icon="envelope" href="http://webmail.transpartnerlogistics.co.zw/" target="_blank">
                    {{ __('Webmail') }}
                </flux:navlist.item>   
            </flux:navlist>             
            

            <!-- Desktop User Menu -->
            <flux:dropdown class="hidden lg:block" position="bottom" align="start">
            @auth
                <flux:profile
                    :name="auth()->user()->contact_person"
                    :initials="auth()->user()->initials()"
                    icon:trailing="chevrons-up-down"
                />                
            @endauth


                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                      @auth  {{ auth()->user()->initials() }} @endauth
                                    </span>
                                </span>
                                @auth
                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                                @endauth
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @fluxScripts
        
    </body>
</html>
