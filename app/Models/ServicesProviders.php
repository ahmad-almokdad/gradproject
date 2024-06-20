<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicesProviders extends Model
{
    use HasFactory;
    protected  $guarded = [];
    public function reviews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ServicesProviders::class,"provider_id","id");
    }
}


