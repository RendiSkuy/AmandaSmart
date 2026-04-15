<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PoItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'qty_pb',
        'qty_ordered',
        'unit_price',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'qty_pb' => 'integer',
        'qty_ordered' => 'integer',
    ];

    /**
     * RELASI: Detail item ini merujuk ke satu kepala PO
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * RELASI: Detail item ini adalah produk tertentu
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * ACCESSOR: Menghitung subtotal per item (qty x price)
     */
    public function getSubtotalAttribute()
    {
        return $this->qty_ordered * $this->unit_price;
    }
}