<x-layouts.app>
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl" level="1">{{ __('Notifications') }}</flux:heading>
            <flux:subheading>{{ __('Manage alerts and registration activity in your territory.') }}
            </flux:subheading>
        </div>

        @if (auth()->user()->unreadNotifications->isNotEmpty())
            <form action="{{ route('notifications.markAllRead') }}" method="POST">
                @csrf
                <flux:button type="submit" variant="ghost" size="sm" icon="check-badge">
                    {{ __('Mark all as read') }}
                </flux:button>
            </form>
        @endif
    </div>

    <flux:separator class="mb-6" />

    <div class="grid grid-cols-1 gap-3">
        @forelse ($notifications as $notification)
            {{-- Standard Tailwind Div instead of flux:card --}}
            <div @class([
                'relative flex items-center gap-4 p-4 rounded-xl border transition-colors',
                'bg-blue-50/30 dark:bg-blue-900/10 border-blue-200 dark:border-blue-800 border-l-4 border-l-blue-500' => $notification->unread(),
                'bg-white dark:bg-zinc-900 border-zinc-200 dark:border-zinc-700 opacity-75' => $notification->read(),
            ])>
                {{-- Status Icon --}}
                <div @class([
                    'flex items-center justify-center w-10 h-10 rounded-full shrink-0',
                    'bg-blue-100 text-blue-600 dark:bg-blue-900/50 dark:text-blue-400' => $notification->unread(),
                    'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400' => $notification->read(),
                ])>
                    <flux:icon.bell variant="mini" class="w-5 h-5" />
                </div>

                {{-- Content --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <flux:text font="semibold" @class([
                            'text-blue-900 dark:text-blue-100' => $notification->unread(),
                        ])>
                            {{ $notification->data['message'] ?? __('New Activity') }}
                        </flux:text>
                        <flux:text size="xs" class="text-zinc-500">
                            {{ $notification->created_at->diffForHumans() }}
                        </flux:text>
                    </div>

                    <flux:text size="sm" class="truncate text-zinc-600 dark:text-zinc-400">
                        {{ __('A new :role, :name, has registered.', [
                            'role' => $notification->data['client_role'] ?? 'client',
                            'name' => $notification->data['client_name'] ?? 'User',
                        ]) }}
                    </flux:text>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-2">
                    @if ($notification->unread())
                        <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST">
                            @csrf
                            <flux:button size="xs" type="submit" variant="ghost" title="Mark as read"
                                icon="check" />
                        </form>
                    @endif

                    @if (isset($notification->data['client_id']))
                        <flux:button size="sm" variant="primary"
                            :href="route('notifications.readAndView', $notification->id)" wire:navigate>
                            {{ __('View') }}
                        </flux:button>
                    @endif
                </div>
            </div>
        @empty
            <div
                class="flex flex-col items-center justify-center py-20 bg-zinc-50 dark:bg-zinc-900/50 rounded-xl border border-dashed border-zinc-300 dark:border-zinc-700">
                <flux:icon.bell-slash class="w-12 h-12 text-zinc-300 dark:text-zinc-600 mb-4" />
                <flux:heading size="lg">{{ __('All caught up!') }}</flux:heading>
                <flux:subheading>{{ __('You have no new notifications.') }}</flux:subheading>
            </div>
        @endforelse
    </div>

    @if ($notifications->hasPages())
        <div class="mt-8">
            {{ $notifications->links() }}
        </div>
    @endif
</x-layouts.app>
