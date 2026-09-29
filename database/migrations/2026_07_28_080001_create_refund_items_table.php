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
        Schema::create('refund_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('refund_id')->nullable();
            $table->uuid('sales_transaction_item_id')->nullable();
            $table->uuid('product_detail_id')->nullable();
            $table->integer('qty')->nullable();
            $table->double('price')->nullable();
            $table->double('refund_amount')->nullable();
            $table->timestamps();

            $table->foreign('refund_id')->references('id')->on('refunds')->onDelete('cascade');
            $table->foreign('sales_transaction_item_id')->references('id')->on('sales_transaction_items')->onDelete('cascade');
            $table->foreign('product_detail_id')->references('id')->on('product_details')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refund_items');
    }
};
