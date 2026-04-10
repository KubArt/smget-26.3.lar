<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = ['name', 'description', 'code', 'amount', 'plan_id', 'expires_at', 'uses'];
    protected $dates = ['expires_at'];

    // Если ваучер сразу дает тариф
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}
