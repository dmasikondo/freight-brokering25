<?php

namespace App\Models;

use App\Enums\LaneStatus;
use App\Enums\TrailerType;
use App\Enums\VehiclePositionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lane extends Model
{
    use HasFactory,  SoftDeletes;

    protected $fillable =[
        'destination',
        'location',
        'countryfrom',
        'cityfrom',
        'countryto',
        'cityto',
        'countryfrom',
        'trailer',
        'capacity',
        'rate',
        'availability_date',
        'status',
        'vehicle_status',        
    ];
			
    protected function casts(): array
    {
        return [
            'availability_date' => 'date',
            'status' =>LaneStatus::class,
            'vehicle_status' => VehiclePositionStatus::class,
            'trailer' =>TrailerType::class,
        ];
    } 

    public function contacts()
    {
        return $this->morphMany(Contact::class,'contactable');
    }    
}
