<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('qty_po');
            $table->string('status')->default('pending');
            $table->foreignId('selected_supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');
            $table->timestamp('delivery_deadline')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};