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
        Schema::table('seasonals', function (Blueprint $table) {
            $table->text('hover_description')->nullable()->after('description');
            $table->string('hover_image')->nullable()->after('banner_image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seasonals', function (Blueprint $table) {
            $table->dropColumn(['hover_description', 'hover_image']);
        });
    }
};
