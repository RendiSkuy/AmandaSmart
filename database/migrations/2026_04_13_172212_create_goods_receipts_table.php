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
    Schema::create('goods_receipts', function (Blueprint $table) {
    $table->id();
    $table->string('lpb_number')->unique(); // Nomor LPB
    $table->foreignId('purchase_order_id')->constrained();
    $table->foreignId('supplier_id')->constrained();
    $table->foreignId('dc_id')->constrained('distribution_centers');
    $table->enum('status', ['received', 'cancelled'])->default('received');
    $table->boolean('is_read')->default(false); // Flag untuk supplier
    $table->timestamp('received_at');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goods_receipts');
    }
};
