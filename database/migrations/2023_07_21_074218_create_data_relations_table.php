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
        Schema::create('data_relations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('primary_table');
            $table->string('primary_id');
            $table->string('relation_table');
            $table->string('relation_id');
            $table->string('on_delete');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_relations');
    }
};
