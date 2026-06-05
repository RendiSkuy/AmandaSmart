<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsReceipt extends Model
{
    protected $fillable = [
        'purchase_order_id', 'qty_received', 'received_at', 'barcode'
    ];

    protected $casts = ['received_at' => 'datetime'];

    public function purchaseOrder() {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function ttf() {
        return $this->hasOne(Ttf::class);
    }

    public function retur() {
        return $this->hasOne(Retur::class);
    }
}