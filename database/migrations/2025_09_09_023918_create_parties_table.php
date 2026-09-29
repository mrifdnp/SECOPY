<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parties', function (Blueprint $table) {
            $table->uuid('id')->primary()->nullable();
            $table->uuid('store_id')->nullable();
            $table->string('name');
            $table->text('tags')->nullable();

            $table->string('created_by', 100)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->string('restored_by', 100)->nullable();
            $table->timestamp('restored_at')->nullable();
            $table->string('deleted_by', 100)->nullable();
            $table->softDeletes('deleted_at', 0)->nullable();
            $table->timestamps();

            $table->foreign('store_id')->references('id')->on('stores')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parties');
    }
}
