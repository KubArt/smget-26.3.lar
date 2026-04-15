<?php

namespace App\Models\Crm;

use Illuminate\Database\Eloquent\Model;
use App\Models\Site;
use App\Models\User;

class LeadRoutingRule extends Model
{
    protected $fillable = [
        'site_id', 'name', 'conditions', 'assign_to_user_id',
        'set_stage_code', 'priority', 'is_active'
    ];

    protected $casts = [
        'conditions' => 'array',
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];

    public function site() { return $this->belongsTo(Site::class); }
    public function user() { return $this->belongsTo(User::class, 'assign_to_user_id'); }
}
