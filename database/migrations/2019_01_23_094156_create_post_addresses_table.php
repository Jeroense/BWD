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
            $table->string('firstName')->nullable();
            $table->string('lnPrefix')->nullable();
            $table->string('lastName')->nullable();
            $table->string('street')->nullable();
            $table->string('houseNr')->nullable();
            $table->string('houseNrPostfix')->nullable();
            $table->string('postalCode')->nullable();
            $table->string('city')->nullable();
            $table->string('provinceCode')->nullable();
            $table->string('countryCode')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
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
