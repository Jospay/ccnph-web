<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cooperative_registers', function (Blueprint $table) {
            $table->id();

            // Cooperative details
            $table->string('name');
            $table->string('acronym')->nullable();
            $table->string('logo_path');
            $table->string('cda_registration_number')->index();
            $table->string('type');
            $table->string('cda_certificate_path');
            $table->string('email');
            $table->string('contact', 20);

            // Office address
            $table->string('region');
            $table->string('province');
            $table->string('city');
            $table->string('barangay');
            $table->string('street_address')->nullable();
            $table->string('zip_code', 10)->nullable();

            // Primary leader (kept here until the phone is verified, then a User is created)
            $table->string('leader_first_name');
            $table->string('leader_last_name');
            $table->string('leader_position');
            $table->string('leader_email');
            $table->string('leader_phone', 20);
            $table->string('leader_password')->nullable(); // hashed; cleared once the User exists

            // Phone verification (mirrors the account-deletion flow)
            $table->string('verification_request_id')->nullable();
            $table->timestamp('otp_sent_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();

            // pending_verification -> pending_approval -> approved | rejected
            $table->string('status', 30)->default('pending_verification')->index();

            $table->foreignId('leader_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cooperative_registers');
    }
};
