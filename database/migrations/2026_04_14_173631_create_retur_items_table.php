<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retur_items', function (Blueprint $table) {
            $table->id();
            // onDelete('cascade') agar jika header dihapus, detailnya ikut terhapus
            $table->foreignId('retur_id')->constrained('returs')->onDelete('cascade');
            $table->foreignId('product_id')->constrained();
            $table->integer('qty_retur');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retur_items');
    }
};