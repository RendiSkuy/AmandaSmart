<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ttf extends Model
{
    protected $table = 'ttf'; // Karena nama tabel singkat, pastikan manual
    protected $fillable = [
        'goods_receipt_id', 'total_amount', 'total_deductions', 'status_payment'
    ];

    public function goodsReceipt() {
        return $this->belongsTo(GoodsReceipt::class);
    }
}