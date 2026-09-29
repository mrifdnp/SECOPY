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
        Schema::create('asset_inventories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('parent_user_id')->nullable(); // Using string for safety
            $table->string('code', 50)->unique()->nullable(false);
            $table->string('category', 50)->nullable(true);
            $table->string('name', 100)->nullable(false);
            $table->date('tgl')->nullable();
            $table->integer('depreciation_year')->nullable(false);
            $table->decimal('masa', 8, 2)->nullable();
            $table->decimal('nilai_residu', 15, 2)->nullable();
            $table->enum('last_condition', ['aktif', 'rusak', 'hilang', 'terjual']);
            $table->decimal('purchase_price', 15, 2)->nullable();
            $table->string('image')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_inventories');
    }
};
