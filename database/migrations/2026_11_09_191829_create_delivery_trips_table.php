<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\DeliveryTripStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // One truck run between two stations or hubs.
        Schema::create('delivery_trips', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->foreignId('delivery_truck_id')->constrained('delivery_trucks');
            $table->foreignId('delivery_origin_station_id')->constrained('delivery_stations');
            $table->foreignId('delivery_destination_station_id')->constrained('delivery_stations');
            $table->string('driver_name');
            $table->string('driver_phone', 30)->nullable();
            $table->string('status', 20)->default(DeliveryTripStatus::PLANNED->value); // planned, in_transit, arrived
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('departed_at')->nullable();
            $table->timestamp('arrived_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'delivery_origin_station_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_trips');
    }
};
