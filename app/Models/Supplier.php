<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = ['supplier_code', 'name', 'status', 'email'];

    // Relasi: Satu supplier memiliki banyak produk (untuk rumus PB)
    public function products() {
        return $this->hasMany(Product::class);
    }

    // Relasi: Satu supplier memiliki banyak PO
    public function purchaseOrders() {
        return $this->hasMany(PurchaseOrder::class);
    }

    // Relasi: Satu supplier memiliki banyak user (akun staf supplier)
    public function users() {
        return $this->hasMany(User::class);
    }
}