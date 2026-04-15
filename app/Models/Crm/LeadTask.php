<?php

namespace App\Models\Crm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class LeadTask extends Model
{
    protected $fillable = [
        'lead_id', 'assigned_to', 'created_by', 'title',
        'description', 'due_date', 'reminder_at',
        'reminded_at', 'status', 'priority', 'completed_at'
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'reminder_at' => 'datetime',
        'reminded_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function lead(): BelongsTo { return $this->belongsTo(Lead::class); }
    public function assignedTo(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
