<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tblcategory', function (Blueprint $table) {
            $table->id();
            $table->string('CategoryName');
            $table->timestamps();
        });

        Schema::create('tblsubcategory', function (Blueprint $table) {
            $table->id('SubCategoryId');
            $table->string('Subcategory');
            $table->timestamps();
        });

        Schema::create('tblposts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('CategoryId')->nullable()->constrained('tblcategory')->nullOnDelete();
            $table->unsignedBigInteger('SubCategoryId')->nullable();
            $table->string('PostTitle');
            $table->string('PostUrl')->nullable();
            $table->longText('PostDetails');
            $table->string('PostImage')->nullable();
            $table->timestamp('postingdate')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tblposts');
        Schema::dropIfExists('tblsubcategory');
        Schema::dropIfExists('tblcategory');
    }
};