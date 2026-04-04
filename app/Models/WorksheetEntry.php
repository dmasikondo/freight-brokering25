<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorksheetEntry extends Model
{
    // No timestamps on entries to keep the table light, 
    // but you can enable them if you want 'created_at' for each task.
    public $timestamps = false;

    protected $fillable = [
        'header_id',
        'partner_name',
        'contact_details',
        'activity',
        'feedback',
        'way_forward',
        'started_at',
        'completed_at',
        'sort_order',
        'reminder_at',
        'private_notes',
        'partner_type'
    ];
  
    protected $casts = [
        'partner_type' => \App\Enums\PartnerType::class,
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'reminder_at' => 'datetime',
    ];

    /**
     * The parent worksheet header.
     */
    public function header(): BelongsTo
    {
        return $this->belongsTo(WorksheetHeader::class, 'header_id');
    }
}
