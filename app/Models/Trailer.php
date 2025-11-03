<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\TrailerType;

class Trailer extends Model
{
    protected $fillable =['name'];

    protected function casts(): array
    {
        return [
            'name' =>TrailerType::class,
        ];
    }    
}

