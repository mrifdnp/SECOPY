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
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('source_party_id')->nullable();
            $table->uuid('destination_party_id')->nullable();
            $table->dateTime('movement_date');
            $table->string('movement_type');
            $table->string('remark')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->foreign('source_party_id')->references('id')->on('parties')->onDelete('set null');
            $table->foreign('destination_party_id')->references('id')->on('parties')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
