<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->uuid('id')->primary()->nullable();
$table->string('first_name');
$table->string('last_name')->nullable();
$table->string('email')->unique();
$table->string('password')->nullable();
$table->string('phone')->nullable();
$table->date('birthday')->nullable();
$table->string('postcode')->nullable();
$table->string('sub_district')->nullable();
$table->string('district')->nullable();
$table->string('city')->nullable();
$table->string('province')->nullable();
$table->string('address')->nullable();
$table->string('onboarding_channel')->nullable();

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
        Schema::dropIfExists('customers');
    }
}
