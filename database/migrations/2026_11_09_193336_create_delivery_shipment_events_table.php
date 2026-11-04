<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\DeliveryShipmentStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tracking history, append-only.
        Schema::create('delivery_shipment_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_shipment_id')->constrained()->cascadeOnDelete();
            $table->string('status', 30); // check enums
            $table->foreignId('delivery_station_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('delivery_rider_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('delivery_trip_id')->nullable()->constrained()->nullOnDelete();
            $table->string('note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['delivery_shipment_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_shipment_events');
    }
};
