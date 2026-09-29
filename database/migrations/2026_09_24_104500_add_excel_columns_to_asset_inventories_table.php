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
        Schema::table('asset_inventories', function (Blueprint $table) {
            $table->string('merk', 100)->nullable()->after('name');
            $table->integer('jumlah_unit')->default(1)->after('tgl');
            $table->decimal('harga_per_unit', 15, 2)->nullable()->after('jumlah_unit');
            $table->decimal('umur_manfaat', 8, 2)->nullable()->after('harga_per_unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_inventories', function (Blueprint $table) {
            $table->dropColumn(['merk', 'jumlah_unit', 'harga_per_unit', 'umur_manfaat']);
        });
    }
};
