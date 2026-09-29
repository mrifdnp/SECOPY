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
        if (!Schema::hasTable('general_journals')) {
            Schema::create('general_journals', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->dateTime('date');
                $table->uuid('head_accounts_id');
                $table->foreign('head_accounts_id')->references('id')->on('head_accounts')->onDelete('cascade')->onUpdate('cascade');
                $table->text('naration');
                $table->string('generated_source')->nullable();
                $table->decimal('debit', 18, 2)->default(0);
                $table->decimal('kredit', 18, 2)->default(0);
                $table->string('source_type')->nullable();
                $table->string('source_id')->nullable();
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
        Schema::dropIfExists('general_journals');
    }
};
