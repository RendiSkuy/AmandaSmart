<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VrsSchedule extends Model
{
    protected $fillable = [
        'supplier_id', 'purchase_order_id', 'dc_id', 
        'scheduled_date', 'time_slot', 'gate_number', 'status'
    ];

    public function purchaseOrder() {
        return $this->belongsTo(PurchaseOrder::class);
    }
}