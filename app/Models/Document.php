<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'filename',
        'disk_path',
        'comment',
        'document_type',    
    ];

    public function documentable()
    {
        return $this->morphTo();
    }    
}
