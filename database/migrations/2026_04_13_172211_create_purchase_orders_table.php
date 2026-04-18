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
            
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            
            // Dibuat nullable agar bisa kosong saat alur BKL
            $table->foreignId('dc_id')->nullable()->constrained('distribution_centers')->onDelete('cascade');
            
            $table->enum('type', ['bkl', 'dc'])->default('dc'); 
            $table->date('order_date');
            $table->date('expire_date');
            $table->string('status')->default('active');
            $table->boolean('is_read')->default(false);
            $table->timestamp('downloaded_at')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};