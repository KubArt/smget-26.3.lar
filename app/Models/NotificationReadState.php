<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationReadState extends Model
{
    protected $fillable = [
        'user_id',
        'notification_id'
    ];

    // Связь с пользователем
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
