<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    @include('partials.nav-contacts')     
    <flux:header container class="bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
           
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
       
        <flux:navbar class="-mb-px max-lg:hidden">
            <flux:navbar.item  href="{{ route('home') }}" :current="request()->routeIs('home')"
                wire:navigate>
                        <span class="flex space-x-2 mr-2 items-center">
                            <x-app-logo-icon class="size-5" />
                            TransPartner Logistics
                        </span>                
        </flux:navbar.item>
            <flux:navbar.item icon="user-group" :href="route('about-us')" :current="request()->routeIs('about-us')"
                wire:navigate>About Us
            </flux:navbar.item>

             <flux:separator vertical variant="subtle" class="my-2"/>

            <flux:dropdown class="max-lg:hidden">
                <flux:navbar.item icon:trailing="chevron-down" :current="request()->routeIs('lanes.index') || request()->routeIs('freights.index') || request()->routeIs('consultancy')  ">Services</flux:navbar.item>
                <flux:navmenu>
                    <flux:navmenu.item icon="truck" :href="route('lanes.index')" :current="request()->routeIs('lanes.index')"  wire:navigate>
                 {{ __('Available Vehicles') }}
                    </flux:navmenu.item>
                    <flux:navmenu.item icon="cube" :href="route('freights.index')" :current="request()->routeIs('freights.index')" wire:navigate>
                        {{ __('Available Loads') }}
                    </flux:navmenu.item>
                    <flux:navmenu.item icon="arrow-trending-up" :href="route('consultancy')" :current="request()->routeIs('consultancy')" wire:navigate>
                         {{ __('Consultancy') }}
                    </flux:navmenu.item>
                </flux:navmenu>
            </flux:dropdown>
            <flux:navbar.item icon="question-mark-circle" :href="route('faq')" :current="request()->routeIs('faq')" wire:navigate>
                F. A. Q
            </flux:navbar.item>    
            <flux:navbar.item icon="scale" :href="route('terms')" :current="request()->routeIs('terms')"
                wire:navigate>
                {{ __('Terms') }}
            </flux:navbar.item>           
            <flux:navbar.item icon="envelope" href="http://webmail.transpartnerlogistics.co.zw/" target="_blank" wire:navigate>
                {{ __('Webmail') }}
            </flux:navbar.item>                     
        </flux:navbar>
        <flux:spacer />

            <flux:dropdown  position="top" align="start">
                <flux:navbar.item icon:trailing="chevron-down" :current="request()->routeIs('login') || request()->routeIs('register')">
                    <x-graphic name="user" class="size-6"/>
                </flux:navbar.item>
                <flux:navmenu>
                    <flux:navmenu.item icon="key" :href="route('login')" :current="request()->routeIs('login')"  wire:navigate>
                 {{ __('Login') }}
                    </flux:navmenu.item>
                    <flux:navmenu.item icon="lock-open" :href="route('register')" :current="request()->routeIs('register')" wire:navigate>
                 {{ __('Register') }}
                    </flux:navmenu.item>  
                </flux:navmenu>
            </flux:dropdown>

    </flux:header>

<flux:sidebar sticky collapsible="mobile" class="lg:hidden bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">
    <flux:sidebar.header>
        <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
    </flux:sidebar.header>
    <flux:sidebar.nav>
        <flux:sidebar.item href="{{ route('home') }}" :current="request()->routeIs('home')" wire:navigate>
            <span class="flex space-x-2 items-center">
                <x-app-logo-icon class="size-5" />
                TransPartner Logistics
            </span>
        </flux:sidebar.item>
        
        <flux:sidebar.item icon="user-group" :href="route('about-us')" :current="request()->routeIs('about-us')" wire:navigate>
            About Us
        </flux:sidebar.item>

        <flux:sidebar.item icon="question-mark-circle" :href="route('faq')" :current="request()->routeIs('faq')" wire:navigate>
            F. A. Q
        </flux:sidebar.item>

        <flux:sidebar.item icon="scale" :href="route('terms')" :current="request()->routeIs('terms')" wire:navigate>
            {{ __('Terms') }}
        </flux:sidebar.item>

        <flux:sidebar.item icon="envelope" href="http://webmail.transpartnerlogistics.co.zw/" target="_blank" wire:navigate>
            {{ __('Webmail') }}
        </flux:sidebar.item>

        <flux:sidebar.group expandable heading="Services" class="grid" :current="request()->routeIs('lanes.index') || request()->routeIs('freights.index') || request()->routeIs('consultancy')  ">
            <flux:sidebar.item icon="truck" :href="route('lanes.index')" :current="request()->routeIs('lanes.index')" wire:navigate>
                {{ __('Available Vehicles') }}
            </flux:sidebar.item>
            <flux:sidebar.item icon="cube" :href="route('freights.index')" :current="request()->routeIs('freights.index')" wire:navigate>
                {{ __('Available Loads') }}
            </flux:sidebar.item>
            <flux:sidebar.item icon="arrow-trending-up" :href="route('consultancy')" :current="request()->routeIs('consultancy')" wire:navigate>
                {{ __('Consultancy') }}
            </flux:sidebar.item>
        </flux:sidebar.group>
    </flux:sidebar.nav>
    <flux:sidebar.spacer />
    <flux:sidebar.nav>
        <div class="px-2 py-4 border-t border-zinc-200 dark:border-zinc-700">
            <h3 class="text-xs font-bold uppercase mb-3 text-zinc-500 dark:text-zinc-500">{{ __('Get In Touch') }}</h3>

            <div class="space-y-1">
                {{-- 1. Clickable Phone Number (tel:) --}}
                <a href="tel:+263772930514"
                    class="flex items-center p-2 -mx-1 text-zinc-600 dark:text-zinc-400 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-700 transition duration-150 group"
                    title="Mobile Phone">
                    <flux:icon.device-phone-mobile class="w-4 h-4 mr-2 text-blue-500" />
                    <span class="text-sm">+263 772 930 514</span>
                </a>

                <a href="tel:+263718930514"
                    class="flex items-center p-2 -mx-1 text-zinc-600 dark:text-zinc-400 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-700 transition duration-150 group"
                    title="Mobile Phone">
                    <flux:icon.device-phone-mobile class="w-4 h-4 mr-2 text-blue-500" />
                    <span class="text-sm">+263 718 930 514</span>
                </a>

                {{-- 2. WhatsApp (wa.me for direct chat) --}}
                <a href="https://wa.me/263772930514"
                    class="flex items-center p-2 -mx-1 text-zinc-600 dark:text-zinc-400 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-700 transition duration-150 group"
                    target="_blank" title="Whatsapp">
                    <x-graphic name="whatsapp" class="w-4 h-4 mr-2 text-green-500" />
                    <span class="text-sm">WhatsApp Chat</span>
                </a>

                {{-- 3. Email (mailto:) --}}
                <a href="mailto:support@transpartner.co.zw"
                    class="flex items-center p-2 -mx-1 text-zinc-600 dark:text-zinc-400 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-700 transition duration-150 group"
                    target="_blank" title="Email">
                    <flux:icon.envelope class="w-4 h-4 mr-2 text-red-500" />
                    <span class="text-sm">Email Support</span>
                </a>

                <a href="https://www.facebook.com/transpartnerlogisticsZim"
                    class="flex items-center p-2 -mx-1 text-zinc-600 dark:text-zinc-400 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-700 transition duration-150 group"
                    target="_blank" title="Facebook">
                    <x-graphic name="facebook" class="w-4 h-4 mr-2 text-blue-500" />
                    <span class="text-sm">Facebook</span>
                </a>

                <a href="https://x.com/TranspartnerLog"
                    class="flex items-center p-2 -mx-1 text-zinc-600 dark:text-zinc-400 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-700 transition duration-150 group"
                    target="_blank" title="Twitter now X">
                    <x-graphic name="twitter" class="w-4 h-4 mr-2 text-gray-500" />
                    <span class="text-sm">Twitter</span>
                </a>

                <a href="https://www.linkedin.com/company/transpartner-logistics-company/?trk=biz-companies-cym"
                    class="flex items-center p-2 -mx-1 text-zinc-600 dark:text-zinc-400 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-700 transition duration-150 group"
                    target="_blank" title="Linked In">
                    <x-graphic name="linkedin" class="w-4 h-4 mr-2 text-blue-500" />
                    <span class="text-sm">Linked In</span>
                </a>
            </div>

            {{-- Social Media Icons Only --}}
            <div class="flex space-x-4 pt-3 mt-3">

            </div>
        </div>        
    </flux:sidebar.nav>
</flux:sidebar>
 <flux:main container>
        <div class="flex max-md:flex-col items-start">
            <flux:separator class="md:hidden" />
            <div class="flex-1 max-md:pt-6 self-stretch">
              {{$slot}}
            </div>
            
        </div>
        @include('partials.footer')
    </flux:main>

    @fluxScripts
</body>

</html>
