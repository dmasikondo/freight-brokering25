<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class director extends Model
{
    public function contacts()
    {
        return $this->morphMany(Contact::class,'contactable');
    }
}
