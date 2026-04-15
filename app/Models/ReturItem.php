<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturItem extends Model
{
    protected $table = 'retur_items'; // Pastikan nama tabel benar

    protected $fillable = [
        'retur_id', 
        'product_id', 
        'qty_retur'
    ];

    // Relasi balik ke Header
    public function retur()
    {
        return $this->belongsTo(Retur::class);
    }

    // Relasi ke Produk agar bisa tahu nama barangnya
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}