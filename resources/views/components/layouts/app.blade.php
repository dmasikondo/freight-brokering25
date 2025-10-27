@auth
    <x-layouts.app.sidebar :title="$title ?? null">
        <flux:main>
            {{ $slot }}
        </flux:main>
    </x-layouts.app.sidebar>
@endauth
@guest
    <x-layouts.app.horizontal-nav>
        <flux:main>
            {{ $slot }}
        </flux:main>
    </x-layouts.app.horizontal-nav>
@endguest
