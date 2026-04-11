<?php

namespace App\Models\Widgets;

use Illuminate\Database\Eloquent\Model;

class WidgetType extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'category',
        'manifest',
        'is_active',
        'is_free'
    ];

    // Также не забудь про cast для JSON поля manifest
    protected $casts = [
        'manifest' => 'array',
        'is_active' => 'boolean',
        'is_free' => 'boolean',
    ];
}
