<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Province extends Model
{
    use HasFactory;

    /**
     * Get the Zimbabwean cities for the province.
     */
    public function zimbabweCities(): HasMany
    {
        return $this->hasMany(ZimbabweCity::class);
    }

    /**
     * Get the territories associated with this province.
     */
    public function territories()
    {
        return $this->belongsToMany(Territory::class, 'province_territory')
                    ->withTimestamps();
    }
}
