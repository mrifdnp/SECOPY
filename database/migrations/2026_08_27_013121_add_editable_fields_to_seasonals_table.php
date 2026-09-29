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
            $table->string('collection_title')->nullable()->after('hover_description');
            $table->text('collection_subtitle')->nullable()->after('collection_title');
            $table->string('button_text')->nullable()->after('collection_subtitle');
            $table->string('button_link')->nullable()->after('button_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seasonals', function (Blueprint $table) {
            $table->dropColumn(['collection_title', 'collection_subtitle', 'button_text', 'button_link']);
        });
    }
};
