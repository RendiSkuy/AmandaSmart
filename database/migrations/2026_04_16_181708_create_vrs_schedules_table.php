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
Schema::create('vrs_schedules', function (Blueprint $table) {
    $table->id();
    $table->foreignId('supplier_id')->constrained();
    $table->foreignId('purchase_order_id')->constrained();
    $table->foreignId('dc_id')->constrained('distribution_centers');
    $table->date('scheduled_date');
    $table->string('time_slot'); // Contoh: "09:00 - 10:00"
    $table->string('gate_number')->nullable(); // Ditentukan Admin Gudang
    $table->enum('status', ['booked', 'arrived', 'cancelled', 'completed'])->default('booked');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vrs_schedules');
    }
};
