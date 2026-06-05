<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'plu_code', 'name', 'minor', 'max_stock', 'on_hand'
    ];

    // Accessor untuk menghitung Qty PB secara otomatis
    protected $appends = ['qty_pb'];
    
    public function getQtyPbAttribute()
    {
        $selisih = $this->max_stock - $this->on_hand;
        
        if ($selisih <= 0) return 0;

        $jumlahDus = floor($selisih / $this->minor);
        return $jumlahDus * $this->minor;
    }
}