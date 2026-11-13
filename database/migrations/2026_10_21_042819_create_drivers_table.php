<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('contact_number')->unique();
            $table->string('password');
            $table->string('photo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('driver_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained()->cascadeOnDelete();
            $table->string('region');
            $table->string('region_code')->nullable();
            $table->string('province');
            $table->string('province_code')->nullable();
            $table->string('city');
            $table->string('city_code')->nullable();
            $table->string('barangay')->nullable();
            $table->string('barangay_code')->nullable();
            $table->timestamps();

            $table->index('city');
            $table->index(['driver_id', 'city']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_areas');
        Schema::dropIfExists('drivers');
    }
};
