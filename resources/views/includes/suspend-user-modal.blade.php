<flux:modal name="suspend_user_modal" class="md:w-[450px]">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Suspend User Account</flux:heading>
            <flux:subheading>This will restrict {{ $user->contact_person }} from accessing the platform immediately.
            </flux:subheading>
        </div>

        <form action="{{ route('users.suspend', $user) }}" method="POST" class="space-y-6">
            @csrf
            <flux:input label="Reason for Suspension" placeholder="e.g., Policy violation, Unpaid fees..."
                name="suspension_reason" required />

            <div class="flex gap-3">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" color="rose">Confirm Suspension</flux:button>
            </div>
        </form>
    </div>
</flux:modal>
