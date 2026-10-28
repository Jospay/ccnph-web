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
        // Coverage: postal code -> pricing zone + the station that serves it.
        Schema::create('delivery_service_areas', function (Blueprint $table) {
            $table->id();
            $table->string('postal_code', 10)->unique();
            $table->string('city');
            $table->string('province');
            $table->foreignId('delivery_zone_id')->constrained();
            $table->foreignId('delivery_station_id')->constrained();
            $table->boolean('cod_enabled')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_service_areas');
    }
};
