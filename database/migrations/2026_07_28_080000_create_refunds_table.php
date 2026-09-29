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
        Schema::create('refunds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sales_transaction_id')->nullable();
            $table->string('refund_invoice_id')->nullable();
            $table->datetime('refund_date')->nullable();
            $table->double('total_refund')->nullable();
            $table->text('reason')->nullable();
            $table->uuid('store_id')->nullable();
            
            $table->string('created_by', 100)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->string('restored_by', 100)->nullable();
            $table->timestamp('restored_at')->nullable();
            $table->string('deleted_by', 100)->nullable();
            $table->softDeletes('deleted_at', 0)->nullable();
            $table->timestamps();

            $table->foreign('sales_transaction_id')->references('id')->on('sales_transactions')->onDelete('cascade');
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
