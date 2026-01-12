<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Traits\Auditable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'contact_person',
        'phone_type',
        'contact_phone',
        'whatsapp',
        'organisation',
        'slug',
        'suspended_at',
        'suspension_reason',
        'suspended_by_id',
        'approved_at',
        'approved_by_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'suspended_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    // --- State Helpers ---

    public function isSuspended(): bool
    {
        return !is_null($this->suspended_at);
    }

    public function isApproved(): bool
    {
        return !is_null($this->approved_at);
    }

    public function needsApproval(): bool
    {
        return $this->hasAnyRole(['shipper', 'carrier']) && !$this->isApproved();
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->username)
            ->explode(' ')
            ->take(2)
            ->map(fn($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)
            ->withPivot('classification', 'created_at')
            ->withTimestamps();
    }

    public function assignRole(string $rolesWithOwnership): void
    {
        // Split the input string by commas to get individual roles
        $rolesArray = explode(',', $rolesWithOwnership);
        $data = [];

        foreach ($rolesArray as $roleWithOwnership) {
            // Split each role entry into role name and ownership type (if provided)
            $parts = array_map('trim', explode(':', $roleWithOwnership));

            // Ensure the role name is always present
            if (count($parts) > 0) {
                $roleName = $parts[0];
                $ownershipType = count($parts) === 2 ? $parts[1] : null;

                // Retrieve the role based on the provided name
                $role = Role::where('name', $roleName)->firstOrFail();

                // Prepare the data for the pivot table
                $roleData = [];
                if ($ownershipType) {
                    $roleData['classification'] = $ownershipType;
                }

                // Store the role ID and its corresponding data
                $data[$role->id] = $roleData;
            } else {
                // Handle the case where the format is incorrect
                throw new \InvalidArgumentException("Invalid format for role: '{$roleWithOwnership}'. Expected format is 'roleName[:ownershipType]'.");
            }
        }

        // Sync the roles with the user, detaching any previously assigned roles
        $this->roles()->sync($data);
    }



    /**
     * Check if the user has role of
     */
    public function hasRole($role)
    {
        return  (bool) $this->roles()->where('name', $role)->count();
    }

    /**
     * Determine if the user has any of the given roles.
     *
     * @param  array|string  $roles
     * @return bool
     */
    public function hasAnyRole($roles): bool
    {
        $roles = is_array($roles) ? $roles : func_get_args();

        return $this->roles()->whereIn('name', $roles)->exists();
    }

    public function suspendedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'suspended_by_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    public function buslocation(): hasMany
    {
        return $this->hasMany(Buslocation::class);
    }

    public function creationAudit()
    {
        return $this->hasOne(UserCreation::class, 'created_user_id');
    }
    public function createdBy()
    {
        return $this->hasOneThrough(
            User::class,
            UserCreation::class,
            'created_user_id', // Foreign key on the user_creations table
            'id',              // Foreign key on the users table
            'id',              // Local key on the users table
            'creator_user_id'  // Local key on the user_creations table
        );
    }

    public function createdUsers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(
            Usercreation::class,
            'creator_user_id',
            'id'
        );
    }

    /**
     * Get the territories assigned to the user.
     */
    public function territories(): BelongsToMany
    {
        return $this->belongsToMany(Territory::class, 'territory_user')
            ->withPivot('assigned_by_user_id')
            ->withTimestamps();
    }

    /**
     * Get the users that were assigned by this user.
     */
    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'territory_user', 'assigned_by_user_id', 'user_id');
    }

    public function userTerritoryAssignmentStatus($territoryName)
    {
        if ($this->territories()->where('name', $territoryName)->exists()) {
            return true;
        }

        return false;
    }

    public function freights()
    {
        return $this->hasMany(Freight::class, 'creator_id');
    }

    public function lanes()
    {
        return $this->hasMany(Lane::class, 'creator_id');
    }

    // A company has many directors (through contacts)
    public function directors()
    {
        return $this->morphMany(Contact::class, 'contactable')->where('type', 'director');
    }

    // A company has many trade references (through contacts)
    public function traderefs()
    {
        return $this->morphMany(Contact::class, 'contactable')->where('type', 'traderef');
    }

    public function fleets()
    {
        return $this->hasMany(Fleet::class);
    }


    public function profileDocuments()
    {
        return $this->morphMany(Document::class, 'documentable')->where('document_type', 'company profile');
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'auditable');
    }    



    // This accessor generates the identification number based on your new format
    protected function identificationNumber(): Attribute
    {

        return Attribute::make(
            get: function () {
                // Ensure all relationships are loaded to prevent errors
                if ($this->roles->isEmpty() || $this->buslocation->isEmpty() || !$this->hasAnyRole(['shipper', 'carrier'])) return null;

                // 1. Get the country code (assuming 'ZW' for Zimbabwe)
                $countryCode = ($this->buslocation->first()->country === 'Zimbabwe') ? 'ZW' : 'SA';

                // 2. Get the city abbreviation
                if ($this->buslocation->first()?->country === 'Zimbabwe') {
                    $city = ZimbabweCity::where('name', $this->buslocation->first()->city)->first();
                    $cityAbbreviation = $city->abbreviation;
                } else {
                    $cityAbbreviation = 'ZA'; // Or a more specific code for South African cities
                }

                // 3. Get the carrier / shipper class code from the pivot table
                $class = ($this->roles->first()->pivot->classification === 'real_owner') ? '01' : '02';

                // 4. Get the year month and date
                $date = $this->created_at->format('ymd');

                // 5. Get the user ID, padded to three digits
                $paddedId = str_pad($this->id, 5, '0', STR_PAD_LEFT);

                // 6. Get the customer type suffix (C for Carrier, S for Shipper)
                $suffix = ($this->roles->first()->name === 'carrier') ? 'C' : 'S';

                // 7. Combine all parts into the final identification number
                return "{$countryCode}{$cityAbbreviation}{$class}{$date}{$paddedId}{$suffix}";
            },
        );
    }

    /**
     * Get the user's email masked with asterisks.
     */
    protected function maskedEmail(): Attribute
    {
        return Attribute::make(
            get: function () {
                $email = $this->email;
                if (!$email) return '';

                [$name, $domain] = explode('@', $email);

                // Show first  chars, then mask, then show the last char of the name
                $length = strlen($name);
                $visible = 4;

                if ($length <= 3) {
                    $maskedName = substr($name, 0, 1) . str_repeat('*', $length - 1);
                } else {
                    $maskedName = substr($name, 0, $visible) .
                        str_repeat('*', max(1, $length - ($visible + 1))) .
                        substr($name, -1);
                }

                return $maskedName . '@' . $domain;
            },
        );
    }

    /**
     * Shared logic for Policy, Notification, and Listener
     */
    public function getGeographicalBounds(): array
    {
        return cache()->remember("user_{$this->id}_bounds", 3600, function () {
            $territories = $this->territories()
                ->with(['countries', 'zimbabweCities', 'provinces.zimbabweCities'])
                ->get();

            $countries = $territories->flatMap->countries->pluck('name')->unique()
                ->reject(fn($n) => strtolower($n) === 'zimbabwe')->values()->toArray();

            $cities = $territories->flatMap->zimbabweCities->pluck('name')
                ->concat($territories->flatMap->provinces->flatMap->zimbabweCities->pluck('name'))
                ->unique()->values()->toArray();

            return compact('countries', 'cities');
        });
    }

/**
 * The "Master Filter": Limits a query to users this staff member is allowed to see.
 */
public function scopeVisibleTo(Builder $query, User $staff): Builder
{
    // 1. Superadmins/Admins see everyone
    if ($staff->hasAnyRole(['superadmin', 'admin'])) {
        return $query;
    }

    $bounds = $staff->getGeographicalBounds();
    $territoryIds = $staff->territories()->pluck('territories.id');

    return $query->where(function ($q) use ($staff, $bounds, $territoryIds) {
        // Logic A: Find Clients (Shippers/Carriers) in my territory
        $q->where(function ($clientQuery) use ($staff, $bounds) {
            $clientQuery->whereHas('roles', fn($r) => $r->whereIn('name', ['shipper', 'carrier']))
                ->where(function ($sub) use ($staff, $bounds) {
                    $sub->whereHas('createdBy', fn($cb) => $cb->where('user_creations.creator_user_id', $staff->id))
                        ->orWhereHas('buslocation', fn($bl) => 
                            $bl->whereIn('country', $bounds['countries'])
                               ->orWhereIn('city', $bounds['cities'])
                        );
                });
        });

        // Logic B: Find other Staff in my shared Territories
        $q->orWhere(function ($staffQuery) use ($territoryIds) {
            $staffQuery->whereHas('roles', fn($r) => $r->whereIn('name', [
                'marketing logistics associate', 
                'procurement logistics associate', 
                'operations logistics associate'
            ]))
            ->whereHas('territories', fn($t) => $t->whereIn('territories.id', $territoryIds));
        });
    });
}    

}
