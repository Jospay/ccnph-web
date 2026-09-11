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

            $table->foreignId('product_validation_service_id');

            $table->string('image');

            $table->timestamps();

            $table->foreign(
                'product_validation_service_id',
                'pvsi_service_id_foreign'
            )->references('id')
             ->on('product_validation_services')
             ->cascadeOnDelete();
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
