<?php

// database/migrations/xxxx_xx_xx_add_deletion_fields_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('deletion_requested_at')->nullable();
            $table->timestamp('scheduled_deletion_at')->nullable();
            $table->string('deletion_verification_request_id')->nullable();
            $table->timestamp('deletion_otp_sent_at')->nullable();
            $table->string('deletion_token')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'deletion_requested_at',
                'scheduled_deletion_at',
                'deletion_verification_request_id',
                'deletion_otp_sent_at',
                'deletion_token',
            ]);
        });
    }
};
