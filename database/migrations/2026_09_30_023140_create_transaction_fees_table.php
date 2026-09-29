<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_fees', function (Blueprint $table) {
            $table->id();
            $table->string('module');
            $table->enum('type', ['Percentage', 'PHP']);
            $table->decimal('transfer_fee', 10, 4);
            $table->decimal('minimum_fee', 10, 2)->default(1);
            $table->timestamps();
            $table->unique('module');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_fees');
    }
};
