<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'product_id',
        'qty_po',
        'status',
        'selected_supplier_id',
        'delivery_deadline',
    ];

    protected $casts = [
        'delivery_deadline' => 'datetime',
    ];

    /**
     * RELASI: PO ini milik satu Supplier yang terpilih
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'selected_supplier_id');
    }

    /**
     * RELASI: PO ini memesan satu Product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * RELASI: Satu PO biasanya menghasilkan satu LPB (Goods Receipt)
     */
    public function goodsReceipt(): HasOne
    {
        return $this->hasOne(GoodsReceipt::class);
    }

    /**
     * RELASI: Satu PO dapat memiliki banyak penawaran (offers)
     */
    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }
}