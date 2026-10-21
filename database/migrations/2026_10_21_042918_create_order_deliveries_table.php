<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained()->restrictOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('status')->default('assigned');
            $table->unsignedSmallInteger('attempts')->default(0);

            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();

            $table->string('failure_reason', 500)->nullable();
            $table->decimal('cod_amount_collected', 12, 2)->nullable();
            $table->string('proof_photo')->nullable();
            $table->string('remarks', 500)->nullable();

            $table->timestamps();

            $table->index(['driver_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_deliveries');
    }
};
