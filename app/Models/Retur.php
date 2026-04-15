<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Retur extends Model
{
    protected $fillable = [
        'retur_number', 
        'goods_receipt_id', 
        'supplier_id', 
        'reason', 
        'status'
    ];

    // Relasi ke tabel detail (retur_items)
    public function items()
    {
        return $this->hasMany(ReturItem::class, 'retur_id');
    }

    // Relasi ke LPB
    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    // Relasi ke Supplier
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}