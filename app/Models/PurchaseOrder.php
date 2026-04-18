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
        'supplier_id',
        'dc_id',
        'type',
        'order_date',
        'expire_date',
        'status',
        'is_read',
        'downloaded_at',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expire_date' => 'date',
        'is_read' => 'boolean',
        'downloaded_at' => 'datetime',
    ];

    /**
     * RELASI: PO ini milik satu Supplier
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * RELASI: PO ditujukan ke satu Distribution Center (DC)
     */
    public function dc(): BelongsTo
    {
        return $this->belongsTo(DistributionCenter::class, 'dc_id');
    }

    /**
     * RELASI: PO memiliki banyak detail barang (Items)
     */
    public function items()
    {
        // Pastikan diarahkan ke PurchaseOrderItem
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id');
    }

    /**
     * RELASI: Satu PO biasanya menghasilkan satu LPB (Goods Receipt)
     */
    public function goodsReceipt(): HasOne
    {
        return $this->hasOne(GoodsReceipt::class);
    }

    /**
     * SCOPE: Mengambil PO yang belum dibaca supplier
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }
}