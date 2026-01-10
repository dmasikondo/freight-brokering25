<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Str;

class UserRegistrationService
{
    /**
     * Registers or updates a user based on the provided data and the user who is creating them.
     *
     * @param array $data The validated user data.
     * @param User|null $creator The authenticated user performing the action (or null for self-registration).
     * @param User|null $user The user model to update (or null for a new user).
     * @return User|null The created/updated user, or null if the action failed.
     */
    public function registerUser(array $data, ?User $creator = null, ?User $user = null): ?User
    {
        // 1. Authorization: Check if the creator can create the requested role 
        // PLANNING TO USE A USER POLICY FOR THIS
        // if ($creator) {
        //     $allowedRoles = $this->getAllowedRolesForUser($creator);
        //     if (!in_array($data['role'], $allowedRoles->pluck('name')->toArray())) {
        //         Log::warning("User {$creator->id} attempted to create user with unauthorized role '{$data['role']}'.");
        //         return null; // Authorization failed
        //     }
        // }

        // 2. Data Preparation for both creation and update
        $userData = [
            'contact_person' => $data['first_name'] . ' ' . $data['surname'],
            'contact_phone' => $data['contact_phone'],
            'phone_type' => $data['phone_type'],
            'whatsapp' => $data['whatsapp'],
            'email' => $data['email'],
        ];

        // Handle creation vs. update
        if (!$user) {
            // New User Creation Logic
            $userData['slug'] = Str::slug($data['first_name'] . ' ' . $data['surname']) . '-' . uniqid();
            $userData['password'] = Hash::make($data['password']);
            $user = User::create($userData);
        } else {

            // Existing User Update Logic
            $user->update($userData);

            // Handle password update if provided
            if (!empty($data['password'])) {
                $user->password = Hash::make($data['password']);
                $user->save();
            }

            // Remove existing relationships to replace them
            $user->roles()->detach();
            // $user->buslocation()->delete();
        }

        if (!$user) {
            // Creation/update failed
            return null;
        }

        // 3. Assign Roles and Metadata 
        $targetRole = $data['customer_type'];
        if (in_array($targetRole, ['shipper', 'carrier'])) {
            $user->update(['organisation' => $data['company_name']]);
            $user->buslocation()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'country' => $data['country'],
                    'city' => $data['city'],
                    'address' => $data['address'],
                ]
            );
            $rolesToAssign = "{$data['customer_type']}:{$data['ownership_type']}";
            $user->assignRole($rolesToAssign);
        } else {
            $user->assignRole($targetRole);
        }

        // 4. Add Audit Trail for Staff-assisted Action
        if ($creator) {
            // Use updateOrCreate to prevent unique constraint violations
            $user->creationAudit()->updateOrCreate(
                ['created_user_id' => $user->id, 'creator_user_id' => $creator->id],
                ['creator_user_id' => $creator->id]
            );
        }

        // 5. Fire Registered Event
        // Only fire event for self-registration, not for staff-assisted creation/update
        if (!$creator && $user->wasRecentlyCreated) {
            event(new Registered($user));
        }

        return $user;
    }

    /**
     * Get the roles a user is allowed to register others into.
     *
     * @param User $user The user performing the registration.
     * @return \Illuminate\Support\Collection
     */
    public function getAllowedRolesForUser(User $user): \Illuminate\Support\Collection
    {
        $allRoles = Role::pluck('name', 'name');

        if ($user->hasRole('superadmin')) {
            // Superadmin can register all users
            return $allRoles;
        }
        if ($user->hasRole('admin')) {
            // Admin can register all users except superadmin
            return $allRoles->except('superadmin');
        }
        if ($user->hasRole('procurement executive associate')) {
            // Procurement logistics executive can register shippers and carriers
            return $allRoles->only(['shipper', 'carrier']);
        }
        if ($user->hasRole('procurement logistics associate')) {
            // Procurement logistics associate can register carriers only
            return $allRoles->only(['carrier']);
        }
        if ($user->hasRole('marketing logistics associate')) {
            // Marketing logistics associate can register shippers only
            return $allRoles->only(['shipper']);
        }
        if ($user->hasRole('operations logistics associate')) {
            // operations logistics associate can register shippers and carriers only
            return $allRoles->only(['shipper', 'carrier']);
        }

        // Default: If no specific role is matched, the user can't register others.
        return collect([]);
    }
}
