<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('revenue_breakdowns', 'cooperative_id')) {
            return;
        }

        Schema::table('revenue_breakdowns', function (Blueprint $table) {
            $table->foreignId('cooperative_id')
                ->nullable()
                ->after('allocation_service_id')
                ->constrained('cooperatives')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('revenue_breakdowns', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cooperative_id');
        });
    }
};
