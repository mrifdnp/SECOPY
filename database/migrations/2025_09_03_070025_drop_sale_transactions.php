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
        Schema::table('sale_transactions', function (Blueprint $table) {
             Schema::dropIfExists('sale_transactions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary()->nullable();
            $table->string('store_id')->nullable();
            $table->datetime('date')->nullable();
            $table->string('invoice_id')->nullable();
            $table->string('customer_id')->nullable();
            $table->string('sub_total')->nullable();
            $table->string('discount_percent')->nullable();
            $table->string('discount_nominal')->nullable();
            $table->string('additional_cost')->nullable();
            $table->string('total')->nullable();
            $table->string('payment_type')->nullable();
            $table->string('channel')->nullable();
            $table->string('payment_status')->nullable();
            $table->string('sales_person')->nullable();

            $table->string('created_by', 100)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->string('restored_by', 100)->nullable();
            $table->timestamp('restored_at')->nullable();
            $table->string('deleted_by', 100)->nullable();
            $table->softDeletes('deleted_at', 0)->nullable();
            $table->timestamps();
        });
    }
};
