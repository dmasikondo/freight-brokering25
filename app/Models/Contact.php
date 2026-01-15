<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Contact extends Model
{
    /** @use HasFactory<\Database\Factories\FreightFactory> */
    use HasFactory, Auditable;

    protected $fillable = [
        'full_name',
        'phone_number',
        'whatsapp',
        'email',
        'address',
        'city',
        'country',
        'type',     
    ];

    public function contactable()
    {
        return $this->morphTo();
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'auditable');
    }    
}
