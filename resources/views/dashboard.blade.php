<x-layouts.app :title="__('Dashboard')">
    <div class="p-6 space-y-6">
        <!-- Header Section -->
        <livewire:carrier.profile-completion-check :user="$user" />

        <livewire:users.contact-info :user='$user' />

        <!-- Form Completion Status -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <livewire:carrier.director.status-info :user="$user" />

            <livewire:carrier.fleet.status-info :user="$user" />

            <livewire:carrier.traderef.status-info :user="$user" />
        </div>

        <livewire:carrier.document-upload :user="$user" />

        <livewire:carrier.recent-file-uploads :user="$user" />
    </div>

</x-layouts.app>
