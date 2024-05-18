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
}
