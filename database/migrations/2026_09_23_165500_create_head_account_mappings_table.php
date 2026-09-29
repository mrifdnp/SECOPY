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
        if (!Schema::hasTable('head_account_mappings')) {
            Schema::create('head_account_mappings', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('users_id')->nullable();
                $table->uuid('store_id')->nullable()->index();
                $table->uuid('outlet_id')->nullable()->index();
                $table->string('key', 100)->index();
                $table->uuid('debit')->nullable();
                $table->uuid('credit')->nullable();
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
        Schema::dropIfExists('head_account_mappings');
    }
};
