<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\UserAddressLabel;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('label')->default(UserAddressLabel::HOME->value);
            $table->string('recipient_name');
            $table->string('recipient_number');
            $table->string('region');
            $table->string('province')->nullable();
            $table->string('city');
            $table->string('barangay');
            $table->string('street');
            $table->string('unit_bldg_house');
            $table->string('postal_code', 20);
            $table->text('landmark')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_addresses');
    }
};