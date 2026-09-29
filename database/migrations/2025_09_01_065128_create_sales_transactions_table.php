<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary()->nullable();
            $table->uuid('store_id')->nullable();
            $table->datetime('date')->nullable();
            $table->string('invoice_id')->nullable();
            $table->uuid('customer_id')->nullable();
            $table->string('sub_total')->nullable();
            $table->string('discount_percent')->nullable();
            $table->string('discount_nominal')->nullable();
            $table->string('additional_cost')->nullable();
            $table->string('total')->nullable();
            $table->uuid('payment_type')->nullable();
            $table->string('channel')->nullable();
            $table->string('payment_status')->nullable();
            $table->uuid('sale_people_id')->nullable();

            $table->string('created_by', 100)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->string('restored_by', 100)->nullable();
            $table->timestamp('restored_at')->nullable();
            $table->string('deleted_by', 100)->nullable();
            $table->softDeletes('deleted_at', 0)->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->foreign('sale_people_id')->references('id')->on('sale_people')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_transactions');
    }
}
