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
        Schema::create('failed_import_data', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('import_status_id');
            $table->text('row');
            $table->text('replace_action')->nullable();
            $table->timestamps();

            $table->foreign('import_status_id')->references('id')->on('import_statuses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('failed_import_data');
    }
};
