<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DistributionCenter extends Model
{
    use HasFactory;

    /**
     * Kolom yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'type',
        'address',
        'is_active',
    ];

    /**
     * Casting tipe data kolom.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relasi ke Purchase Orders.
     * Satu DC bisa menerima banyak PO.
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'dc_id');
    }

    /**
     * Relasi ke Goods Receipts (LPB).
     * Satu DC menjadi tempat penerimaan banyak barang (LPB).
     */
    public function goodsReceipts(): HasMany
    {
        return $this->hasMany(GoodsReceipt::class, 'dc_id');
    }

    /**
     * Scope untuk memfilter DC yang masih aktif saja.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}