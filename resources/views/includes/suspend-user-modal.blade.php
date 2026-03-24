<flux:modal name="suspend_user_modal" class="md:w-[450px]">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">
                {{ $user->suspended_at ? 'Restore User Access' : 'Suspend User Account' }}
            </flux:heading>
            <flux:subheading>
                {{ $user->suspended_at 
                    ? "This will allow {$user->contact_person} to log back into the platform." 
                    : "This will restrict {$user->contact_person} from accessing the platform immediately." 
                }}
            </flux:subheading>
        </div>

        <form action="{{ $user->suspended_at ? route('users.unsuspend', $user) : route('users.suspend', $user) }}" method="POST" class="space-y-6">
            @csrf
            {{-- Use PATCH for unsuspend as per your web.php, POST for suspend --}}
            @if($user->suspended_at)
                @method('PATCH')
            @endif

            @if(!$user->suspended_at)
                <flux:input 
                    label="Reason for Suspension" 
                    placeholder="e.g., Policy violation, Unpaid fees..."
                    name="suspension_reason" 
                    required 
                />
            @else
                <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800">
                    <p class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Previous Suspension Reason</p>
                    <p class="text-sm font-medium mt-1 text-zinc-600 dark:text-zinc-300">
                        {{ $user->suspension_reason ?? 'No reason provided.' }}
                    </p>
                </div>
            @endif

            <div class="flex gap-3">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                
                <flux:button 
                    type="submit" 
                    variant="primary" 
                    :color="$user->suspended_at ? 'emerald' : 'rose'"
                >
                    {{ $user->suspended_at ? 'Confirm Restoration' : 'Confirm Suspension' }}
                </flux:button>
            </div>
        </form>
    </div>
</flux:modal>