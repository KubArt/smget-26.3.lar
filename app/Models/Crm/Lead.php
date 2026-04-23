<?php

namespace App\Models\Crm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Site;
use App\Models\User;
use App\Models\Widget;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lead extends Model
{
    protected $fillable = [
        'site_id', 'client_id', 'widget_id', 'phone', 'email',
        'form_data', 'status', 'assigned_to', 'vaucher_name',
        'vaucher_code', 'vaucher_end_date', 'vaucher_is_active',
        'description', 'tag', 'utm_source', 'utm_campaign',
        'utm_medium', 'utm_term', 'utm_content', 'utm_referrer',
        'page_url', 'ip_address', 'user_agent'
    ];

    protected $casts = [
        'form_data' => 'array',
        'vaucher_end_date' => 'datetime',
        'vaucher_is_active' => 'boolean',
        'tag' => 'integer',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function widget(): BelongsTo
    {
        return $this->belongsTo(Widget::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Текущая стадия воронки (связь по коду статуса)
     */
    public function funnelStage(): BelongsTo
    {
        return $this->belongsTo(FunnelStage::class, 'status', 'code')
            ->where('site_id', $this->site_id);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(LeadNote::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(LeadTask::class);
    }

    public function stageHistories()
    {
        return $this->hasMany(LeadStageHistory::class, 'lead_id');
    }
    // В файле Lead.php добавьте связь:
    public function prize(): HasOne
    {
        return $this->hasOne(Prize::class, 'lead_id');
    }
}
