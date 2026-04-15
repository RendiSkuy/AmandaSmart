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
    Schema::create('distribution_centers', function (Blueprint $table) {
    $table->id();
    $table->string('code')->unique(); // Contoh: DC01
    $table->string('name');
    $table->enum('type', ['dc', 'toko']); // DC atau Toko Amanda
    $table->string('address')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distribution_centers');
    }
};
