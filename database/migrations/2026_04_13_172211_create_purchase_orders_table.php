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
Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique(); // Contoh: PO-2026-0001
            
            // Relasi ke Supplier
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            
            // Relasi ke Distribution Center (DC) - Ini yang tadi menyebabkan error
            $table->foreignId('dc_id')->constrained('distribution_centers')->onDelete('cascade');
            
            $table->enum('type', ['bkl', 'dc'])->default('bkl'); // BKL (langsung) atau lewat DC
            $table->date('order_date');
            $table->date('expire_date');
            
            // Status PO: active, completed, or cancelled
            $table->string('status')->default('active');
            
            // Fitur Pelacakan untuk Supplier
            $table->boolean('is_read')->default(false);
            $table->timestamp('downloaded_at')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
