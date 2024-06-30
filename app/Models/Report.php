<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;
    protected $table = "reports";
    protected $fillable =[
        "provider_id","user_id","report"
    ];
    public function facilities_reports(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Provider::class,"provider_id","id")->withDefault();
    }
    public function users_reports(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class,"user_id","id")->withDefault();
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }
    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}