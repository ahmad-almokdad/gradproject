<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/*class FavoriteProvider extends Model
{
    protected $fillable = ['user_id' , 'provider_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function providers()
    {
        return $this->belongsTo(Provider::class);
    }
}

class Provider extends Model
{
    public function users()
    {
         return $this->belongsToMany(User::class, 'favorites');
    }
}

class User extends Model
{
    public function courses()
    {
         return $this->belongsToMany(Provider::class, 'favorites');
    }
}*/
class favorites extends Model
{
    use HasFactory;
    protected $table = "favorites";
    protected $fillable =[
        "user_id","provider_id",
    ];
    protected  $guarded = [];
    public $timestamps = false;
 
    public function provider_favorites(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Provider::class,"provider_id")->withCount(['orders'=>function ($query){
            $query->where('status','completed');
        }]);
    }
    public function users_favorites(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class,"user_id","id")->withDefault();
    }
    
}
