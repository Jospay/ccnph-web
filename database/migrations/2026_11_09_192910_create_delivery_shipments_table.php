<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\DeliveryServiceType;
use App\Enums\DeliveryPickupType;
use App\Enums\DeliveryShipmentStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // The parcel. Sender and recipient are copied here, so later address
        // changes in the shop never alter a parcel that was already booked.
        Schema::create('delivery_shipments', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_number', 30)->unique();
            $table->foreignId('delivery_client_id')->constrained('delivery_api_clients');
            $table->string('client_reference', 100); // the shop's order number
            $table->foreignId('delivery_rate_id')->nullable()->constrained()->nullOnDelete();
            $table->string('service_type', 20)->default(DeliveryServiceType::STANDARD->value);
            $table->string('pickup_type', 20)->default(DeliveryPickupType::PICKUP->value); // pickup, dropoff
            $table->string('status', 30)->default(DeliveryShipmentStatus::BOOKED->value);
            $table->boolean('is_returning')->default(false);

            $table->string('sender_name');
            $table->string('sender_phone', 30);
            $table->string('sender_address');
            $table->string('sender_postal_code', 10);
            $table->string('recipient_name');
            $table->string('recipient_phone', 30);
            $table->string('recipient_address');
            $table->string('recipient_postal_code', 10);
            $table->string('item_description')->nullable();

            $table->unsignedInteger('weight_grams');
            $table->unsignedSmallInteger('length_cm')->nullable();
            $table->unsignedSmallInteger('width_cm')->nullable();
            $table->unsignedSmallInteger('height_cm')->nullable();
            $table->unsignedInteger('chargeable_weight_grams');

            $table->decimal('declared_value', 12, 2)->default(0);
            $table->decimal('cod_amount', 12, 2)->default(0);
            $table->decimal('shipping_fee', 10, 2);
            $table->decimal('cod_fee', 10, 2)->default(0);
            $table->decimal('insurance_fee', 10, 2)->default(0);
            $table->decimal('return_fee', 10, 2)->default(0); // only charged if the parcel is returned
            $table->decimal('total_fee', 10, 2);

            $table->foreignId('delivery_origin_station_id')->constrained('delivery_stations');
            $table->foreignId('delivery_destination_station_id')->constrained('delivery_stations');
            $table->foreignId('delivery_current_station_id')->nullable()->constrained('delivery_stations')->nullOnDelete();
            $table->json('route'); // ordered station ids, e.g. [12, 3, 7, 25]

            $table->unsignedTinyInteger('delivery_attempts')->default(0);
            $table->unsignedTinyInteger('max_delivery_attempts')->default(2);
            $table->date('eta_min_date')->nullable();
            $table->date('eta_max_date')->nullable();
            $table->timestamp('cod_remitted_at')->nullable();
            $table->timestamp('completed_at')->nullable(); // delivered, returned, cancelled or lost
            $table->foreignId('delivery_settlement_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['delivery_client_id', 'client_reference']); // booking twice returns the same parcel
            $table->index(['status', 'delivery_current_station_id']);
            $table->index(['delivery_client_id', 'status', 'delivery_settlement_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_shipments');
    }
};
