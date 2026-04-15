<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsReceipt extends Model
{
    protected $fillable = [
        'lpb_number', 'purchase_order_id', 'supplier_id', 
        'dc_id', 'status', 'is_read', 'received_at'
    ];

    protected $casts = ['received_at' => 'datetime', 'is_read' => 'boolean'];

    public function purchaseOrder() {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function distributionCenter()
{
    // Pastikan foreign key-nya adalah dc_id sesuai migration kamu
    return $this->belongsTo(DistributionCenter::class, 'dc_id');
}
// Tambahkan ini agar bisa menarik data barang yang diterima
    public function items()
    {
        // Relasi ke tabel detail barang (Pastikan model GoodsReceiptItem sudah dibuat)
        return $this->hasMany(GoodsReceiptItem::class, 'goods_receipt_id');
    }

    // Tambahkan juga relasi ke Supplier (opsional tapi penting)
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    public function ttf() {
        return $this->hasOne(Ttf::class);
    }
}