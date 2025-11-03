<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fleet extends Model
{
    protected $fillable = [
        'horses', 'trailer_qty','online'
    ];

    public function trailers()
    {
        return $this->belongsToMany(Trailer::class);
    }
}
