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
        Schema::create('change_log', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('type',['Mayor Update','Minor Update','New Release']);
            $table->datetime('created_date');
            $table->string('version',10);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('change_log');
    }
};
