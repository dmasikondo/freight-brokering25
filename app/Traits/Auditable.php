<?php

namespace App\Traits;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait Auditable 
{
    protected static function bootAuditable() 
    {
        static::updating(function ($model) {
            $changes = [];
            foreach ($model->getDirty() as $key => $newValue) {
                $oldValue = $model->getOriginal($key);
                
                // Sensitive or irrelevant fields to ignore
                if (in_array($key, ['password', 'remember_token', 'updated_at', 'created_at'])) continue;
                
                $changes[$key] = ['old' => $oldValue, 'new' => $newValue];
            }

            if (!empty($changes)) {
                ActivityLog::create([
                    'auditable_type' => get_class($model),
                    'auditable_id'   => $model->id,
                    'actor_id'       => Auth::id(),
                    'event'          => 'updated',
                    'payload'        => $changes,
                    'ip_address'     => request()->ip(),
                ]);
            }
        });
    }
}
