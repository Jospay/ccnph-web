<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\DeliverySettlementStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // What you pay a client: COD collected minus fees.
        Schema::create('delivery_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_client_id')->constrained('delivery_api_clients');
            $table->string('reference', 30)->unique();
            $table->dateTime('cut_off_at');
            $table->decimal('cod_total', 12, 2);
            $table->decimal('fee_total', 12, 2);
            $table->decimal('net_amount', 12, 2); // negative means the client owes you
            $table->string('status', 20)->default(DeliverySettlementStatus::PENDING->value); // pending, paid
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['delivery_client_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_settlements');
    }
};
