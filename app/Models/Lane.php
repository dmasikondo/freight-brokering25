<?php

namespace App\Models;

use App\Enums\CapacityUnit;
use App\Enums\LaneStatus;
use App\Enums\RateType;
use App\Enums\TrailerType;
use App\Enums\VehiclePositionStatus;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Lane extends Model
{
    use HasFactory,  SoftDeletes, Auditable;


    protected $fillable = [
        'destination',
        'location',
        'countryfrom',
        'cityfrom',
        'countryto',
        'cityto',
        'trailer',
        'capacity',
        'rate',
        'availability_date',
        'status',
        'vehicle_status',
        'carrier_id',
        'creator_id',
        'capacity_unit',
        'rate_type',
        'uuid',
    ];

    protected function casts(): array
    {
        return [
            'availability_date' => 'date',
            'status' => LaneStatus::class,
            'vehicle_status' => VehiclePositionStatus::class,
            'trailer' => TrailerType::class,
            'capacity_unit' => CapacityUnit::class,
            'rate_type' => RateType::class,
        ];
    }

    public $incrementing = true;
    protected $keyType = 'int';

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'carrier_id');
    }

    public function contacts(): MorphMany
    {
        return $this->morphMany(Contact::class, 'contactable');
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'auditable');
    }    

    protected static function booted()
    {
        static::retrieved(function ($lane) {
            // If an old record is accessed and has no UUID, give it one and save it
            if (!$lane->uuid) {
                $lane->uuid = (string) \Illuminate\Support\Str::uuid();
                $lane->save();
            }
        });
    }
}
