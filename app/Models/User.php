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

    public function assignRole(string $roleName, ?string $ownershipType = null): void
    {
        $role = Role::where('name', $roleName)->firstOrFail();
        $data = [];
        if ($ownershipType) {
            $data['classification'] = $ownershipType;
        }
        $this->roles()->attach($role->id, $data);
    }  

    public function buslocation(): hasOne
    {
        return $this->hasOne(Buslocation::class);
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

                // 1. Get the country code (assuming 'ZW' for Zimbabwe)
                $countryCode = ($this->buslocation->country === 'Zimbabwe') ? 'ZW' : 'SA';

                // 2. Get the city abbreviation
                if ($this->buslocation->country === 'Zimbabwe') {
                    $city = ZimbabweCity::where('name', $this->buslocation->city)->first();
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
                $customerSuffix = ($this->roles->first()->name === 'Carrier') ? 'C' : 'S';

                // 7. Combine all parts into the final identification number
                return "{$countryCode}{$cityAbbreviation}{$classCode}{$yearMonth}{$paddedId}{$customerSuffix}";
            },
        );
    }
    
}
