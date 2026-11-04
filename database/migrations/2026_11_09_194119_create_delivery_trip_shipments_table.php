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
        // Which shipments are assigned to which delivery trips (which parcels are on which trucks).
        Schema::create('delivery_trip_shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_trip_id')->constrained()->cascadeOnDelete();
            $table->foreignId('delivery_shipment_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['delivery_trip_id','delivery_shipment_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_trip_shipments');
    }
};
