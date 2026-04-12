<?php

namespace App\Models\Widgets;

use Illuminate\Database\Eloquent\Model;

class WidgetStatistic extends Model
{
    protected $fillable = [
        'widget_id', 'event_type', 'url', 'utm_source', 'utm_campaign',
        'utm_medium', 'utm_content', 'utm_term', 'ip', 'user_agent',
        'referer', 'query', 'session_id'
    ];
}
