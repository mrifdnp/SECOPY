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
        Schema::create('sales_transaction_items', function (Blueprint $table) {
            $table->uuid('id')->primary()->nullable();
            $table->uuid('sales_transaction_id')->nullable();
            $table->uuid('product_detail_id')->nullable();
            $table->double('price')->nullable();
            $table->double('discount_percent')->nullable();
            $table->double('discount_nominal')->nullable();
            $table->double('final_price')->nullable();
            $table->integer('qty')->nullable();
            
            $table->string('created_by', 100)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->string('restored_by', 100)->nullable();
            $table->timestamp('restored_at')->nullable();
            $table->string('deleted_by', 100)->nullable();
            $table->softDeletes('deleted_at', 0)->nullable();
            $table->timestamps();

            $table->foreign('sales_transaction_id')->references('id')->on('sales_transactions')->onDelete('cascade');
            $table->foreign('product_detail_id')->references('id')->on('product_details')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_transaction_items');
    }
};
