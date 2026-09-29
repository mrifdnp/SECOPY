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
        Schema::create('product_stock_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('product_detail_id')->nullable();
            $table->integer('qty')->nullable();
            $table->integer('last_stock')->nullable(); 

            $table->string('created_by',100)->nullable();
            $table->string('updated_by',100)->nullable();
            $table->string('restored_by',100)->nullable();
            $table->timestamp('restored_at')->nullable();
            $table->string('deleted_by',100)->nullable();
            $table->softDeletes('deleted_at', 0)->nullable();
            $table->timestamps();

             $table->foreign('product_detail_id')->references('id')->on('product_details')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_stock_movements', function (Blueprint $table) {
            Schema::dropIfExists('product_stock_movements');
        });
    }
};
