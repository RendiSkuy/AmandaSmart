<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VrsSchedule extends Model
{
    protected $fillable = [
        'purchase_order_id', 'scheduled_date', 'time_slot', 'status', 'actual_arrival_at'
    ];

    protected $casts = [
        'actual_arrival_at' => 'datetime',
    ];

    public function purchaseOrder() {
        return $this->belongsTo(PurchaseOrder::class);
    }
}