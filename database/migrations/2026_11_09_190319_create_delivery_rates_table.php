<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\DeliveryServiceType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // One row per origin zone -> destination zone -> service type.
        Schema::create('delivery_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_origin_zone_id')->constrained('delivery_zones');
            $table->foreignId('delivery_destination_zone_id')->constrained('delivery_zones');
            $table->string('service_type', 20)->default(DeliveryServiceType::STANDARD->value);
            $table->decimal('base_fee', 10, 2); // covers up to base_weight_grams
            $table->unsignedInteger('base_weight_grams')->default(1000);
            $table->unsignedInteger('step_weight_grams')->default(500); // extra weight is charged per started step
            $table->decimal('step_fee', 10, 2);
            $table->unsignedInteger('max_weight_grams')->default(50000);
            $table->unsignedInteger('volumetric_divisor')->default(5000); // L x W x H (cm) / divisor = kg
            $table->decimal('cod_fee_percent', 5, 2)->default(0);
            $table->decimal('cod_min_fee', 10, 2)->default(0);
            $table->decimal('insurance_percent', 5, 2)->default(0);
            $table->decimal('return_fee', 10, 2)->default(0);
            $table->unsignedTinyInteger('eta_min_days');
            $table->unsignedTinyInteger('eta_max_days');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['delivery_origin_zone_id', 'delivery_destination_zone_id', 'service_type'], 'rates_route_service_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_rates');
    }
};
