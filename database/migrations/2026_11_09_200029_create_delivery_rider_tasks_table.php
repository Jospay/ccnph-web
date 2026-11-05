<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\DeliveryRiderTaskType;
use App\Enums\DeliveryRiderTaskStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // One job for one rider. A rider's open tasks are their run sheet.
        // Every delivery attempt gets its own row, so the attempt history is kept.
        Schema::create('delivery_rider_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_rider_id')->constrained();
            $table->foreignId('delivery_shipment_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20); // pickup, delivery, return
            $table->string('status', 20)->default(DeliveryRiderTaskStatus::ASSIGNED->value); // assigned, completed, failed, cancelled
            $table->unsignedTinyInteger('attempt_no')->default(1);
            $table->string('failure_reason')->nullable();
            $table->string('received_by')->nullable();
            $table->string('pod_photo_url')->nullable(); // proof of delivery
            $table->decimal('cod_collected', 12, 2)->default(0);
            $table->foreignId('delivery_cod_remittance_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['delivery_rider_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_rider_tasks');
    }
};
