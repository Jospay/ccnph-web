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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('user_type_id')->after('id')->default(4)->constrained('user_types')->onDelete('restrict');
            $table->foreignId('status_id')->after('user_type_id')->default(1)->constrained('statuses')->onDelete('restrict');
            $table->boolean('is_seller')->default(false)->after('status_id');
            $table->string('phone', 20)->unique()->nullable()->after('email_verified_at');
            $table->timestamp('phone_verified_at')->nullable()->after('phone');
            $table->enum('gender', ['Male', 'Female', 'Other', 'Prefer not to say'])->nullable()->after('phone_verified_at');
            $table->date('birthdate')->nullable()->after('gender');
            $table->string('region')->nullable()->after('birthdate');
            $table->string('province')->nullable()->after('region');
            $table->string('city')->nullable()->after('province');
            $table->string('barangay')->nullable()->after('city');
            $table->string('street')->nullable()->after('barangay');
            $table->string('postal_code', 20)->nullable()->after('street');
            $table->string('avatar')->nullable()->after('postal_code');
            $table->enum('valid_id_type', [
                'National ID',
                'Passport',
                'Driver License',
                'PhilHealth ID',
                'SSS ID',
                'UMID',
                'Postal ID',
                'Voter ID',
                'TIN ID',
                'PAG-IBIG ID',
                'PRC ID',
                'Senior Citizen ID',
                'PWD ID',
                'OFW ID'
            ])->nullable()->after('avatar');
            $table->string('valid_id_number', 20)->unique()->nullable()->after('valid_id_type');
            $table->string('front_valid_id_picture')->nullable()->after('valid_id_number');
            $table->string('back_valid_id_picture')->nullable()->after('front_valid_id_picture');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['user_type_id']);
            $table->dropColumn([
                'user_type_id',
                'status_id',
                'phone',
                'phone_verified_at',
                'gender',
                'region',
                'province',
                'city',
                'barangay',
                'street',
                'postal_code',
                'avatar',
                'valid_id_type',
                'valid_id_number',
                'front_valid_id_picture',
                'back_valid_id_picture',
            ]);
        });
    }
};
