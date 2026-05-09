You're right, I missed it. Here is the complete controller with `mount()` clearly present:

```php
<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use App\Models\TenderOffer;
use App\Models\Freight;
use App\Models\Lane;
use App\Enums\TenderOfferStatus;
use App\Enums\FreightStatus;
use App\ValueObjects\TenderConfig;
use Illuminate\Support\Facades\DB;

new class extends Component {

    public int    $tenderableId;
    public string $tenderableType;

    // Form fields
    public string $amount            = '';
    public string $proposedPickup    = '';
    public string $proposedDelivery  = '';
    public string $notes             = '';

    // UI state
    public bool   $showForm          = false;
    public string $rejectionReason   = '';
    public ?int   $rejectingOfferId  = null;
    public ?int   $revising          = null;

    // ---------------------------------------------------------------
    // Mount — NEVER hydrate Eloquent here, use raw DB only
    // Eloquent hydration triggers enum casts which Livewire cannot
    // serialize in its initial snapshot
    // ---------------------------------------------------------------
    public function mount(int $tenderableId, string $tenderableType): void
    {
        $this->tenderableId   = $tenderableId;
        $this->tenderableType = $tenderableType;
        [$table, $column] = match ($tenderableType) {
            Freight::class => ['freights', 'carriage_rate'],
            Lane::class    => ['lanes',    'rate'],
            default        => throw new \InvalidArgumentException(
                'Unsupported tenderable type: ' . $tenderableType
            ),
        };

        $value = DB::table($table)
            ->where('id', $tenderableId)
            ->value($column);

        $this->amount = (string) ((float) ($value ?? 0));
    }

    // ---------------------------------------------------------------
    // Private config helper
    // ---------------------------------------------------------------

    private function getTenderConfig(): TenderConfig
    {
        return match ($this->tenderableType) {
            Freight::class => new TenderConfig(
                bidderRole:         'carrier',
                rankOrder:          'asc',
                floorField:         'carriage_rate',
                requiresPickupDate: true,
                awardPermission:    'award',
            ),
            Lane::class => new TenderConfig(
                bidderRole:         'shipper',
                rankOrder:          'desc',
                floorField:         'rate',
                requiresPickupDate: false,
                awardPermission:    'award',
            ),
            default => throw new \InvalidArgumentException(
                'Unsupported tenderable type: ' . $this->tenderableType
            ),
        };
    }

    // ---------------------------------------------------------------
    // Computed properties — persist:false ensures Livewire never
    // tries to serialize these between requests
    // ---------------------------------------------------------------

    #[Computed(persist: false)]
    public function tenderable()
    {
        return $this->tenderableType::find($this->tenderableId)
            ->load(['tenderOffers.carrier', 'awardedOffer.carrier']);
    }

    #[Computed(persist: false)]
    public function config(): TenderConfig
    {
        return $this->getTenderConfig();
    }

    #[Computed(persist: false)]
    public function floorAmount(): float
    {
        [$table, $column] = match ($this->tenderableType) {
            Freight::class => ['freights', 'carriage_rate'],
            Lane::class    => ['lanes',    'rate'],
            default        => ['freights', 'carriage_rate'],
        };

        return (float) (DB::table($table)
            ->where('id', $this->tenderableId)
            ->value($column) ?? 0);
    }

    #[Computed(persist: false)]
    public function isOpen(): bool
    {
        $status = DB::table(match ($this->tenderableType) {
            Freight::class => 'freights',
            Lane::class    => 'lanes',
            default        => 'freights',
        })
        ->where('id', $this->tenderableId)
        ->value('status');

        return $status === 'published';
    }

    #[Computed(persist: false)]
    public function canBid(): bool
    {
        if (!auth()->check()) return false;
        if (!$this->isOpen)  return false;

        $user = auth()->user();

        $requiredRole = match ($this->tenderableType) {
            Freight::class => 'carrier',
            Lane::class    => 'shipper',
            default        => null,
        };

        if (!$requiredRole || !$user->hasRole($requiredRole)) {
            return false;
        }

        $row = DB::table(match ($this->tenderableType) {
            Freight::class => 'freights',
            Lane::class    => 'lanes',
            default        => 'freights',
        })
        ->where('id', $this->tenderableId)
        ->first(['creator_id', 'shipper_id']);

        if ($row && isset($row->creator_id) && $row->creator_id === $user->id) return false;
        if ($row && isset($row->shipper_id) && $row->shipper_id === $user->id) return false;

        return !TenderOffer::query()
            ->where('tenderable_type', $this->tenderableType)
            ->where('tenderable_id',   $this->tenderableId)
            ->where('carrier_id',      $user->id)
            ->whereIn('status', [
                TenderOfferStatus::PENDING,
                TenderOfferStatus::SHORTLISTED,
            ])
            ->exists();
    }

    #[Computed(persist: false)]
    public function canViewLeaderboard(): bool
    {
        if (!auth()->check()) return false;

        $user = auth()->user();

        if ($user->hasAnyRole([
            'admin',
            'superadmin',
            'logistics operations executive',
            'marketing logistics associate',
            'operations logistics associate',
        ])) {
            return true;
        }

        $row = DB::table(match ($this->tenderableType) {
            Freight::class => 'freights',
            Lane::class    => 'lanes',
            default        => 'freights',
        })
        ->where('id', $this->tenderableId)
        ->first(['creator_id', 'shipper_id', 'carrier_id']);

        if ($row) {
            if (isset($row->creator_id) && $row->creator_id === $user->id) return true;
            if (isset($row->shipper_id) && $row->shipper_id === $user->id) return true;
            if (isset($row->carrier_id) && $row->carrier_id === $user->id) return true;
        }

        return TenderOffer::query()
            ->where('tenderable_type', $this->tenderableType)
            ->where('tenderable_id',   $this->tenderableId)
            ->where('carrier_id',      $user->id)
            ->exists();
    }

    #[Computed(persist: false)]
    public function activeOffers()
    {
        return TenderOffer::query()
            ->with('carrier')
            ->where('tenderable_type', $this->tenderableType)
            ->where('tenderable_id',   $this->tenderableId)
            ->whereIn('status', [
                TenderOfferStatus::PENDING,
                TenderOfferStatus::SHORTLISTED,
            ])
            ->orderBy('amount', $this->getTenderConfig()->rankOrder)
            ->get()
            ->map(function ($offer, $index) {
                $offer->ranked_position = $index + 1;
                return $offer;
            });
    }

    #[Computed(persist: false)]
    public function closedOffers()
    {
        return TenderOffer::query()
            ->with('carrier', 'awardedBy')
            ->where('tenderable_type', $this->tenderableType)
            ->where('tenderable_id',   $this->tenderableId)
            ->whereIn('status', [
                TenderOfferStatus::AWARDED,
                TenderOfferStatus::REJECTED,
                TenderOfferStatus::WITHDRAWN,
                TenderOfferStatus::EXPIRED,
            ])
            ->latest('updated_at')
            ->get();
    }

    #[Computed(persist: false)]
    public function awardedOffer(): ?TenderOffer
    {
        return TenderOffer::query()
            ->with('carrier', 'awardedBy')
            ->where('tenderable_type', $this->tenderableType)
            ->where('tenderable_id',   $this->tenderableId)
            ->where('status', TenderOfferStatus::AWARDED)
            ->first();
    }

    #[Computed(persist: false)]
    public function myOffer(): ?TenderOffer
    {
        if (!auth()->check()) return null;

        return TenderOffer::query()
            ->where('tenderable_type', $this->tenderableType)
            ->where('tenderable_id',   $this->tenderableId)
            ->where('carrier_id',      auth()->id())
            ->whereIn('status', [
                TenderOfferStatus::PENDING,
                TenderOfferStatus::SHORTLISTED,
            ])
            ->first();
    }

    // ---------------------------------------------------------------
    // Actions
    // ---------------------------------------------------------------

    public function submitOffer(): void
    {
        if (!$this->canBid) {
            $this->addError('amount', 'You are not eligible to submit an offer.');
            return;
        }

        $config = $this->getTenderConfig();
        $floor  = $this->floorAmount;

        $rules = [
            'amount' => ['required', 'numeric', 'min:' . $floor],
            'notes'  => ['nullable', 'string', 'max:500'],
        ];

        if ($config->requiresPickupDate) {
            $rules['proposedPickup']   = ['required', 'date', 'after_or_equal:today'];
            $rules['proposedDelivery'] = ['nullable', 'date', 'after_or_equal:proposedPickup'];
        }

        $this->validate($rules, [
            'amount.min' => "Your offer must be at least US\$$floor.",
        ]);

        TenderOffer::create([
            'tenderable_type'        => $this->tenderableType,
            'tenderable_id'          => $this->tenderableId,
            'carrier_id'             => auth()->id(),
            'amount'                 => $this->amount,
            'proposed_pickup_date'   => $this->proposedPickup   ?: null,
            'proposed_delivery_date' => $this->proposedDelivery ?: null,
            'notes'                  => $this->notes,
            'status'                 => TenderOfferStatus::PENDING,
        ]);

        $this->reset(['proposedPickup', 'proposedDelivery', 'notes', 'showForm']);
        $this->amount = (string) $this->floorAmount;
        session()->flash('offer_success', 'Your offer has been submitted successfully.');
    }

    public function startRevision(int $offerId): void
    {
        $offer = TenderOffer::findOrFail($offerId);
        $this->authorize('update', $offer);

        $this->revising         = $offerId;
        $this->amount           = (string) $offer->amount;
        $this->proposedPickup   = $offer->proposed_pickup_date?->format('Y-m-d') ?? '';
        $this->proposedDelivery = $offer->proposed_delivery_date?->format('Y-m-d') ?? '';
        $this->notes            = $offer->notes ?? '';
    }

    public function saveRevision(): void
    {
        $offer = TenderOffer::findOrFail($this->revising);
        $this->authorize('update', $offer);

        $this->validate([
            'amount' => ['required', 'numeric', 'max:' . $offer->amount],
        ], [
            'amount.max' => 'Revisions must be lower than your current offer of US$'
                . number_format((float) $offer->amount, 2) . '.',
        ]);

        $offer->update([
            'amount'                 => $this->amount,
            'proposed_pickup_date'   => $this->proposedPickup   ?: null,
            'proposed_delivery_date' => $this->proposedDelivery ?: null,
            'notes'                  => $this->notes,
        ]);

        $this->reset(['revising', 'proposedPickup', 'proposedDelivery', 'notes']);
        $this->amount = (string) $this->floorAmount;
        session()->flash('offer_success', 'Your offer has been revised successfully.');
    }

    public function withdrawOffer(int $offerId): void
    {
        $offer = TenderOffer::findOrFail($offerId);
        $this->authorize('withdraw', $offer);
        $offer->update(['status' => TenderOfferStatus::WITHDRAWN]);
        session()->flash('offer_success', 'Your offer has been withdrawn.');
    }

    public function shortlistOffer(int $offerId): void
    {
        $offer = TenderOffer::findOrFail($offerId);
        $this->authorize('manage', $offer);
        $offer->update([
            'status' => $offer->status === TenderOfferStatus::SHORTLISTED
                ? TenderOfferStatus::PENDING
                : TenderOfferStatus::SHORTLISTED,
        ]);
    }

    public function confirmReject(int $offerId): void
    {
        $this->rejectingOfferId = $offerId;
        $this->rejectionReason  = '';
    }

    public function rejectOffer(): void
    {
        if (!$this->rejectingOfferId) return;

        $offer = TenderOffer::findOrFail($this->rejectingOfferId);
        $this->authorize('manage', $offer);

        $offer->update([
            'status'           => TenderOfferStatus::REJECTED,
            'rejection_reason' => $this->rejectionReason ?: null,
        ]);

        $this->reset(['rejectingOfferId', 'rejectionReason']);
    }

    public function awardOffer(int $offerId): void
    {
        $offer = TenderOffer::findOrFail($offerId);
        $this->authorize('award', $offer);

        TenderOffer::query()
            ->where('tenderable_type', $this->tenderableType)
            ->where('tenderable_id',   $this->tenderableId)
            ->whereIn('status', [
                TenderOfferStatus::PENDING->value,
                TenderOfferStatus::SHORTLISTED->value,
            ])
            ->where('id', '!=', $offerId)
            ->update(['status' => TenderOfferStatus::REJECTED->value]);

        $offer->update([
            'status'     => TenderOfferStatus::AWARDED,
            'awarded_at' => now(),
            'awarded_by' => auth()->id(),
        ]);

        DB::table(match ($this->tenderableType) {
            Freight::class => 'freights',
            Lane::class    => 'lanes',
            default        => 'freights',
        })
        ->where('id', $this->tenderableId)
        ->update(['status' => 'unpublished']);

        session()->flash('offer_success', 'Offer awarded. The listing has been closed.');
    }

    public function revokeAward(int $offerId): void
    {
        $offer = TenderOffer::findOrFail($offerId);
        $this->authorize('revoke', $offer);

        $offer->update([
            'status'     => TenderOfferStatus::PENDING,
            'awarded_at' => null,
            'awarded_by' => null,
        ]);

        DB::table(match ($this->tenderableType) {
            Freight::class => 'freights',
            Lane::class    => 'lanes',
            default        => 'freights',
        })
        ->where('id', $this->tenderableId)
        ->update(['status' => 'published']);

        session()->flash('offer_success', 'Award revoked. Listing is now open again.');
    }
};?>



 {{-- <div class="space-y-6 mt-8">

    {{-- Flash --}}
    @if(session('offer_success'))
        <div class="p-4 text-sm text-emerald-800 rounded-2xl bg-emerald-50 border border-emerald-100">
            {{ session('offer_success') }}
        </div>
    @endif

    <div class="flex items-center gap-3">
        <flux:icon name="banknotes" class="size-5 text-zinc-400" />
        <flux:heading size="lg">Tender Offers</flux:heading>
        <flux:badge size="sm" color="zinc">{{ $this->activeOffers->count() }} active</flux:badge>
    </div>

    {{-- NOT OPEN NOTICE --}}
    @if(!$this->isOpen)
        <div class="p-4 bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700 rounded-xl text-center">
            <flux:icon name="lock-closed" class="size-5 text-zinc-400 mx-auto mb-2" />
            <flux:text size="sm" class="text-zinc-500">This tender is not currently open for offers.</flux:text>
        </div>
    @endif

    {{-- SUBMIT FORM --}}
    @auth
        @if($this->isOpen && $this->canBid && !$this->myOffer)
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6">
                <flux:heading size="md" class="mb-1">Submit Your Offer</flux:heading>
                <flux:text size="sm" class="mb-6 text-zinc-500">
                    {{-- Minimum offer: <strong>US${{ number_format($this->floorAmount, 2) }}</strong> --}}
                </flux:text>

                <div class="space-y-4">
                    <flux:input
                        type="number"
                        step="0.01"
                        label="Your Offer (US$)"
                        wire:model="amount"
                        :min="$this->floorAmount"
                        kbd="US$" />
                    <flux:error name="amount" />

                    @if($this->config->requiresPickupDate)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <flux:input type="date" label="Proposed Pickup Date" wire:model="proposedPickup" />
                                <flux:error name="proposedPickup" />
                            </div>
                            <div>
                                <flux:input type="date" label="Proposed Delivery Date (optional)" wire:model="proposedDelivery" />
                            </div>
                        </div>
                    @endif

                    <flux:textarea label="Notes (optional)" wire:model="notes" rows="2"
                        placeholder="Any relevant details about your offer..." />

                    <flux:button wire:click="submitOffer" variant="primary" color="lime" icon="paper-airplane">
                        Submit Offer
                    </flux:button>
                </div>
            </div>
        @endif

        {{-- MY ACTIVE OFFER --}}
        @if($this->myOffer)
            <div class="bg-lime-50 dark:bg-lime-900/20 border border-lime-300 dark:border-lime-700 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <flux:icon name="star" variant="mini" class="size-4 text-lime-600" />
                        <flux:heading size="md">My Current Offer</flux:heading>
                    </div>
                    <flux:badge color="lime" size="sm">
                        Rank #{{ $this->myOffer->ranked_position ?? '—' }}
                    </flux:badge>
                </div>

                @if($this->revising === $this->myOffer->id)
                    {{-- Revision Form --}}
                    <div class="space-y-4">
                        {{-- <flux:input type="number" step="0.01"
                            label="Revised Amount — must be lower than current US${{ number_format((float) $this->myOffer->amount, 2) }}"
                            wire:model="amount"
                            :max="$this->myOffer->amount"
                            kbd="US$" />
                        <flux:error name="amount" /> --}}

                        @if($this->config->requiresPickupDate)
                            <div class="grid grid-cols-2 gap-4">
                                <flux:input type="date" label="Proposed Pickup" wire:model="proposedPickup" />
                                <flux:input type="date" label="Proposed Delivery" wire:model="proposedDelivery" />
                            </div>
                        @endif

                        <flux:textarea label="Notes" wire:model="notes" rows="2" />

                        <div class="flex gap-2">
                            <flux:button wire:click="saveRevision" variant="primary" color="lime" icon="check">
                                Save Revision
                            </flux:button>
                            <flux:button wire:click="$set('revising', null)" variant="ghost">
                                Cancel
                            </flux:button>
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm mb-4">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-zinc-400 block">Offer</span>
                            <span class="font-bold text-lg text-zinc-900 dark:text-white">
                                US${{ number_format((float) $this->myOffer->amount, 2) }}
                            </span>
                        </div>
                        @if($this->myOffer->proposed_pickup_date)
                            <div>
                                <span class="text-[10px] uppercase font-bold text-zinc-400 block">Pickup</span>
                                <span class="font-medium">
                                    {{ $this->myOffer->proposed_pickup_date->format('d M Y') }}
                                </span>
                            </div>
                        @endif
                        @if($this->myOffer->proposed_delivery_date)
                            <div>
                                <span class="text-[10px] uppercase font-bold text-zinc-400 block">Delivery</span>
                                <span class="font-medium">
                                    {{ $this->myOffer->proposed_delivery_date->format('d M Y') }}
                                </span>
                            </div>
                        @endif
                        <div>
                            <span class="text-[10px] uppercase font-bold text-zinc-400 block">Submitted</span>
                            <span class="font-medium">{{ $this->myOffer->created_at->diffForHumans() }}</span>
                        </div>
                    </div>

                    @if($this->myOffer->notes)
                        <p class="text-xs italic text-zinc-500 mb-4">"{{ $this->myOffer->notes }}"</p>
                    @endif

                    <div class="flex gap-2">
                        @if($this->isOpen)
                            <flux:button
                                wire:click="startRevision({{ $this->myOffer->id }})"
                                variant="ghost" size="sm" icon="pencil-square">
                                Revise Offer
                            </flux:button>
                            <flux:button
                                wire:click="withdrawOffer({{ $this->myOffer->id }})"
                                wire:confirm="Withdraw your offer? This cannot be undone."
                                variant="ghost" size="sm" icon="x-mark" color="red">
                                Withdraw
                            </flux:button>
                        @endif
                    </div>
                @endif
            </div>
        @endif
    @endauth

    {{-- REJECTION CONFIRMATION --}}
    @if($rejectingOfferId)
        <div class="p-5 bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900 rounded-2xl space-y-3">
            <p class="font-bold text-red-800 dark:text-red-300 text-sm">Provide a reason for rejection (optional)</p>
            <flux:textarea wire:model="rejectionReason" rows="2" placeholder="Reason for rejection..." />
            <div class="flex gap-2 justify-end">
                <flux:button variant="ghost" wire:click="$set('rejectingOfferId', null)">Cancel</flux:button>
                <flux:button variant="primary" color="red" wire:click="rejectOffer" icon="x-mark">
                    Confirm Rejection
                </flux:button>
            </div>
        </div>
    @endif

    {{-- LEADERBOARD --}}
    @if($this->activeOffers->count())
        @if($this->canViewLeaderboard)
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-zinc-100 dark:border-zinc-800 flex items-center justify-between">
                    <flux:heading size="md">Live Leaderboard</flux:heading>
                    <flux:text size="xs" class="text-zinc-400 uppercase tracking-widest">
                        {{ $this->config->rankOrder === 'asc' ? 'Lowest offer wins' : 'Highest offer wins' }}
                    </flux:text>
                </div>

                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach($this->activeOffers as $offer)
                        @php
                            $isTopRank = $offer->ranked_position === 1;
                            $isMe      = auth()->id() === $offer->carrier_id;
                        @endphp
                        <div class="px-6 py-4 flex items-center gap-4
                            {{ $isTopRank ? 'bg-lime-50 dark:bg-lime-900/10' : '' }}">

                            {{-- Rank --}}
                            <div class="w-8 text-center shrink-0">
                                @if($isTopRank)
                                    <flux:icon name="trophy" variant="mini" class="size-5 text-amber-500 mx-auto" />
                                @else
                                    <span class="text-sm font-bold text-zinc-400">#{{ $offer->ranked_position }}</span>
                                @endif
                            </div>

                            {{-- Carrier Identity --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-bold text-sm text-zinc-900 dark:text-white">
                                        @if($isMe)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-lime-500 text-white text-[9px] font-black uppercase tracking-widest">
                                                Me
                                            </span>
                                        @else
                                            {{ $offer->carrier->organisation ?? $offer->carrier->contact_person }}
                                        @endif
                                    </span>
                                    <flux:badge size="sm" :color="$offer->status->color()">
                                        {{ $offer->status->label() }}
                                    </flux:badge>
                                </div>
                                <div class="flex items-center gap-3 mt-1 text-[11px] text-zinc-400">
                                    @if($offer->proposed_pickup_date)
                                        <span>Pickup: {{ $offer->proposed_pickup_date->format('d M Y') }}</span>
                                    @endif
                                    <span>{{ $offer->created_at->diffForHumans() }}</span>
                                </div>
                                @if($offer->notes)
                                    <p class="text-[11px] italic text-zinc-400 mt-0.5 truncate">
                                        "{{ $offer->notes }}"
                                    </p>
                                @endif
                            </div>

                            {{-- Amount --}}
                            <div class="text-right shrink-0">
                                <span class="font-bold text-lg {{ $isTopRank ? 'text-lime-700' : 'text-zinc-900 dark:text-white' }}">
                                    {{-- US${{ number_format((float) $offer->amount, 2) }} --}}
                                </span>
                            </div>

                            {{-- Moderation Actions --}}
                            @can('manage', $offer)
                                <div class="flex items-center gap-1 shrink-0">
                                    <flux:button
                                        wire:click="shortlistOffer({{ $offer->id }})"
                                        size="sm" variant="ghost"
                                        :icon="$offer->status === \App\Enums\TenderOfferStatus::SHORTLISTED ? 'star-solid' : 'star'"
                                        title="{{ $offer->status === \App\Enums\TenderOfferStatus::SHORTLISTED ? 'Remove shortlist' : 'Shortlist' }}">
                                    </flux:button>

                                    @can('award', $offer)
                                        <flux:button
                                            wire:click="awardOffer({{ $offer->id }})"
                                            wire:confirm="Award this offer? All other offers will be rejected and the tender closed."
                                            size="sm" variant="filled" color="lime" icon="trophy">
                                            Award
                                        </flux:button>
                                    @endcan

                                    <flux:button
                                        wire:click="confirmReject({{ $offer->id }})"
                                        size="sm" variant="ghost" color="red" icon="x-mark"
                                        title="Reject">
                                    </flux:button>
                                </div>
                            @endcan
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            {{-- Public count only --}}
            <div class="p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border border-zinc-200 dark:border-zinc-700 text-center">
                <flux:icon name="users" class="size-6 text-zinc-400 mx-auto mb-2" />
                <flux:text size="sm" class="text-zinc-500">
                    <strong>{{ $this->activeOffers->count() }}</strong> offer(s) submitted for this tender.
                </flux:text>
            </div>
        @endif
    @endif

    {{-- AWARDED BANNER --}}
    @if($this->tenderable->awardedOffer)
        @php $awarded = $this->tenderable->awardedOffer; @endphp
        <div class="p-6 bg-green-50 dark:bg-green-950/20 border border-green-200 dark:border-green-900 rounded-2xl">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-green-100 dark:bg-green-900/40 rounded-full">
                        <flux:icon name="trophy" class="size-5 text-green-600" />
                    </div>
                    <div>
                        <p class="font-black text-green-800 dark:text-green-300 uppercase tracking-widest text-xs mb-0.5">
                            Tender Awarded
                        </p>
                        <p class="font-bold text-green-900 dark:text-white">
                            {{ $awarded->carrier->organisation ?? $awarded->carrier->contact_person }}
                            <span class="font-normal text-green-700 dark:text-green-400">
                                {{-- — US${{ number_format((float) $awarded->amount, 2) }} --}}
                            </span>
                        </p>
                        <p class="text-xs text-green-600 dark:text-green-500 mt-0.5">
                            Awarded {{ $awarded->awarded_at?->diffForHumans() }}
                            by {{ $awarded->awardedBy?->contact_person ?? 'System' }}
                        </p>
                    </div>
                </div>

                @if(auth()->user()?->hasAnyRole(['admin', 'superadmin']))
                    <flux:button size="sm" variant="ghost" color="red"
                        wire:click="revokeAward({{ $awarded->id }})"
                        wire:confirm="Revoke this award and re-open the tender?"
                        icon="arrow-uturn-left">
                        Revoke Award
                    </flux:button>
                @endif
            </div>
        </div>
    @endif

    {{-- CLOSED OFFERS ARCHIVE --}}
    @if($this->canViewLeaderboard && $this->closedOffers->count())
        <details class="group">
            <summary class="cursor-pointer flex items-center gap-2 text-sm text-zinc-400 hover:text-zinc-600 transition-colors list-none">
                <flux:icon name="chevron-right" variant="mini"
                    class="size-4 group-open:rotate-90 transition-transform" />
                Closed Offers ({{ $this->closedOffers->count() }})
            </summary>

            <div class="mt-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-2xl border border-zinc-200 dark:border-zinc-700 divide-y divide-zinc-100 dark:divide-zinc-800 overflow-hidden">
                @foreach($this->closedOffers as $offer)
                    <div class="px-6 py-3 flex items-center gap-4
                        {{ $offer->isAwarded() ? 'bg-green-50 dark:bg-green-900/10' : '' }}">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-sm font-bold text-zinc-700 dark:text-zinc-300">
                                    {{ $offer->carrier->organisation ?? $offer->carrier->contact_person }}
                                </span>
                                <flux:badge size="sm" :color="$offer->status->color()">
                                    {{ $offer->status->label() }}
                                </flux:badge>
                                @if($offer->isAwarded() && $offer->awardedBy)
                                    <span class="text-[10px] text-zinc-400">
                                        by {{ $offer->awardedBy->contact_person }}
                                        · {{ $offer->awarded_at?->format('d M Y') }}
                                    </span>
                                @endif
                            </div>
                            @if($offer->rejection_reason)
                                <p class="text-xs text-red-500 mt-0.5 italic">
                                    "{{ $offer->rejection_reason }}"
                                </p>
                            @endif
                        </div>

                        <span class="font-bold text-sm text-zinc-600 dark:text-zinc-300 shrink-0">
                            {{-- US${{ number_format((float) $offer->amount, 2) }} --}}
                        </span>

                        @if($offer->isAwarded() && auth()->user()?->hasAnyRole(['admin', 'superadmin']))
                            <flux:button
                                wire:click="revokeAward({{ $offer->id }})"
                                wire:confirm="Revoke this award? The tender will need to be re-opened manually."
                                size="sm" variant="ghost" color="red" icon="arrow-uturn-left">
                                Revoke
                            </flux:button>
                        @endif
                    </div>
                @endforeach
            </div>
        </details>
    @endif
 {{-- </div> --}} 

<div class="space-y-6 mt-8">
haboss
</div>