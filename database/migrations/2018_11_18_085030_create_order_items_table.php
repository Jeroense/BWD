<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orderItems', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('orderId')->unsigned();
            $table->string('bolOrderItemId');   // 25-2 toegevoegd om makkelijker te kunnen zoeken, na <CancelRequest>true</CancelRequest>
            $table->integer('qty');
            // $table->integer('variantId');
            $table->integer('customVariantId');  // heb dit 17-2 gewijzigd
            $table->string('ean'); // dit 19-4 toegevoegd
            $table->string('latestDeliveryDate');

            $table->foreign('orderId')
                    ->references('id')
                    ->on('orders')
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
        Schema::dropIfExists('orderItems');
    }
}
