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
        if (!Schema::hasTable('accounting_business_groups')) {
            Schema::create('accounting_business_groups', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name');
                $table->uuid('parent_user_id')->nullable();
                $table->string('created_by', 100)->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->string('deleted_by', 100)->nullable();
                $table->softDeletes('deleted_at', 0)->nullable();
                $table->string('restored_by', 100)->nullable();
                $table->timestamp('restored_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounting_business_groups');
    }
};
