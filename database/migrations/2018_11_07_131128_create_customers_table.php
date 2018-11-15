<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->increments('id');
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
            $table->boolean('hasDeliveryAddress')->nullable();
            $table->boolean('hasBillingAddress')->nullable();
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
