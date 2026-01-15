<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Fleet extends Model
{
    use Auditable;

    protected $fillable = [
        'horses', 'trailer_qty','online'
    ];

    public function trailers()
    {
        return $this->belongsToMany(Trailer::class);
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'auditable');
    }    
}
