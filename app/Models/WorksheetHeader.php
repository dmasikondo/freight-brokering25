<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorksheetHeader extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'is_completed',
    ];

    /**
     * Get the entries associated with this named worksheet.
     */
    public function entries(): HasMany
    {
        return $this->hasMany(WorksheetEntry::class, 'header_id')
            ->orderBy('sort_order', 'asc');
    }

    /**
     * The user who created this worksheet.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sharedWith()
    {
        return $this->belongsToMany(User::class, 'worksheet_header_user')->withTimestamps();
    }
}
