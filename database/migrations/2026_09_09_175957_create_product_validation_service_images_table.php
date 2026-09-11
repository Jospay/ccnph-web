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
        Schema::create('product_validation_service_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_validation_service_id')
                ->constrained(indexName: 'pvs_images_service_id_foreign')
                ->cascadeOnDelete();
            $table->string('image');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_validation_service_images');
    }
};
