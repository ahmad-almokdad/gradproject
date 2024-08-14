<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProviderStatistics extends Model
{
    protected $table = 'provider_statistics';

    protected $fillable = [
        'provider_id', 'month', 'year', 'profit',
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }
    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}