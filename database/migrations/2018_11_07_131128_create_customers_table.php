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
