<?php

namespace App\Models\Integration;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = ['name', 'slug', 'icon', 'description', 'instruction', 'is_active'];
}
