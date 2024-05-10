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
        // the image inside public_path('images/services')
        if (!empty($value)) {
            // Assuming your images are stored in the 'images/services' directory
            return asset('images/services/' . $value);
        }
        // If the value is empty, return a default or null value
        return null;
    }
}
