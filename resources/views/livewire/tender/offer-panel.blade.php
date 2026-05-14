<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use App\Models\TenderOffer;
use App\Models\Freight;
use App\Models\Lane;
use App\Enums\TenderOfferStatus;
use App\Enums\PricingType;
use App\ValueObjects\TenderConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

new class extends Component {
    public int $tenderableId;
    public string $tenderableType;
    public string|int|null $freightId = null;

    // Form fields
    public string $amount = '';
    public string $proposedPickup = '';
    public string $proposedDelivery = '';
    public string $notes = '';

    // UI state
    public string $rejectionReason = '';
    public ?int $rejectingOfferId = null;
    public ?int $revising = null;

    // ---------------------------------------------------------------
    // Mount — raw DB only; no Eloquent (enum casts break snapshots)
    // ---------------------------------------------------------------

    public function mount(int $tenderableId, string $tenderableType): void
    {
        $this->tenderableId = $tenderableId;
        $this->tenderableType = $tenderableType;

        $this->amount = match ($tenderableType) {
            Freight::class => (string) ((float) (DB::table('freights')->where('id', $tenderableId)->value('carriage_rate') ?? 0)),
            Lane::class => (string) ((float) (DB::table('lanes')->where('id', $tenderableId)->value('rate') ?? 0)),
            default => '0',
        };
    }

    // ---------------------------------------------------------------
    // Private helpers
    // ---------------------------------------------------------------

    private function tenderableTable(): string
    {
        return match ($this->tenderableType) {
            Freight::class => 'freights',
            Lane::class => 'lanes',
            default => throw new \InvalidArgumentException('Unsupported tenderable type: ' . $this->tenderableType),
        };
    }

    private function rateColumn(): string
    {
        return match ($this->tenderableType) {
            Freight::class => 'carriage_rate',
            Lane::class => 'rate',
            default => 'carriage_rate',
        };
    }

    private function getTenderConfig(): TenderConfig
    {
        return match ($this->tenderableType) {
            Freight::class => new TenderConfig(bidderRole: 'carrier', rankOrder: 'asc', floorField: 'carriage_rate', requiresPickupDate: true, awardPermission: 'award'),
            Lane::class => new TenderConfig(bidderRole: 'shipper', rankOrder: 'desc', floorField: 'rate', requiresPickupDate: false, awardPermission: 'award'),
            default => throw new \InvalidArgumentException('Unsupported tenderable type: ' . $this->tenderableType),
        };
    }

    // ---------------------------------------------------------------
    // Computed
    // ---------------------------------------------------------------

    #[Computed(persist: false)]
    public function tenderable(): Freight|Lane|null
    {
        return $this->tenderableType::find($this->tenderableId);
    }

    #[Computed(persist: false)]
    public function config(): TenderConfig
    {
        return $this->getTenderConfig();
    }

    #[Computed(persist: false)]
    public function referenceAmount(): float
    {
        return (float) (DB::table($this->tenderableTable())->where('id', $this->tenderableId)->value($this->rateColumn()) ?? 0);
    }

    #[Computed(persist: false)]
    public function floorAmount(): float
    {
        return (float) (DB::table($this->tenderableTable())->where('id', $this->tenderableId)->value($this->rateColumn()) ?? 0);
    }

    #[Computed(persist: false)]
    public function isRateOfCarriage(): bool
    {
        if ($this->tenderableType !== Freight::class) {
            return false;
        }

        return DB::table('freights')->where('id', $this->tenderableId)->value('payment_option') === PricingType::RateOfCarriage->value;
    }

    #[Computed(persist: false)]
    public function amountUnit(): string
    {
        return $this->isRateOfCarriage ? '$/km' : 'US$';
    }

    #[Computed(persist: false)]
    public function isOpen(): bool
    {
        return DB::table($this->tenderableTable())->where('id', $this->tenderableId)->value('status') === 'published';
    }

    #[Computed(persist: false)]
    public function myPublishedFreights()
    {
        if (!auth()->check() || $this->tenderableType !== Lane::class) {
            return collect();
        }

        return Freight::query()
            ->where(function ($q) {
                $q->where('shipper_id', auth()->id())->orWhere('creator_id', auth()->id());
            })
            ->where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->get(['id', 'uuid', 'name', 'cityfrom', 'cityto', 'countryfrom', 'countryto']);
    }

    /** Delegates to TenderOfferPolicy::create */
    #[Computed(persist: false)]
    public function canBid(): bool
    {
        if (!auth()->check() || !$this->isOpen || !$this->tenderable) {
            return false;
        }

        return auth()
            ->user()
            ->can('create', [TenderOffer::class, $this->tenderable]);
    }

    /** Delegates to TenderOfferPolicy::viewLeaderboard */
    #[Computed(persist: false)]
    public function canViewLeaderboard(): bool
    {
        if (!auth()->check() || !$this->tenderable) {
            return false;
        }

        return auth()
            ->user()
            ->can('viewLeaderboard', [TenderOffer::class, $this->tenderable]);
    }

    #[Computed(persist: false)]
    public function activeOffers()
    {
        return TenderOffer::query()
            ->with('bidder', 'freight')
            ->where('tenderable_type', $this->tenderableType)
            ->where('tenderable_id', $this->tenderableId)
            ->whereIn('status', [TenderOfferStatus::PENDING, TenderOfferStatus::SHORTLISTED])
            ->orderBy('amount', $this->getTenderConfig()->rankOrder)
            ->get()
            ->values()
            ->map(fn($offer, $index) => tap($offer, fn($o) => ($o->ranked_position = $index + 1)));
    }

    #[Computed(persist: false)]
    public function closedOffers()
    {
        return TenderOffer::query()
            ->with('bidder', 'awardedBy')
            ->where('tenderable_type', $this->tenderableType)
            ->where('tenderable_id', $this->tenderableId)
            ->whereIn('status', [TenderOfferStatus::AWARDED, TenderOfferStatus::REJECTED, TenderOfferStatus::WITHDRAWN, TenderOfferStatus::EXPIRED])
            ->latest('updated_at')
            ->get();
    }

    #[Computed(persist: false)]
    public function awardedOffer(): ?TenderOffer
    {
        return TenderOffer::query()->with('bidder', 'awardedBy')->where('tenderable_type', $this->tenderableType)->where('tenderable_id', $this->tenderableId)->where('status', TenderOfferStatus::AWARDED)->first();
    }

    #[Computed(persist: false)]
    public function myOffer(): ?TenderOffer
    {
        if (!auth()->check()) {
            return null;
        }

        return TenderOffer::query()
            ->with('freight')
            ->where('tenderable_type', $this->tenderableType)
            ->where('tenderable_id', $this->tenderableId)
            ->where('bidder_id', auth()->id())
            ->whereIn('status', [TenderOfferStatus::PENDING, TenderOfferStatus::SHORTLISTED])
            ->first();
    }

    /**
     * All offers the authenticated user has ever placed on this tender,
     * including closed ones — shown in their personal activity feed.
     */
    #[Computed(persist: false)]
    public function myOfferHistory()
    {
        if (!auth()->check()) {
            return collect();
        }

        return TenderOffer::query()
            ->where('tenderable_type', $this->tenderableType)
            ->where('tenderable_id', $this->tenderableId)
            ->where('bidder_id', auth()->id())
            ->latest('updated_at')
            ->get();
    }

    // ---------------------------------------------------------------
    // Actions
    // ---------------------------------------------------------------

    public function submitOffer(): void
    {
        $this->authorize('create', [TenderOffer::class, $this->tenderable]);

        $config = $this->getTenderConfig();
        $floor = $this->referenceAmount;
        $unit = $this->amountUnit;
        if ($this->tenderableType === Lane::class) {
            $this->freightId = (int) $this->freightId;
        }

        $rules = [
            'amount' => match ($this->tenderableType) {
                Freight::class => ['required', 'numeric', 'min:0.01', 'max:' . $floor],
                Lane::class => ['required', 'numeric', 'min:' . $floor],
                default => ['required', 'numeric', 'min:0.01'],
            },
            'notes' => ['nullable', 'string', 'max:500'],
        ];

        // Lane bids must reference one of the shipper's own published freights
        if ($this->tenderableType === Lane::class) {
            $rules['freightId'] = [
                'required',
                'integer',
                Rule::exists('freights', 'id')->where(function ($query) {
                    $query->where('status', 'published')->where(function ($q) {
                        $q->where('shipper_id', auth()->id())->orWhere('creator_id', auth()->id());
                    });
                }),
            ];
        }

        if ($config->requiresPickupDate) {
            $rules['proposedPickup'] = ['required', 'date', 'after_or_equal:today'];
            $rules['proposedDelivery'] = ['nullable', 'date', 'after_or_equal:proposedPickup'];
        }

        $this->validate($rules, [
            'amount.max' => "Your offer must not exceed {$unit}" . number_format($floor, 2) . '.',
            'amount.min' => "Your offer must be at least {$unit}" . number_format($floor, 2) . '.',
            'freightId.required' => 'Please select which of your freights this bid is for.',
            'freightId.exists' => 'The selected freight is not valid or is no longer published.',
        ]);

        $offer = TenderOffer::create([
            'tenderable_type' => $this->tenderableType,
            'tenderable_id' => $this->tenderableId,
            'bidder_id' => auth()->id(),
            'freight_id' => $this->tenderableType === Lane::class ? $this->freightId : null,
            'amount' => $this->amount,
            'proposed_pickup_date' => $this->proposedPickup ?: null,
            'proposed_delivery_date' => $this->proposedDelivery ?: null,
            'notes' => $this->notes,
            'status' => TenderOfferStatus::PENDING,
        ]);

        $offer->notifyStaff('offer_submitted');

        $this->reset(['proposedPickup', 'proposedDelivery', 'notes', 'freightId']);
        $this->amount = (string) $this->referenceAmount;
        session()->flash('offer_success', 'Your offer has been submitted successfully.');
    }

    public function startRevision(int $offerId): void
    {
        $offer = TenderOffer::findOrFail($offerId);
        $this->authorize('update', $offer);

        $this->revising = $offerId;
        $this->amount = (string) $offer->amount;
        $this->proposedPickup = $offer->proposed_pickup_date?->format('Y-m-d') ?? '';
        $this->proposedDelivery = $offer->proposed_delivery_date?->format('Y-m-d') ?? '';
        $this->notes = $offer->notes ?? '';
    }

    public function saveRevision(): void
    {
        $offer = TenderOffer::findOrFail($this->revising);
        $currentAmount = (float) $offer->amount;
        $unit = $this->amountUnit;

        $this->authorize('update', $offer);

        $this->validate([
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                match ($this->tenderableType) {
                    // Freight: revised offer must be LOWER than current
                    Freight::class => fn($attr, $val, $fail) => (float) $val >= $currentAmount ? $fail("Revisions must be lower than your current offer of {$unit}" . number_format($currentAmount, 2) . '.') : null,
                    // Lane: revised offer must be HIGHER than current
                    Lane::class => fn($attr, $val, $fail) => (float) $val <= $currentAmount ? $fail("Revisions must be higher than your current offer of {$unit}" . number_format($currentAmount, 2) . '.') : null,
                    default => fn() => null,
                },
            ],
        ]);

        $offer->update([
            'amount' => $this->amount,
            'proposed_pickup_date' => $this->proposedPickup ?: null,
            'proposed_delivery_date' => $this->proposedDelivery ?: null,
            'notes' => $this->notes,
        ]);

        $offer->notifyStaff('offer_revised');

        $this->reset(['revising', 'proposedPickup', 'proposedDelivery', 'notes']);
        $this->amount = (string) $this->floorAmount;
        session()->flash('offer_success', 'Your offer has been revised successfully.');
    }

    public function withdrawOffer(int $offerId): void
    {
        $offer = TenderOffer::findOrFail($offerId);
        $this->authorize('withdraw', $offer);

        $offer->update(['status' => TenderOfferStatus::WITHDRAWN]);
        $offer->notifyStaff('offer_withdrawn');

        session()->flash('offer_success', 'Your offer has been withdrawn.');
    }

    public function shortlistOffer(int $offerId): void
    {
        $offer = TenderOffer::findOrFail($offerId);
        $this->authorize('manage', $offer);

        $newStatus = $offer->status === TenderOfferStatus::SHORTLISTED ? TenderOfferStatus::PENDING : TenderOfferStatus::SHORTLISTED;

        $offer->update(['status' => $newStatus]);

        if ($newStatus === TenderOfferStatus::SHORTLISTED) {
            $offer->notifyBidder('offer_shortlisted');
        }
    }

    public function confirmReject(int $offerId): void
    {
        $this->rejectingOfferId = $offerId;
        $this->rejectionReason = '';
    }

    public function rejectOffer(): void
    {
        if (!$this->rejectingOfferId) {
            return;
        }

        $offer = TenderOffer::findOrFail($this->rejectingOfferId);
        $this->authorize('manage', $offer);

        $offer->update([
            'status' => TenderOfferStatus::REJECTED,
            'rejection_reason' => $this->rejectionReason ?: null,
        ]);

        $offer->notifyBidder('offer_rejected');

        $this->reset(['rejectingOfferId', 'rejectionReason']);
    }

    public function awardOffer(int $offerId): void
    {
        $offer = TenderOffer::findOrFail($offerId);
        $this->authorize('award', $offer);

        // Reject all other active offers — bulk update skips model events intentionally;
        // rankings are irrelevant once the tender is closed immediately after.
        $others = TenderOffer::query()
            ->with('bidder') // needed so notifyBidder can load the relation
            ->where('tenderable_type', $this->tenderableType)
            ->where('tenderable_id', $this->tenderableId)
            ->whereIn('status', [TenderOfferStatus::PENDING->value, TenderOfferStatus::SHORTLISTED->value])
            ->where('id', '!=', $offerId)
            ->get();

        foreach ($others as $other) {
            $other->update(['status' => TenderOfferStatus::REJECTED->value]);
            $other->notifyBidder('offer_rejected');
        }

        $offer->update([
            'status' => TenderOfferStatus::AWARDED,
            'awarded_at' => now(),
            'awarded_by' => auth()->id(),
        ]);

        $offer->notifyBidder('offer_awarded');

        DB::table($this->tenderableTable())
            ->where('id', $this->tenderableId)
            ->update(['status' => 'unpublished']);

        session()->flash('offer_success', 'Offer awarded. The listing has been closed.');
    }

    public function revokeAward(int $offerId): void
    {
        $offer = TenderOffer::findOrFail($offerId);
        $this->authorize('revoke', $offer);

        $offer->update([
            'status' => TenderOfferStatus::WITHDRAWN,
            'awarded_at' => null,
            'awarded_by' => null,
        ]);

        $offer->notifyBidder('award_revoked');

        DB::table($this->tenderableTable())
            ->where('id', $this->tenderableId)
            ->update(['status' => 'published']);

        session()->flash('offer_success', 'Award revoked. Listing is now open again.');
    }
};
?>

<div class="space-y-6 mt-8">

    {{-- ── Flash ─────────────────────────────────────────────────────── --}}
    @if (session('offer_success'))
        <div class="p-4 text-sm text-emerald-800 rounded-2xl bg-emerald-50 border border-emerald-100">
            {{ session('offer_success') }}
        </div>
    @endif

    {{-- ── Header ──────────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-3">
        <flux:icon name="banknotes" class="size-5 text-zinc-400" />
        <flux:heading size="lg">Tender Offers</flux:heading>
        @if ($this->canViewLeaderboard)
            <flux:badge size="sm" color="zinc">{{ $this->activeOffers->count() }} active</flux:badge>
        @endif
    </div>

    {{-- ── Tender closed notice ─────────────────────────────────────────── --}}
    @if (!$this->isOpen)
        <div
            class="p-4 bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700 rounded-xl text-center">
            <flux:icon name="lock-closed" class="size-5 text-zinc-400 mx-auto mb-2" />
            <flux:text size="sm" class="text-zinc-500">This tender is not currently open for offers.</flux:text>
        </div>
    @endif

    @auth

        {{-- ── Submit form ──────────────────────────────────────────────────── --}}
        @if ($this->isOpen && $this->canBid && !$this->myOffer)
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6">
                <flux:heading size="md" class="mb-1">Submit Your Offer</flux:heading>
                {{-- <flux:text size="sm" class="mb-6 text-zinc-500">
                    Minimum offer: <strong>{{ $this->amountUnit }} {{ number_format($this->floorAmount, 2) }}</strong>
                </flux:text> --}}
                <flux:text size="sm" class="mb-6 text-zinc-500">
                    @if ($this->tenderableType === \App\Models\Freight::class)
                        Maximum offer: <strong>{{ $this->amountUnit }}{{ number_format($this->floorAmount, 2) }}</strong>
                        <span class="block text-xs mt-0.5 text-zinc-400">Submit the lowest rate you can offer — the shipper
                            favours the lowest bid.</span>
                    @else
                        Minimum offer: <strong>{{ $this->amountUnit }}{{ number_format($this->floorAmount, 2) }}</strong>
                        <span class="block text-xs mt-0.5 text-zinc-400">Submit the highest rate you can offer — the carrier
                            favours the highest bid.</span>
                    @endif
                </flux:text>

                <div class="space-y-4">
                    @if ($this->tenderableType === \App\Models\Lane::class)
                        <div>
                            @if ($this->myPublishedFreights->isEmpty())
                                <div
                                    class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl text-sm text-amber-800 dark:text-amber-300">
                                    <flux:icon name="exclamation-triangle" variant="mini" class="size-4 inline mr-1" />
                                    You have no published freights to bid with. Please publish a freight listing first.
                                </div>
                            @else
                                <flux:select label="Which of your freights is this bid for?" wire:model="freightId"
                                    placeholder="Select a freight...">
                                    @foreach ($this->myPublishedFreights as $freight)
                                        <flux:select.option value="">— Select a freight —</flux:select.option>
                                        <flux:select.option :value="$freight->id">
                                            {{ $freight->name }}
                                            — {{ $freight->cityfrom }}, {{ $freight->countryfrom }}
                                            → {{ $freight->cityto }}, {{ $freight->countryto }}
                                        </flux:select.option>
                                    @endforeach
                                </flux:select>
                            @endif
                        </div>
                    @endif
                    <div>
                        <flux:input type="number" step="0.01" label="Your Offer ({{ $this->amountUnit }})"
                            wire:model="amount"
                            :min="$this->tenderableType === \App\Models\Lane::class ? $this->floorAmount : 0.01"
                            :max="$this->tenderableType === \App\Models\Freight::class ? $this->floorAmount : null" />

                    </div>

                    @if ($this->config->requiresPickupDate)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <flux:input type="date" label="Proposed Pickup Date" wire:model="proposedPickup" />
                            </div>
                            <flux:input type="date" label="Proposed Delivery Date (optional)"
                                wire:model="proposedDelivery" />
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

        {{-- ── My active offer ──────────────────────────────────────────────── --}}
        @if ($this->myOffer)
            @php $mine = $this->myOffer; @endphp
            <div class="bg-lime-50 dark:bg-lime-900/20 border border-lime-300 dark:border-lime-700 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <flux:icon name="star" variant="mini" class="size-4 text-lime-600" />
                        <flux:heading size="md">My Current Offer</flux:heading>
                    </div>
                    <flux:badge color="lime" size="sm">Rank #{{ $mine->ranked_position ?? '—' }}</flux:badge>
                </div>

                @if ($this->revising === $mine->id)
                    {{-- Revision form --}}
                    <div class="space-y-4">
                        <div>
                            <flux:input type="number" step="0.01"
                                label="{{ $this->tenderableType === \App\Models\Freight::class
                                    ? 'Revised Amount — must be lower than ' . $this->amountUnit . number_format((float) $mine->amount, 2)
                                    : 'Revised Amount — must be higher than ' . $this->amountUnit . number_format((float) $mine->amount, 2) }}"
                                wire:model="amount"
                                :max="$this->tenderableType === \App\Models\Freight::class ? $mine->amount : null"
                                :min="$this->tenderableType === \App\Models\Lane::class ? $mine->amount : 0.01" />
                            <flux:error name="amount" />
                        </div>

                        @if ($this->config->requiresPickupDate)
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
                            <flux:button wire:click="$set('revising', null)" variant="ghost">Cancel</flux:button>
                        </div>
                    </div>
                @else
                    {{-- Offer summary --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm mb-4">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-zinc-400 block">Offer</span>
                            <span class="font-bold text-lg text-zinc-900 dark:text-white">
                                {{ $this->amountUnit }} {{ number_format((float) $mine->amount, 2) }}
                            </span>
                        </div>
                        @if ($mine->freight_id && $this->tenderableType === \App\Models\Lane::class)
                            <div class="col-span-2 md:col-span-4">
                                <span class="text-[10px] uppercase font-bold text-zinc-400 block">Freight</span>
                                <a href="{{ route('freights.show', $mine->freight->uuid) }}"
                                    class="font-medium text-sm text-zinc-900 dark:text-white hover:text-lime-600 transition-colors">
                                    {{ $mine->freight->name ?? $mine->freight->uuid }}
                                    @if ($mine->freight->name)
                                        <span class="text-zinc-400 font-normal">
                                            — {{ $mine->freight->cityfrom }}, {{ $mine->freight->countryfrom }}
                                            → {{ $mine->freight->cityto }}, {{ $mine->freight->countryto }}
                                        </span>
                                    @endif
                                </a>
                            </div>
                        @endif
                        @if ($mine->proposed_pickup_date)
                            <div>
                                <span class="text-[10px] uppercase font-bold text-zinc-400 block">Pickup</span>
                                <span class="font-medium">{{ $mine->proposed_pickup_date->format('d M Y') }}</span>
                            </div>
                        @endif
                        @if ($mine->proposed_delivery_date)
                            <div>
                                <span class="text-[10px] uppercase font-bold text-zinc-400 block">Delivery</span>
                                <span class="font-medium">{{ $mine->proposed_delivery_date->format('d M Y') }}</span>
                            </div>
                        @endif
                        <div>
                            <span class="text-[10px] uppercase font-bold text-zinc-400 block">Submitted</span>
                            <span class="font-medium">{{ $mine->created_at->diffForHumans() }}</span>
                        </div>
                    </div>

                    @if ($mine->notes)
                        <p class="text-xs italic text-zinc-500 mb-4">"{{ $mine->notes }}"</p>
                    @endif

                    @if ($this->isOpen)
                        <div class="flex gap-2">
                            <flux:button wire:click="startRevision({{ $mine->id }})" variant="ghost" size="sm"
                                icon="pencil-square">
                                Revise Offer
                            </flux:button>
                            <flux:button wire:click="withdrawOffer({{ $mine->id }})"
                                wire:confirm="Withdraw your offer? This cannot be undone." variant="ghost" size="sm"
                                icon="x-mark" color="red">
                                Withdraw
                            </flux:button>
                        </div>
                    @endif
                @endif
            </div>
        @endif

        {{-- ── My offer history / activity feed (bidder-facing) ────────────── --}}
        {{-- Shown to carriers/shippers who are not leaderboard viewers,
         so they can track the outcome of all their offers on this tender --}}
        @if (!$this->canViewLeaderboard && $this->myOfferHistory->count())
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-zinc-100 dark:border-zinc-800 flex items-center gap-2">
                    <flux:icon name="clock" class="size-4 text-zinc-400" />
                    <flux:heading size="md">My Offer Activity</flux:heading>
                </div>

                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach ($this->myOfferHistory as $histOffer)
                        @php
                            $statusColor = $histOffer->status->color();
                            $isAwarded = $histOffer->isAwarded();
                            $isRejected = $histOffer->status === \App\Enums\TenderOfferStatus::REJECTED;
                            $isShortlist = $histOffer->status === \App\Enums\TenderOfferStatus::SHORTLISTED;
                        @endphp
                        <div
                            class="px-6 py-4 {{ $isAwarded ? 'bg-green-50 dark:bg-green-900/10' : ($isRejected ? 'bg-red-50/50 dark:bg-red-900/10' : '') }}">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1 min-w-0">
                                    {{-- Status headline --}}
                                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                                        <flux:badge size="sm" :color="$statusColor">
                                            {{ $histOffer->status->label() }}
                                        </flux:badge>
                                        <span class="text-[11px] text-zinc-400">
                                            {{ $histOffer->updated_at->diffForHumans() }}
                                        </span>
                                    </div>

                                    {{-- Human-readable outcome message --}}
                                    @if ($isAwarded)
                                        <p class="text-sm font-semibold text-green-800 dark:text-green-300">
                                            🎉 Congratulations! Your offer was awarded. Please await further instructions
                                            from the team.
                                        </p>
                                    @elseif($isShortlist)
                                        <p class="text-sm font-medium text-blue-800 dark:text-blue-300">
                                            Your offer has been shortlisted. A final decision will be made soon.
                                        </p>
                                    @elseif($isRejected)
                                        <p class="text-sm text-red-700 dark:text-red-400">
                                            Your offer was not successful on this occasion.
                                            @if ($histOffer->rejection_reason)
                                                <span class="block mt-0.5 italic text-xs text-red-500">
                                                    "{{ $histOffer->rejection_reason }}"
                                                </span>
                                            @endif
                                        </p>
                                    @elseif($histOffer->status === \App\Enums\TenderOfferStatus::WITHDRAWN)
                                        <p class="text-sm text-zinc-500">You withdrew this offer.</p>
                                    @else
                                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Your offer is under review.</p>
                                    @endif
                                </div>

                                {{-- Amount --}}
                                <div class="text-right shrink-0">
                                    <span class="text-[10px] uppercase font-bold text-zinc-400 block">Amount</span>
                                    <span class="font-bold text-zinc-900 dark:text-white">
                                        {{ $this->amountUnit }} {{ number_format((float) $histOffer->amount, 2) }}
                                    </span>
                                </div>
                            </div>

                            @if ($histOffer->proposed_pickup_date)
                                <div class="flex items-center gap-4 mt-2 text-[11px] text-zinc-400">
                                    <span>Pickup: {{ $histOffer->proposed_pickup_date->format('d M Y') }}</span>
                                    @if ($histOffer->proposed_delivery_date)
                                        <span>Delivery: {{ $histOffer->proposed_delivery_date->format('d M Y') }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    @endauth

    {{-- ── Rejection confirmation modal ────────────────────────────────── --}}
    @if ($rejectingOfferId)
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

    {{-- ── Live leaderboard (staff + tenderable owner) ──────────────────── --}}
    @if ($this->canViewLeaderboard && $this->activeOffers->count())
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-zinc-100 dark:border-zinc-800 flex items-center justify-between">
                <flux:heading size="md">Live Leaderboard</flux:heading>
                <flux:text size="xs" class="text-zinc-400 uppercase tracking-widest">
                    {{ $this->config->rankOrder === 'asc' ? 'Lowest offer wins' : 'Highest offer wins' }}
                </flux:text>
            </div>

            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @foreach ($this->activeOffers as $offer)
                    @php
                        $isTopRank = $offer->ranked_position === 1;
                        $isMe = auth()->id() === $offer->bidder_id;
                    @endphp
                    <div
                        class="px-6 py-4 flex items-center gap-4 {{ $isTopRank ? 'bg-lime-50 dark:bg-lime-900/10' : '' }}">

                        {{-- Rank --}}
                        <div class="w-8 text-center shrink-0">
                            @if ($isTopRank)
                                <flux:icon name="trophy" variant="mini" class="size-5 text-amber-500 mx-auto" />
                            @else
                                <span class="text-sm font-bold text-zinc-400">#{{ $offer->ranked_position }}</span>
                            @endif
                        </div>

                        {{-- Bidder --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                @if ($isMe)
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full bg-lime-500 text-white text-[9px] font-black uppercase tracking-widest">
                                        Me
                                    </span>
                                @else
                                    {{-- Clickable bidder name → users.show --}}
                                    <a href="{{ route('users.show', $offer->bidder->slug) }}"
                                        class="font-bold text-sm text-zinc-900 dark:text-white hover:text-lime-600 dark:hover:text-lime-400 transition-colors">
                                        {{ $offer->bidder->organisation ?? $offer->bidder->contact_person }}
                                    </a>
                                @endif
                                <flux:badge size="sm" :color="$offer->status->color()">
                                    {{ $offer->status->label() }}
                                </flux:badge>
                            </div>
                            <div class="flex items-center gap-3 mt-1 text-[11px] text-zinc-400">
                                @if ($offer->proposed_pickup_date)
                                    <span>Pickup: {{ $offer->proposed_pickup_date->format('d M Y') }}</span>
                                @endif
                                <span>{{ $offer->created_at->diffForHumans() }}</span>
                            </div>
                            @if ($offer->notes)
                                <p class="text-[11px] italic text-zinc-400 mt-0.5 truncate">"{{ $offer->notes }}"</p>
                            @endif

                            @if ($offer->freight_id)
                                <div class="mt-1 text-[11px] text-zinc-400">
                                    Freight:
                                    <a href="{{ route('freights.show', $offer->freight->uuid) }}"
                                        class="underline hover:text-zinc-600 transition-colors">
                                        {{ $offer->freight->name ?? $offer->freight->uuid }}
                                    </a>
                                </div>
                            @endif
                        </div>

                        {{-- Amount --}}
                        <div class="text-right shrink-0">
                            <span
                                class="font-bold text-lg {{ $isTopRank ? 'text-lime-700' : 'text-zinc-900 dark:text-white' }}">
                                {{ $this->amountUnit }} {{ number_format((float) $offer->amount, 2) }}
                            </span>
                        </div>

                        {{-- Moderation actions --}}
                        @can('manage', $offer)
                            <div class="flex items-center gap-1 shrink-0">
                                <flux:button wire:click="shortlistOffer({{ $offer->id }})" size="sm"
                                    variant="ghost"
                                    :icon="$offer->status === \App\Enums\TenderOfferStatus::SHORTLISTED ? 'sparkles' : 'star'"
                                    :title="$offer->status === \App\Enums\TenderOfferStatus::SHORTLISTED ? 'Remove shortlist' : 'Shortlist'">
                                </flux:button>

                                @can('award', $offer)
                                    <flux:button wire:click="awardOffer({{ $offer->id }})"
                                        wire:confirm="Award this offer? All other offers will be rejected and the tender closed."
                                        size="sm" variant="filled" color="lime" icon="trophy">
                                        Award
                                    </flux:button>
                                @endcan

                                <flux:button wire:click="confirmReject({{ $offer->id }})" size="sm"
                                    variant="ghost" color="red" icon="x-mark" title="Reject">
                                </flux:button>
                            </div>
                        @endcan

                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ── Awarded banner ────────────────────────────────────────────────── --}}
    @if ($this->awardedOffer)
        @php $awarded = $this->awardedOffer; @endphp
        <div class="p-6 bg-green-50 dark:bg-green-950/20 border border-green-200 dark:border-green-900 rounded-2xl">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-green-100 dark:bg-green-900/40 rounded-full">
                        <flux:icon name="trophy" class="size-5 text-green-600" />
                    </div>
                    <div>
                        <p
                            class="font-black text-green-800 dark:text-green-300 uppercase tracking-widest text-xs mb-0.5">
                            Tender Awarded
                        </p>
                        <p class="font-bold text-green-900 dark:text-white">
                            {{-- Clickable winner name --}}
                            <a href="{{ route('users.show', $awarded->bidder->slug) }}"
                                class="hover:text-lime-700 transition-colors">
                                {{ $awarded->bidder->organisation ?? $awarded->bidder->contact_person }}
                            </a>
                            @if ($this->canViewLeaderboard)
                                <span class="font-normal text-green-700 dark:text-green-400 ml-1">
                                    — {{ $this->amountUnit }} {{ number_format((float) $awarded->amount, 2) }}
                                </span>
                            @endif
                        </p>
                        <p class="text-xs text-green-600 dark:text-green-500 mt-0.5">
                            Awarded {{ $awarded->awarded_at?->diffForHumans() }}
                            by @if ($awarded->awardedBy)
                                <a href="{{ route('users.show', $awarded->awardedBy->slug) }}"
                                    class="hover:text-green-700 dark:hover:text-green-300 underline transition-colors">
                                    {{ $awarded->awardedBy->contact_person }}
                                </a>
                            @else
                                System
                            @endif
                        </p>
                    </div>
                </div>

                @can('revoke', $awarded)
                    <flux:button size="sm" variant="ghost" color="red"
                        wire:click="revokeAward({{ $awarded->id }})"
                        wire:confirm="Revoke this award and re-open the tender?" icon="arrow-uturn-left">
                        Revoke Award
                    </flux:button>
                @endcan
            </div>
        </div>
    @endif

    {{-- ── Closed offers archive (staff + tenderable owner) ─────────────── --}}
    @if ($this->canViewLeaderboard && $this->closedOffers->count())
        <details class="group">
            <summary
                class="cursor-pointer flex items-center gap-2 text-sm text-zinc-400 hover:text-zinc-600 transition-colors list-none">
                <flux:icon name="chevron-right" variant="mini"
                    class="size-4 group-open:rotate-90 transition-transform" />
                Closed Offers ({{ $this->closedOffers->count() }})
            </summary>

            <div
                class="mt-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-2xl border border-zinc-200 dark:border-zinc-700 divide-y divide-zinc-100 dark:divide-zinc-800 overflow-hidden">
                @foreach ($this->closedOffers as $offer)
                    <div
                        class="px-6 py-3 flex items-center gap-4 {{ $offer->isAwarded() ? 'bg-green-50 dark:bg-green-900/10' : '' }}">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                {{-- Clickable bidder name in closed archive --}}
                                <a href="{{ route('users.show', $offer->bidder->slug) }}"
                                    class="text-sm font-bold text-zinc-700 dark:text-zinc-300 hover:text-lime-600 dark:hover:text-lime-400 transition-colors">
                                    {{ $offer->bidder->organisation ?? $offer->bidder->contact_person }}
                                </a>
                                <flux:badge size="sm" :color="$offer->status->color()">
                                    {{ $offer->status->label() }}
                                </flux:badge>
                                @if ($offer->isAwarded() && $offer->awardedBy)
                                    <span class="text-[10px] text-zinc-400">
                                        by
                                        <a href="{{ route('users.show', $offer->awardedBy->slug) }}"
                                            class="hover:text-zinc-600 dark:hover:text-zinc-300 underline transition-colors">
                                            {{ $offer->awardedBy->contact_person }}
                                        </a>
                                        · {{ $offer->awarded_at?->format('d M Y') }}
                                    </span>
                                @endif
                            </div>
                            @if ($offer->rejection_reason)
                                <p class="text-xs text-red-500 mt-0.5 italic">"{{ $offer->rejection_reason }}"</p>
                            @endif
                        </div>

                        <span class="font-bold text-sm text-zinc-600 dark:text-zinc-300 shrink-0">
                            {{ $this->amountUnit }} {{ number_format((float) $offer->amount, 2) }}
                        </span>

                        @can('revoke', $offer)
                            @if ($offer->isAwarded())
                                <flux:button wire:click="revokeAward({{ $offer->id }})"
                                    wire:confirm="Revoke this award? The tender will need to be re-opened manually."
                                    size="sm" variant="ghost" color="red" icon="arrow-uturn-left">
                                    Revoke
                                </flux:button>
                            @endif
                        @endcan
                    </div>
                @endforeach
            </div>
        </details>
    @endif

</div>
