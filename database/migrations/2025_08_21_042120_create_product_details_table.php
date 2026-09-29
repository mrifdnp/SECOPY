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
        Schema::create('product_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('product_id');
            $table->uuid('store_id');
            $table->uuid('variant_id')->nullable();
            $table->uuid('color_id')->nullable();
            $table->string('sku')->nullable();
            $table->float('price')->default(0);
            $table->integer('qty')->default(0);
            $table->float('weight')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->foreign('variant_id')->references('id')->on('variants')->onDelete('set null');
            $table->foreign('color_id')->references('id')->on('colors')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_details');
    }
};
