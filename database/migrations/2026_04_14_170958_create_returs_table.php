<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('returs', function (Blueprint $table) {
            $table->id();
            $table->string('retur_number')->unique();
            $table->foreignId('goods_receipt_id')->constrained('goods_receipts'); // Merujuk ke LPB mana
            $table->foreignId('supplier_id')->constrained();
            $table->text('reason'); // Alasan pengembalian
            $table->enum('status', ['pending', 'shipped', 'completed'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('returs');
    }
};