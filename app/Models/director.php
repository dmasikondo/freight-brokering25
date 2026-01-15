<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Traits\Auditable;

class director extends Model
{
    use  Auditable; 

    public function contacts()
    {
        return $this->morphMany(Contact::class,'contactable');
    }
}
