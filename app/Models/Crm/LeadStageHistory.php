<?php

namespace App\Models\Crm;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class LeadStageHistory extends Model
{
    public $timestamps = false; // Используется только created_at через миграцию

    protected $fillable = [
        'lead_id', 'from_stage', 'to_stage', 'changed_by', 'comment'
    ];
    // Добавляем приведение типов
    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function lead() { return $this->belongsTo(Lead::class); }
    public function user() { return $this->belongsTo(User::class, 'changed_by'); }
}
