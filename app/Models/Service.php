<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;
    protected  $guarded = [];

    public function getServiceImageAttribute($value)
    {
        if (!empty($value)) {
            return asset('images/services/' . $value);
        }
        return null;
    }

    public function isCompletedByUser($userId)
    {
        // Check if the service has been marked as completed by the user
        return $this->users()->wherePivot('user_id', $userId)->wherePivot('completed', true)->exists();
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'services')->withPivot('completed');
    }
}
