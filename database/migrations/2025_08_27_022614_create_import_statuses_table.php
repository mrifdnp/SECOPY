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
        Schema::create('import_statuses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('task');
            $table->string('status');
            $table->bigInteger('success_row')->default(0);
            $table->bigInteger('failed_row')->default(0);
            $table->bigInteger('total_row')->default(0);
            $table->string('source_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_statuses');
    }
};
