<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function service()
    {
        return $this->belongsTo(Service::class,'service_id');
    }
    public function order()
    {
        return $this-> belongsTo(Order::class,'order_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');

    }
    public function provider()
    {
        return $this->belongsTo(Provider::class,'provider_id');
    }
}