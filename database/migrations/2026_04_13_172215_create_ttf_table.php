<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ttf', function (Blueprint $table) {
    $table->id();
    $table->string('ttf_number')->unique();
    $table->foreignId('goods_receipt_id')->constrained('goods_receipts'); // Relasi ke LPB
    $table->foreignId('supplier_id')->constrained();
    $table->date('due_date'); // Jatuh tempo pembayaran
    $table->decimal('total_amount', 15, 2);
    $table->decimal('total_deductions', 15, 2)->default(0); // Potongan (pinalti SL/retur)
    $table->decimal('net_amount', 15, 2); // Nilai bersih yang dibayar
    $table->enum('status', ['pending', 'verified', 'paid', 'overdue'])->default('pending');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ttf');
    }
};
