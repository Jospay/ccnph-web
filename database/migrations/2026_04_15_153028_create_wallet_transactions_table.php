<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->nullable()->constrained()->cascadeOnDelete();

            $table->nullableMorphs('reference');
            $table->string('reference_number')->nullable()->unique();
            $table->enum('type', ['deposit', 'withdrawal', 'credit', 'debit', 'membership_payment']);

            // Transfer amount and fee (total deducted = amount + transfer_fee)
            $table->decimal('amount', 15, 2);
            $table->decimal('transfer_fee', 15, 2)->default(0);

            // From / To details (used by transfers)
            $table->string('from_name')->nullable();
            $table->string('from_account_number')->nullable();
            $table->string('to_account_name')->nullable();
            $table->string('to_account_number', 50)->nullable();
            $table->string('to_provider')->nullable();

            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
