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
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('checkout_id')->nullable()->unique()->after('payment_method_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('cancelled_amount')->default(0)->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('checkout_id');
            $table->dropColumn('cancelled_amount');
        });
    }
};
