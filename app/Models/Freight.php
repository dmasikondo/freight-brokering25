<?php

namespace App\Models;

use App\Enums\FreightStatus;
use App\Enums\PricingType;
use App\Enums\ShipmentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\CapacityUnit; // Ensure this exists for casting
use App\Enums\RateType;     // Ensure this exists for casting
use App\Enums\TrailerType;  // Ensure this exists for vehicle_type
use App\Traits\Auditable;   // Import the trait
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;

class Freight extends Model
{
    /** @use HasFactory<\Database\Factories\FreightFactory> */
    use HasFactory, SoftDeletes, Auditable;

    protected function casts(): array
    {
        return [
            'dateto' => 'datetime',
            'datefrom' => 'datetime',
            'status' => FreightStatus::class,
            'shipment_status' => ShipmentStatus::class,
            'payment_option' => PricingType::class,

            'vehicle_type' => TrailerType::class,      // Cast to Enum to match Lane Specs
            'capacity_unit' => CapacityUnit::class,    
            'rate_type' => RateType::class,
            'weight' => 'float',
        ];
    }

    protected $fillable = [
        'uuid',
        'shipper_id',
        'creator_id',
        'publisher_id',
        'name',
        'description',
        'weight',
        'capacity_unit',
        'cityfrom',
        'cityto',
        'countryfrom',
        'countryto',
        'datefrom',
        'dateto',
        'status',
        'shipment_status',
        'payment_option',
        'budget',
        'carriage_rate',
        'rate_type',
        'vehicle_type',
        'distance',
        'is_read',
        'is_hazardous',
        'pickup_address',
        'delivery_address',
    ];

    protected function vehicleType(): Attribute
    {
        return Attribute::make(
            get: fn($value) => TrailerType::fromValue($value),
            set: fn($value) => $value instanceof TrailerType ? $value->value : $value,
        );
    }

    public function goods(): BelongsToMany
    {
        return $this->belongsToMany(Good::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function shipper(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shipper_id');
    }

    public function contacts(): MorphMany
    {
        return $this->morphMany(Contact::class, 'contactable');
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'auditable');
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }
}
