<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Territory extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Get the users that are assigned to this territory.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'territory_user')
            ->withPivot('assigned_by_user_id')
            ->withTimestamps();
    }

    public function provinces()
    {
        return $this->belongsToMany(Province::class, 'province_territory')
                    ->withTimestamps();
    }

    public function zimbabweCities()
    {
        return $this->belongsToMany(zimbabweCity::class, 'territory_zimbabwe_city')
                    ->withTimestamps();
    }


    public function countries()
    {
        return $this->belongsToMany(Country::class, 'country_territory')
                    ->withTimestamps();
    }    
}
