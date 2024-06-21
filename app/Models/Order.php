<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected  $guarded = [];

    public function images()
    {
        return $this->hasMany(OrderImage::class, 'order_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function provider()
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
    public function getImageUrlAttribute($value)
    {
        // the image inside public_path('images/services')
        if (!empty($value)) {
            // Assuming your images are stored in the 'images/services' directory
            return asset('images/orders/' . $value);
        }
        // If the value is empty, return a default or null value
        return null;
    }

      

}