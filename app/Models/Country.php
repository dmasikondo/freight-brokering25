<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable =['name'];

    public function territories()
    {
        return $this->belongsToMany(Territory::class, 'country_territory')
                    ->withTimestamps();
    }    
}
