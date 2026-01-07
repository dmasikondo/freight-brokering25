<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'auditable_type', 
        'auditable_id', 
        'actor_id', 
        'event', 
        'payload', 
        'ip_address'
    ];

    // CRITICAL: This prevents the "Array to string conversion" error.
    protected $casts = [
        'payload' => 'array', 
    ];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

}



