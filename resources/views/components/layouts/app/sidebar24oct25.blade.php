<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
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

            <flux:navlist.group  variant="outline" icon="home" heading="SERVICES"  :current="request()->routeIs('consultancy') || request()->routeIs('lanes.index') || request()->routeIs('freights.index')" expandable>
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
            
            <flux:navlist.item icon="scale" :href="route('terms')" :current="request()->routeIs('terms')" wire:navigate>
                {{ __('Terms') }}
            </flux:navlist.item>
            <flux:navlist variant="outline">
                <flux:navlist.item icon="envelope" href="http://webmail.transpartnerlogistics.co.zw/" target="_blank">
                    {{ __('Webmail') }}
                </flux:navlist.item>  
            </flux:navlist>            
            

            <flux:spacer />
            
            <div class="px-2 py-4 border-t border-zinc-200 dark:border-zinc-700">
                <h3 class="text-xs font-bold uppercase mb-3 text-zinc-500 dark:text-zinc-500">{{ __('Get In Touch') }}</h3>
                
                <div class="space-y-1">
                    {{-- 1. Clickable Phone Number (tel:) --}}
                    <a href="tel:+263772930514" class="flex items-center p-2 -mx-1 text-zinc-600 dark:text-zinc-400 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-700 transition duration-150 group">
                        <flux:icon.phone class="w-4 h-4 mr-2 text-blue-500" />
                        <span class="text-sm">+263 772 930 514</span>
                    </a>

                  <a href="tel:+263718930514" class="flex items-center p-2 -mx-1 text-zinc-600 dark:text-zinc-400 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-700 transition duration-150 group">
                        <flux:icon.phone class="w-4 h-4 mr-2 text-blue-500" />
                        <span class="text-sm">+263 718 930 514</span>
                    </a>                    

                    {{-- 2. WhatsApp (wa.me for direct chat) --}}
                    <a href="https://wa.me/263772930514" class="flex items-center p-2 -mx-1 text-zinc-600 dark:text-zinc-400 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-700 transition duration-150 group" target="_blank">
                        <flux:icon.chat-bubble-bottom-center-text class="w-4 h-4 mr-2 text-green-500" /> 
                        <span class="text-sm">WhatsApp Chat</span>
                    </a>

                    {{-- 3. Email (mailto:) --}}
                    <a href="mailto:support@transpartner.co.zw" class="flex items-center p-2 -mx-1 text-zinc-600 dark:text-zinc-400 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-700 transition duration-150 group" target="_blank">
                        <flux:icon.envelope class="w-4 h-4 mr-2 text-red-500" />
                        <span class="text-sm">Email Support</span>
                    </a>
                </div>

                {{-- Social Media Icons Only --}}
                <div class="flex space-x-4 pt-3 mt-3">
                   
                </div>
            </div>
            
            <flux:spacer /> 
            {{-- END: Contact and Social Media Links --}}


            <!-- Desktop User Menu -->
            <flux:dropdown class="hidden lg:block" position="bottom" align="start">
                <flux:profile
                    :name="auth()->user()->contact_person"
                    :initials="auth()->user()->initials()"
                    icon:trailing="chevrons-up-down"
                />   


                <flux:menu class="w-[220px]">
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
                                    <span class="truncate font-semibold">{{ auth()->user()->contact_person }}</span>
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
                                    <span class="truncate font-semibold">{{ auth()->user()->contact_person }}</span>
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
