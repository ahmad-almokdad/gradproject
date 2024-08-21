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


    public function services()
    {
        return $this->belongsToMany(Service::class, 'services_providers');
    }
    public function orders()
    {
        return $this->hasMany(Order::class, 'provider_id');
    }

    public function favorites(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(favorites::class, "provider_id", "id");
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'provider_id');
    }

    public function reviews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Review::class, "provider_id", "id");
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

    public function scopeSelection($query)
    {
        $table = $this->getTable();
        $columns = $this->getTableColumns($table);

        return $query->select($columns);
    }

    protected function getTableColumns($table)
    {
        $columns = \DB::getSchemaBuilder()->getColumnListing($table);

        // Exclude sensitive columns
        $excludedColumns = ['password', 'email_verified_at', 'remember_token'];

        return array_diff($columns, $excludedColumns);
    }
}
