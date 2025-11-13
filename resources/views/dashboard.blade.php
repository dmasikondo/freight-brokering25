<x-layouts.app :title="__('Dashboard')">

    @if ($user->hasRole('carrier'))
        <livewire:carrier.dashboard :user="$user" />
    @endif
    @if($user->hasRole('shipper'))
        <livewire:shipper.dashboard :user="$user" />
    @endif
    @if($user->hasAnyRole(['admin','superadmin']))
        <livewire:admin.dashboard :user="$user" />
    @endif
    @if($user->hasRole('marketing logistics associate'))
        <livewire:markerting.dashboard :user="$user" />
    @endif
        <livewire:procurement.dashboard :user="$user" />
</x-layouts.app>
