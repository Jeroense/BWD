<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('smakeOrderId')->unsigned()->nullable();
            $table->integer('customerId')->unsigned()->nullable();
            $table->string('bolOrderNr')->nullable();
            $table->string('orderStatus')->nullable();
            $table->string('deliveryDate')->nullable();
            $table->string('shippingMethod')->nullable();
            $table->double('shippingRate',8,2)->nullable();
            $table->double('orderAmount',8,2)->nullable();
            $table->double('totalTax',8,2)->nullable();
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
        Schema::dropIfExists('orders');
    }
}
