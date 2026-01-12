<?php

namespace App\Listeners;

use App\Models\User;
use App\Notifications\NewClientRegistered;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class NotifyStaffOfNewClient implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Define which staff roles care about which client roles.
     */
    protected array $roleMap = [
        'carrier' => [
            'procurement logistics associate',
            'operations logistics associate',
            'logistics operations executive',
        ],
        'shipper' => [
            'marketing logistics associate',
            'operations logistics associate',
            'logistics operations executive',
        ],
    ];

    public function handle(Registered $event): void
    {
        // 1. Refresh user and load relationships to ensure data is available to the queue
        $newClient = $event->user;
        $newClient->load(['roles', 'buslocation']);

        // 2. Identify which role the new user has
        // We check the 'roles' relationship directly since we aren't using a package
        $clientRole = collect($this->roleMap)->keys()->first(function ($roleName) use ($newClient) {
            return $newClient->roles->contains('name', $roleName);
        });

        if (!$clientRole) return;

        // 3. Get the location of the new client
        $location = $newClient->buslocation->first();
        if (!$location) return;

        // 4. Find target staff members using a standard whereHas query
        $targetRoles = $this->roleMap[$clientRole];
        
        $relevantStaff = User::whereHas('roles', function ($query) use ($targetRoles) {
            $query->whereIn('name', $targetRoles);
        })->get()->filter(function ($staff) use ($location) {
            // Logic centralized in User Model
            $bounds = $staff->getGeographicalBounds();
            
            return in_array($location->country, $bounds['countries'] ?? []) || 
                   in_array($location->city, $bounds['cities'] ?? []);
        });

        // 5. Send the notification
        if ($relevantStaff->isNotEmpty()) {
            Notification::send($relevantStaff, new NewClientRegistered($newClient));
        }
    }
}