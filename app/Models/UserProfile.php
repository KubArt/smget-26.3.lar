<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model

{
    protected $fillable = ['user_id', 'name', 'patronymic', 'last_name', 'avatar', 'additional_info'];

    protected $casts = [
        'additional_info' => 'array', // Авто-преобразование JSON в массив
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
