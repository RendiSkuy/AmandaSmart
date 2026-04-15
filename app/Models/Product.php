<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'plu_code',
        'name',
        'minor',
        'max_stock',
        'on_hand',
        'unit_price',
        'is_active',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'is_active' => 'boolean',
        'minor' => 'integer',
        'max_stock' => 'integer',
        'on_hand' => 'integer',
    ];

    /**
     * RELASI: Produk ini milik seorang Supplier
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * RELASI: Produk ini bisa muncul di banyak detail PO
     */
    public function poItems(): HasMany
    {
        return $this->hasMany(PoItem::class);
    }

    /**
     * ACCESSOR: Menghitung Qty PB secara otomatis
     * Panggil di controller dengan: $product->qty_pb
     */
    public function getQtyPbAttribute(): int
    {
        // Jika stok saat ini masih di bawah batas maksimal
        if ($this->on_hand < $this->max_stock) {
            $selisih = $this->max_stock - $this->on_hand;
            
            // Rumus: floor(selisih / minor) * minor
            $hasil = floor($selisih / $this->minor) * $this->minor;
            
            return (int) $hasil;
        }

        return 0; // Stok masih cukup, tidak perlu pesan
    }

    /**
     * SCOPE: Filter produk milik supplier tertentu
     */
    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }
}