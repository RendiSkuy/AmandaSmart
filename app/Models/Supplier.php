<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = ['supplier_code', 'name', 'whatsapp_number'];

    // Relasi: Satu supplier memiliki banyak PO
    public function purchaseOrders() {
        return $this->hasMany(PurchaseOrder::class, 'selected_supplier_id');
    }

    // Relasi: Satu supplier memiliki banyak akun user/sales
    public function users() {
        return $this->hasMany(User::class, 'supplier_id');
    }
}