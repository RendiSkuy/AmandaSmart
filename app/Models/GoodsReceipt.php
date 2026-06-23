<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsReceipt extends Model
{
    protected $fillable = [
        'purchase_order_id', 'received_at', 'barcode'
    ];

    protected $casts = ['received_at' => 'datetime'];

    public function purchaseOrder() {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function details() {
        return $this->hasMany(GoodsReceiptDetail::class, 'goods_receipt_id');
    }

    public function ttf() {
        return $this->hasOne(Ttf::class);
    }

    public function returs() {
        return $this->hasMany(Retur::class);
    }
}