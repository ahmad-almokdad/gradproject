<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Provider extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;
    protected  $guarded = [];
    protected $hidden = ['password'];
    public function scopeSelection($query)
    {
        return $query->select('id','name' , 'phone' ,'email','address','status', 'created_at' , 'updated_at');
    }
    public function services()
    {
        return $this->belongsToMany(Service::class, 'services_providers');
    }
    public function orders()
    {
        return $this->hasMany(Order::class, 'provider_id');
    }
    public function provider_favorites(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            "favorites",
            "user_id",
            "provider_id",
            "id",
        //     "id",
        );
    }
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
