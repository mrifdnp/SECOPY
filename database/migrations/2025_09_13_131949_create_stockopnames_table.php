<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockopnamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stockopnames', function (Blueprint $table) {
            $table->uuid('id')->primary()->nullable();
$table->string('title');
$table->date('date_start');
$table->date('date_end');
$table->uuid('store_id');

$table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade')->onUpdate('cascade');
            $table->string('created_by',100)->nullable();
            $table->string('updated_by',100)->nullable();
            $table->string('restored_by',100)->nullable();
            $table->timestamp('restored_at')->nullable();
            $table->string('deleted_by',100)->nullable();
            $table->softDeletes('deleted_at', 0)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stockopnames');
    }
}
