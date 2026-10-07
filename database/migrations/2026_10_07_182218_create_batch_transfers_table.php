<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batch_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->nullable()->constrained()->nullOnDelete();
            $table->string('paymongo_batch_id')->nullable();
            $table->string('paymongo_transfer_id')->nullable();
            $table->string('source_account_number')->nullable();
            $table->string('destination_account_number')->nullable();
            $table->string('destination_account_name')->nullable();
            $table->string('destination_account_bic')->nullable();
            $table->string('channel')->nullable();
            $table->decimal('amount', 15, 2);
            $table->decimal('fee', 15, 2)->default(0);
            $table->string('provider')->nullable();
            $table->string('purpose')->nullable();
            $table->text('remarks')->nullable();
            $table->string('reference_number')->unique();
            $table->string('status')->default('pending');
            $table->string('failure_code')->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_transfers');
    }
};
