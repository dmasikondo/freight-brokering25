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
                $isWorksheet = $notification->type === 'App\Notifications\WorksheetSharedNotification';
                $isTenderOffer = $notification->type === 'App\Notifications\TenderOfferNotification';
                $data = $notification->data;
                $unread = $notification->unread();

                // ── Tender offer event → visual identity ──────────────────
                // Staff-facing events (bidder acted)
                $tenderEvent = $data['event'] ?? null;
                $isTenderStaff =
                    $isTenderOffer && in_array($tenderEvent, ['offer_submitted', 'offer_revised', 'offer_withdrawn']);
                $isTenderBidder =
                    $isTenderOffer && in_array($tenderEvent, ['offer_shortlisted', 'offer_rejected', 'offer_awarded']);

                [$tenderColor, $tenderIcon] = match ($tenderEvent) {
                    'offer_submitted' => ['lime', 'banknotes'],
                    'offer_revised' => ['lime', 'pencil-square'],
                    'offer_withdrawn' => ['zinc', 'x-circle'],
                    'offer_shortlisted' => ['blue', 'sparkles'],
                    'offer_rejected' => ['red', 'x-circle'],
                    'offer_awarded' => ['green', 'trophy'],
                    'award_revoked' => ['red', 'arrow-uturn-left'],
                    default => ['zinc', 'banknotes'],
                };
            @endphp

            <div @class([
                'relative flex items-center gap-4 p-4 rounded-xl border transition-all',
            
                // ── Tender offer — staff events ──
                'bg-lime-50/40 dark:bg-lime-900/10 border-lime-200 dark:border-lime-800 border-l-4 border-l-lime-500 shadow-sm' =>
                    $unread && $isTenderStaff,
            
                // ── Tender offer — bidder events ──
                'bg-blue-50/40 dark:bg-blue-900/10 border-blue-200 dark:border-blue-800 border-l-4 border-l-blue-500 shadow-sm' =>
                    $unread && $isTenderBidder && $tenderEvent === 'offer_shortlisted',
                'bg-red-50/40 dark:bg-red-900/10 border-red-200 dark:border-red-800 border-l-4 border-l-red-500 shadow-sm' =>
                    $unread && $isTenderBidder && $tenderEvent === 'offer_rejected',
                'bg-green-50/40 dark:bg-green-900/10 border-green-200 dark:border-green-800 border-l-4 border-l-green-500 shadow-sm' =>
                    $unread && $isTenderBidder && $tenderEvent === 'offer_awarded',
                'bg-red-50/40 dark:bg-red-900/10 border-red-200 dark:border-red-800 border-l-4 border-l-red-500 shadow-sm' =>
                    $unread && $isTenderOffer && $tenderEvent === 'award_revoked',
            
                // ── Existing types ──
                'bg-lime-50/40 dark:bg-lime-900/10 border-lime-200 dark:border-lime-800 border-l-4 border-l-lime-500 shadow-sm' =>
                    $unread && $isWorksheet,
                'bg-blue-50/40 dark:bg-blue-900/10 border-blue-200 dark:border-blue-800 border-l-4 border-l-blue-500 shadow-sm' =>
                    $unread && $isRegistration,
                'bg-emerald-50/40 dark:bg-emerald-900/10 border-emerald-200 dark:border-emerald-800 border-l-4 border-l-emerald-500 shadow-sm' =>
                    $unread && $isVerification,
            
                'bg-white dark:bg-zinc-900 border-zinc-200 dark:border-zinc-700 opacity-80' => $notification->read(),
            ])>

                {{-- ── Status icon ────────────────────────────────────────── --}}
                <div @class([
                    'flex items-center justify-center w-12 h-12 rounded-xl shrink-0',
                
                    // Tender offer icon backgrounds
                    'bg-lime-100 dark:bg-lime-900/40 text-lime-600' =>
                        $isTenderOffer && $tenderColor === 'lime',
                    'bg-blue-100 dark:bg-blue-900/40 text-blue-600' =>
                        $isTenderOffer && $tenderColor === 'blue',
                    'bg-red-100 dark:bg-red-900/40 text-red-600' =>
                        $isTenderOffer && $tenderColor === 'red',
                    'bg-green-100 dark:bg-green-900/40 text-green-600' =>
                        $isTenderOffer && $tenderColor === 'green',
                    'bg-zinc-100 dark:bg-zinc-800 text-zinc-500' =>
                        $isTenderOffer && $tenderColor === 'zinc',
                
                    // Existing type icon backgrounds
                    'bg-lime-100 text-lime-600' => $isWorksheet && !$isTenderOffer,
                    'bg-blue-100 text-blue-600' => $isRegistration,
                    'bg-emerald-100 text-emerald-600' => $isVerification,
                    'bg-zinc-100 text-zinc-500' => $notification->read() && !$isTenderOffer,
                ])>
                    @if ($isTenderOffer)
                        <flux:icon :name="$tenderIcon" variant="mini" class="w-6 h-6" />
                    @elseif ($isVerification)
                        <flux:icon.check-badge variant="mini" class="w-6 h-6" />
                    @elseif ($isWorksheet)
                        <flux:icon.users variant="mini" class="w-6 h-6" />
                    @else
                        <flux:icon.user-plus variant="mini" class="w-6 h-6" />
                    @endif
                </div>

                {{-- ── Content ─────────────────────────────────────────────── --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1">
                        <flux:text font="semibold" class="text-zinc-900 dark:text-white leading-none">
                            @if ($isTenderOffer)
                                {{ $data['title'] }}
                            @elseif ($isVerification)
                                {{ $data['title'] ?? __('Account Verified') }}
                            @elseif ($isWorksheet)
                                {{ $data['action'] === 'granted' ? __('Worksheet Shared') : __('Access Withdrawn') }}
                            @else
                                {{ __('New :role Registered', ['role' => ucfirst($data['client_role'] ?? 'Client')]) }}
                            @endif
                        </flux:text>
                        <flux:text size="xs" class="text-zinc-400 shrink-0 ml-4">
                            {{ $notification->created_at->diffForHumans() }}
                        </flux:text>
                    </div>

                    <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                        @if ($isTenderOffer)
                            {{ $data['body'] }}
                        @elseif ($isVerification)
                            {{ $data['message'] }}
                        @elseif ($isWorksheet)
                            {{ $data['message'] }}
                        @else
                            {{ __('Organization: :name has joined the network.', ['name' => $data['client_name'] ?? 'Unknown']) }}
                        @endif
                    </flux:text>
                </div>

                {{-- ── Actions ──────────────────────────────────────────────── --}}
                <div class="flex items-center gap-2 shrink-0">
                    @if ($unread)
                        <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST">
                            @csrf
                            <flux:button size="xs" type="submit" variant="ghost" icon="check" />
                        </form>
                    @endif

                    @php
                        $isWithdrawn = $isWorksheet && ($data['action'] ?? '') === 'withdrawn';

                        $viewUrl = match (true) {
                            $isTenderOffer || $isWorksheet || $isRegistration => route(
                                'notifications.readAndView',
                                $notification->id,
                            ),
                            default => $data['action_url'] ?? '#',
                        };
                    @endphp

                    @if (!$isWithdrawn)
                        <flux:button size="sm" variant="filled"
                            :color="$isTenderOffer ? $tenderColor : ($isWorksheet ? 'lime' : 'blue')"
                            :href="$viewUrl" wire:navigate>
                            {{ __('Open') }}
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
