<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\DeliveryVehicleType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('delivery_riders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_station_id')->constrained();
            $table->string('name');
            $table->string('phone', 30)->unique();
            $table->string('vehicle_type', 20)->default(DeliveryVehicleType::MOTORCYCLE->value);
            $table->string('plate_number', 20)->nullable();
            $table->char('api_token_hash', 64)->nullable()->unique(); // sha256 token for the rider app
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_riders');
    }
};
