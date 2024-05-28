<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;
    protected $table = "reviews";
    protected $fillable =[
        "provider_id","user_id","rate"
    ];
    public function provider_reviews(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Provider::class,"provider_id","id")->withDefault();
    }
    public function users_reviews(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class,"user_id","id")->withDefault();
    }
}