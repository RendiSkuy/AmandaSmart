<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ttf extends Model
{
    protected $table = 'ttf'; // Karena nama tabel singkat, pastikan manual
    protected $fillable = [
        'ttf_number', 'goods_receipt_id', 'supplier_id', 
        'due_date', 'total_amount', 'total_deductions', 'net_amount', 'status'
    ];

    protected $casts = ['due_date' => 'date'];

    public function supplier() {
        return $this->belongsTo(Supplier::class);
}
    public function goodsReceipt() {
        return $this->belongsTo(GoodsReceipt::class);
    }
}