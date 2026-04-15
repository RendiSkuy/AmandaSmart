<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsReceiptItem extends Model
{
    protected $fillable = [
        'goods_receipt_id', 'product_id', 'qty_ordered', 'qty_received', 'unit_price'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}