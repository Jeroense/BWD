<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomVariantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customVariants', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('variantId')->unsigned()->nullable();
            $table->integer('smakeVariantId')->unsigned()->nullable();
            $table->string('variantName');
            $table->string('ean');
            $table->string('size');
            $table->double('price',8,2)->nullable();
            $table->double('tax',8,2)->nullable();
            $table->double('taxRate',8,2)->nullable();
            $table->double('total',8,2)->nullable();
            $table->string('filename');
            $table->integer('compositeMediaId')->unsigned();
            $table->integer('smakeCompositeMediaId')->unsigned()->nullable();
            $table->integer('productionMediaId')->unsigned();
            $table->integer('smakeProductionMediaId')->unsigned()->nullable();
            $table->double('width_mm',5,1);
            $table->double('height_mm',5,1);
            $table->string('isPublishedAtBol')->nullable(); // initiated, pending, published, Failure
            $table->timestamps();

            $table->foreign('variantId')
                    ->references('id')
                    ->on('variants')
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
        Schema::dropIfExists('customvariants');
    }
}
