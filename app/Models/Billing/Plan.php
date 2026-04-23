<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = ['slug', 'name', 'description', 'price', 'duration_days', 'features', 'is_active'];
    protected $casts =
        [
            'features' => 'array'
        ];

    // Связь с подписками
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}
