<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cooperative_discounts', function (Blueprint $table) {
            $table->id();
            $table->string('module');
            $table->enum('type', ['Percentage', 'PHP']);
            $table->decimal('discount_value', 10, 4)->default(0);
            $table->timestamps();
            $table->unique('module');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cooperative_discounts');
    }
};
