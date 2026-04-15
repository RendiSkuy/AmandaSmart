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
       Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->foreignId('supplier_id')->constrained();
    $table->string('plu_code')->unique(); // Kode PLU
    $table->string('name');
    $table->integer('minor')->default(1); // Untuk pembagi rumus PB
    $table->integer('max_stock')->default(0); // Batas atas stok
    $table->integer('on_hand')->default(0); // Stok saat ini (Stock Onhand)
    $table->decimal('unit_price', 15, 2);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
