<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\LostFoundItemType;
use App\Enums\LostFoundItemCategory;
use App\Enums\LostFoundItemStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lost_found_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // check enums for value
            $table->string('item_name');
            $table->string('category'); // check enums value
            $table->text('description')->nullable();
            $table->date('date_lost_found');
            $table->string('location');
            $table->string('status')->default(LostFoundItemStatus::ACTIVE->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lost_found_items');
    }
};
