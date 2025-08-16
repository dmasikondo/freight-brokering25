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
     * Registers a new user based on the provided data and the user who is creating them.
     *
     * @param array $data The validated user data.
     * @param User|null $creator The authenticated user performing the creation (or null for self-registration).
     * @return User|null The newly created user, or null if creation failed.
     */
    public function registerUser(array $data, ?User $creator = null): ?User
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

        // 2. Data Preparation
        $userData = [
            'contact_person' => $data['first_name']. ' '.$data['surname'],
            'contact_phone' => $data['contact_phone'],
            'phone_type' => $data['phone_type'],
            'whatsapp' => $data['whatsapp'],
            'email' => $data['email'],
            'slug' => Str::slug($data['first_name'] . ' ' . $data['surname']) . '-' . uniqid(),
            'password' => Hash::make($data['password']),
        ];

        // Add optional fields for shipper/carrier roles
        $targetRole = $data['customer_type'];
        if (in_array($targetRole, ['shipper', 'carrier'])) {
            $userData['organisation'] = $data['company_name'];
        }

        // 3. Create the User
        $user = User::create($userData);
        if (!$user) {
            return null;
        }

        // Assign Roles and Metadata        
        if (in_array($targetRole, ['shipper', 'carrier'])) {
            $user->save();
            $user->buslocation()->create([
                'country' => $data['country'],
                'city' => $data['city'],
                'address' => $data['address'],
            ]);
            $user->assignRole("{$data['customer_type']}:{$data['ownership_type']}");
        }
        else{
            $user->assignRole($targetRole);
        }

        //  Add Audit Trail for Staff-assisted Registration
        if ($creator) {
            $user->creationAudit()->create([
                'creator_user_id' => $creator->id,
            ]);
        } else {
            // Self-registration: fire event and log in the user and redirect
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

        // Default: If no specific role is matched, the user can't register others.
        return collect([]);
    }
}