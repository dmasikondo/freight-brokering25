<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

}



