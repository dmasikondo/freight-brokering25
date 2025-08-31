<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ZimbabweCity extends Model
{
    use HasFactory;

    protected $fillable =[
        'name',
        'province_id'
    ];

    /**
     * Get the province that owns the Zimbabwean city.
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    /**
     * Get the territories associated with this city.
     */

    public function territories()
    {
        return $this->belongsToMany(Territory::class, 'territory_zimbabwe_city')
                    ->withTimestamps();
    }    
}
