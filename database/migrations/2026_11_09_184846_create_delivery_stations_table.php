<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\DeliveryStationType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // hub = sorting centre, trucks run between hubs.
        // station = local branch for riders and seller drop-off, linked to one hub.
        Schema::create('delivery_stations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->string('type', 20)->default(DeliveryStationType::STATION->value); // hub, station
            $table->foreignId('delivery_hub_id')->nullable()->constrained('delivery_stations')->nullOnDelete();
            $table->foreignId('delivery_zone_id')->constrained();
            $table->string('address');
            $table->string('city');
            $table->string('province');
            $table->string('phone', 30)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_stations');
    }
};
