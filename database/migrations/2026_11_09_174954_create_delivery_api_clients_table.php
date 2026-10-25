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
        // Each system that uses this service (your shop, your other apps).
        Schema::create('delivery_api_clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->char('api_key_hash', 64)->unique(); // sha256 of the key; the plain key is never stored
            $table->string('webhook_url')->nullable();
            $table->text('webhook_secret')->nullable(); // encrypted by the model
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_api_clients');
    }
};
