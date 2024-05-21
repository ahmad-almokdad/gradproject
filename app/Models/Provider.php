<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    use HasFactory;
    protected  $guarded = [];
    protected $hidden = ['password'];

    public function services()
    {
        return $this->belongsToMany(Service::class, 'services_providers');
    }
    public function orders()
    {
        return $this->hasMany(Order::class, 'provider_id');
    }
}
