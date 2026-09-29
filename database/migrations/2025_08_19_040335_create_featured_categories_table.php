<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeaturedCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('featured_categories', function (Blueprint $table) {
            $table->uuid('id')->primary()->nullable();
$table->string('title');
$table->string('sub_title')->nullable();
$table->string('link_url')->nullable();
$table->string('content_path')->nullable();

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
        Schema::dropIfExists('featured_categories');
    }
}
