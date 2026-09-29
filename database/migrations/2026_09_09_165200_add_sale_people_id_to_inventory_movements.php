<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->uuid('sale_people_id')->nullable()->after('store_id');
        });
    }

    public function down()
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropColumn('sale_people_id');
        });
    }
};
