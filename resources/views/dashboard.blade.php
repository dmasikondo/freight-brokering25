<x-layouts.app :title="__('Dashboard')">

    @if ($user->hasRole('carrier'))
        <livewire:carrier.dashboard :user="$user" />
    @endif
    @if($user->hasRole('shipper'))
        <livewire:shipper.dashboard :user="$user" />
    @else
        <livewire:admin.dashboard :user="$user" />
    @endif
</x-layouts.app>
