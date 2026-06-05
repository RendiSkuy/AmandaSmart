<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Retur extends Model
{
    protected $fillable = [
        'goods_receipt_id', 
        'product_id', 
        'qty_retur', 
        'reason'
    ];

    // Relasi ke LPB
    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    // Relasi ke Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}