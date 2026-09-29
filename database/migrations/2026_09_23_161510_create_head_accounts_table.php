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
        if (!Schema::hasTable('head_accounts')) {
            Schema::create('head_accounts', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name');
                $table->string('code')->nullable();
                $table->string('nature', 50); // assets, liability, equity, expense, revenue, hpp
                $table->string('type', 50); // asset lancar, asset tetap, bukan asset
                $table->string('expense_type', 50)->nullable(); // beban kas, beban non kas, beban barang, bukan beban
                $table->string('kas', 50)->nullable(); // kas, non kas
                $table->string('aktifitas', 100)->nullable(); // aktivitas operasi, aktivitas investasi, aktivitas pendanaan
                $table->string('sn', 50)->nullable(); // debit, credit
                $table->string('pos', 50)->nullable(); // pl, balancesheet
                $table->text('business_type')->nullable(); // json encoded
                $table->uuid('users_id')->nullable();
                $table->uuid('parent_user_id')->nullable();
                $table->string('created_by', 100)->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->string('restored_by', 100)->nullable();
                $table->timestamp('restored_at')->nullable();
                $table->string('deleted_by', 100)->nullable();
                $table->softDeletes('deleted_at', 0)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('head_accounts');
    }
};
