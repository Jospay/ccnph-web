<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('share_capital_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_share_capital_id')->constrained();
            $table->foreignId('status_id')->constrained();
            $table->unsignedInteger('installment_no');
            $table->unsignedBigInteger('amount');
            $table->date('due_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('share_capital_schedules');
    }
};
