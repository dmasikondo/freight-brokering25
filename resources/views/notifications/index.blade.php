<x-layouts.app>
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl" level="1">{{ __('Notifications') }}</flux:heading>
            <flux:subheading>{{ __('Manage alerts, collaboration, and registration activity.') }}</flux:subheading>
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
            @php
                $isVerification = $notification->type === 'App\Notifications\AccountVerifiedNotification';
                $isRegistration = $notification->type === 'App\Notifications\NewClientRegistered';
                // NEW: Identify Worksheet Sharing
                $isWorksheet = $notification->type === 'App\Notifications\WorksheetSharedNotification';
                $data = $notification->data;
            @endphp

            <div @class([
                'relative flex items-center gap-4 p-4 rounded-xl border transition-all',
                // Lime theme for Worksheet Sharing
                'bg-lime-50/40 dark:bg-lime-900/10 border-lime-200 dark:border-lime-800 border-l-4 border-l-lime-500 shadow-sm' => $notification->unread() && $isWorksheet,
                'bg-blue-50/40 dark:bg-blue-900/10 border-blue-200 dark:border-blue-800 border-l-4 border-l-blue-500 shadow-sm' => $notification->unread() && $isRegistration,
                'bg-emerald-50/40 dark:bg-emerald-900/10 border-emerald-200 dark:border-emerald-800 border-l-4 border-l-emerald-500 shadow-sm' => $notification->unread() && $isVerification,
                'bg-white dark:bg-zinc-900 border-zinc-200 dark:border-zinc-700 opacity-80' => $notification->read(),
            ])>
                
                {{-- Status Icon --}}
                <div @class([
                    'flex items-center justify-center w-12 h-12 rounded-xl shrink-0',
                    'bg-lime-100 text-lime-600' => $isWorksheet,
                    'bg-blue-100 text-blue-600' => $isRegistration,
                    'bg-emerald-100 text-emerald-600' => $isVerification,
                    'bg-zinc-100 text-zinc-500' => $notification->read(),
                ])>
                    @if($isVerification)
                        <flux:icon.check-badge variant="mini" class="w-6 h-6" />
                    @elseif($isWorksheet)
                        <flux:icon.users variant="mini" class="w-6 h-6" />
                    @else
                        <flux:icon.user-plus variant="mini" class="w-6 h-6" />
                    @endif
                </div>

                {{-- Content --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1">
                        <flux:text font="semibold" class="text-zinc-900 dark:text-white leading-none">
                            @if($isVerification)
                                {{ $data['title'] ?? __('Account Verified') }}
                            @elseif($isWorksheet)
                                {{ $data['action'] === 'granted' ? __('Worksheet Shared') : __('Access Withdrawn') }}
                            @else
                                {{ __('New :role Registered', ['role' => ucfirst($data['client_role'] ?? 'Client')]) }}
                            @endif
                        </flux:text>
                        <flux:text size="xs" class="text-zinc-400">
                            {{ $notification->created_at->diffForHumans() }}
                        </flux:text>
                    </div>

                    <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                        @if($isVerification)
                            {{ $data['message'] }}
                        @elseif($isWorksheet)
                            {{ $data['message'] }}
                        @else
                            {{ __('Organization: :name has joined the network.', ['name' => $data['client_name'] ?? 'Unknown']) }}
                        @endif
                    </flux:text>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-2">
                    @if ($notification->unread())
                        <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST">
                            @csrf
                            <flux:button size="xs" type="submit" variant="ghost" icon="check" />
                        </form>
                    @endif

                    @php
                        // Determine the redirect URL
                        $viewUrl = match(true) {
                            $isWorksheet => $data['link'] ?? '#',
                            $isRegistration => route('notifications.readAndView', $notification->id),
                            default => ($data['action_url'] ?? '#'),
                        };

                        $isWithdrawn = $isWorksheet && ($data['action'] ?? '') === 'withdrawn';
                    @endphp

                    {{-- Only show 'Open' button if access wasn't withdrawn --}}
                    @if(!$isWithdrawn)
                        <flux:button 
                            size="sm" 
                            variant="filled" 
                            :color="$isWorksheet ? 'lime' : 'blue'" 
                            :href="$viewUrl" 
                            wire:navigate
                        >
                            {{ __('Open') }}
                        </flux:button>
                    @endif
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center py-20 bg-zinc-50 dark:bg-zinc-900/50 rounded-xl border border-dashed border-zinc-300 dark:border-zinc-700">
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