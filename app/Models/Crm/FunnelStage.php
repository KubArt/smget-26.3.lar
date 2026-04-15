<?php

namespace App\Models\Crm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Site;

class FunnelStage extends Model
{
    protected $fillable = [
        'site_id', 'name', 'code', 'sort_order',
        'color', 'is_system', 'probability'
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'sort_order' => 'integer',
        'probability' => 'integer',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'status', 'code');
    }
}
