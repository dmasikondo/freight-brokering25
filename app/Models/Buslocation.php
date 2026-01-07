<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Buslocation extends Model
{
    use  Auditable;
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'country',
        'city',
        'address',
        'user_id',
    ]; 
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
        
    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'auditable');
    }  
    
    
}
