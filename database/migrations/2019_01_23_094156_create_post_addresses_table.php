<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post_addresses', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('customerId');
            $table->string('firstName', 100)->nullable();
            $table->string('lnPrefix', 25)->nullable();
            $table->string('lastName', 100)->nullable();
            $table->string('street', 100)->nullable();
            $table->string('houseNr', 25)->nullable();
            $table->string('houseNrPostfix', 25)->nullable();
            $table->string('postalCode', 7)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('provinceCode', 3)->nullable();
            $table->string('countryCode', 2)->nullable();
            $table->string('phone', 25)->nullable();
            $table->string('email', 100)->nullable();
            $table->timestamps();

            $table->foreign('customerId')
                    ->references('id')
                    ->on('customers')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('post_addresses');
    }
}
