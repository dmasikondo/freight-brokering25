<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

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
        'slug'
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
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->username)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
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
        return  (bool) $this->roles()->where('name',$role)->count();
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
    
    // This accessor generates the identification number based on your new format
    protected function identificationNumber(): Attribute
    {
        return Attribute::make(
            get: function () {
                // Ensure all relationships are loaded to prevent errors
                if (!$this->buslocation || $this->roles->isEmpty()) {
                    return null;
                }
                if(!$this->hasAnyRole(['shipper', 'carrier'])){
                    return null;
                }

                // 1. Get the country code (assuming 'ZW' for Zimbabwe)
                $countryCode = ($this->buslocation->first()->country === 'Zimbabwe') ? 'ZW' : 'SA';

                // 2. Get the city abbreviation
                if ($this->buslocation->first()?->country === 'Zimbabwe') {
                    $city = ZimbabweCity::where('name', $this->buslocation->first()->city)->first();
                    $cityAbbreviation = $city->abbreviation;
                } else {
                    $cityAbbreviation = 'ZA'; // Or a more specific code for South African cities
                }
                
                // 3. Get the carrier class code from the pivot table
                $ownershipType = $this->roles->first()->pivot->classification;
                $classCode = ($ownershipType === 'real_owner') ? '01' : '02';

                // 4. Get the year and month (YYMM format)
                $yearMonth = $this->created_at->format('ym');

                // 5. Get the user ID, padded to three digits
                $paddedId = str_pad($this->id, 3, '0', STR_PAD_LEFT);

                // 6. Get the customer type suffix (C for Carrier, S for Shipper)
                $customerSuffix = ($this->roles->first()->name === 'carrier') ? 'C' : 'S';

                // 7. Combine all parts into the final identification number
                return "{$countryCode}{$cityAbbreviation}{$classCode}{$yearMonth}{$paddedId}{$customerSuffix}";
            },
        );
    }
    
}
