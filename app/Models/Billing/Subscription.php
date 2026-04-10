<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;
use App\Models\Site;

class Subscription extends Model
{
    protected $fillable = ['site_id', 'plan_id', 'starts_at', 'expires_at', 'auto_renew'];
    protected $dates = ['starts_at', 'expires_at'];
    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    // Проверка на активность
    public function isActive()
    {
        return $this->expires_at->isFuture();
    }
}
