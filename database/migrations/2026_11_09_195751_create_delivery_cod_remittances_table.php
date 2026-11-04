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
        // Cash a rider hands to the station.
        Schema::create('delivery_cod_remittances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_rider_id')->constrained();
            $table->foreignId('delivery_station_id')->constrained();
            $table->decimal('expected_amount', 12, 2);
            $table->decimal('received_amount', 12, 2);
            $table->string('received_by')->nullable();
            $table->dateTime('remitted_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_cod_remittances');
    }
};
