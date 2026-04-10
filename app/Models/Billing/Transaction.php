<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Transaction extends Model
{
    protected $fillable = ['user_id', 'amount', 'type', 'description', 'source_type', 'source_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Полиморфная связь, чтобы знать, чем вызвано пополнение (ваучером, счетом и т.д.)
    public function source()
    {
        return $this->morphTo();
    }
}
