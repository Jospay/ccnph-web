<?php

use App\Enums\ProductValidationServiceCategory;
use App\Enums\ProductValidationServiceGoal;
use App\Enums\ProductValidationServiceStatus;
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
        Schema::create('product_validation_services', function (Blueprint $table) {
            $table->id();
            $table->string('product_name');
            $table->string('product_category')->default(ProductValidationServiceCategory::SAAS->value);
            $table->text('product_description');
            $table->string('validation_goal')->default(ProductValidationServiceGoal::PRODUCT_MARKET_FIT->value);
            $table->string('target_market');
            $table->string('status')->default(ProductValidationServiceStatus::PENDING->value);
            $table->decimal('estimated_price', 12, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_validation_services');
    }
};
