<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'supplier_id', 'plu_code', 'name', 'minor', 
        'max_stock', 'on_hand', 'unit_price', 'is_active'
    ];

    // Accessor untuk menghitung Qty PB secara otomatis sesuai rumus dosen
    // Jadi kamu bisa panggil $product->qty_pb langsung
    protected $appends = ['qty_pb'];
    
    public function getQtyPbAttribute()
    {
        $selisih = $this->max_stock - $this->on_hand;
        
        // Jika stok masih penuh atau melebihi max, tidak perlu pesan (0)
        if ($selisih <= 0) return 0;

        $jumlahDus = floor($selisih / $this->minor);
        return $jumlahDus * $this->minor;
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}