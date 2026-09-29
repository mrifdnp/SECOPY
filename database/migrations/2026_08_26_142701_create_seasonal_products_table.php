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
        Schema::create('seasonal_products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('seasonal_id')->index();
            $table->uuid('product_id')->index();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('seasonal_id')->references('id')->on('seasonals')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seasonal_products');
    }
};
