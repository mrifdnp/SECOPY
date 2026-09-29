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
        Schema::create('inventory_movement_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('inventory_movement_id');
            $table->uuid('product_detail_id');
            $table->integer('qty');
            $table->timestamps();

            $table->foreign('inventory_movement_id')->references('id')->on('inventory_movements')->onDelete('cascade');
            $table->foreign('product_detail_id')->references('id')->on('product_details')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movement_items');
    }
};
