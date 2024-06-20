<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    use HasFactory;
    protected $table = 'providers';
    protected  $guarded = [];
    protected $fillable = [
        'name' , 'phone' ,'email','address','status','rate', 'created_at' , 'updated_at'
    ];
    protected $hidden = ['password'];

    public function services()
    {
        return $this->belongsToMany(Service::class, 'services_providers');
    }

    public function favorites(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(favorites::class,"provider_id","id");
    }

    public function reviews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(review::class,"provider_id","id");
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