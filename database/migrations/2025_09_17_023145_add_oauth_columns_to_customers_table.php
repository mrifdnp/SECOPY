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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('oauth_provider', 50)->nullable();
            $table->string('oauth_id')->nullable();
            $table->text('oauth_token')->nullable();
            $table->text('oauth_refresh_token')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['oauth_provider', 'oauth_id', 'oauth_token', 'oauth_refresh_token']);
        });
    }
};
