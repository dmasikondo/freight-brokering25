<?php

namespace App\Models;

use App\Enums\FreightStatus;
use App\Enums\PricingType;
use App\Enums\ShipmentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Freight extends Model
{
    /** @use HasFactory<\Database\Factories\FreightFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'dateto' => 'datetime',
            'datefrom' => 'datetime',
            'status' =>FreightStatus::class,
            'shipmentStatus' => ShipmentStatus::class,
            'pricingType' =>PricingType::class,
        ];
    }

    protected $fillable =[
        'name',
        'description',
        'weight',
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
        'vehicle_type',
        'distance',
        'is_read',
        'is_hazardous',
        'pickup_address',
        'delivery_address',
    ];

    public function goods(){
        return $this->belongsToMany(Good::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function contacts()
    {
        return $this->morphMany(Contact::class,'contactable');
    }



}
 