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
        Schema::table('sales_transactions', function (Blueprint $table) {
            $table->integer('xendit_fees')->nullable();
            $table->string('xendit_payment_method', 100)->nullable();
            $table->string('xendit_channel', 100)->nullable();
            $table->string('xendit_url',200)->nullable();
            $table->text('xendit_metadata')->nullable();
            $table->text('xendit_metapaid')->nullable();
            $table->datetime('paid_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_transactions', function (Blueprint $table) {
            $table->dropColumn([
                'xendit_fees',
                'xendit_payment_method',
                'xendit_channel',
                'xendit_url',
                'xendit_metadata',
                'xendit_metapaid',
                'paid_at'
            ]);
        });
    }
};
