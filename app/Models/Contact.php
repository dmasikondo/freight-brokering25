<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contact extends Model
{
    /** @use HasFactory<\Database\Factories\FreightFactory> */
    use HasFactory;

    protected $fillable = [
        'full_name',
        'phone_number',
        'whatsapp',
        'email',
    ];

    public function contactable()
    {
        return $this->morphTo();
    }
}
