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
            $table->integer('parentVariantId')->unsigned()->nullable();
            $table->integer('smakeVariantId')->nullable();
            $table->string('ean');
            $table->string('size');
            $table->double('price',8,2)->nullable();
            $table->double('tax',8,2)->nullable();
            $table->double('taxRate',8,2)->nullable();
            $table->double('total',8,2)->nullable();
            $table->string('filename');
            $table->integer('compositeMediaId')->unsigned();
            $table->integer('smakeId')->unsigned()->nullable();
            $table->integer('productionMediaId')->unsigned();
            $table->integer('smakeProductionMediaId')->unsigned()->nullable();
            $table->double('width_mm',5,1);
            $table->double('height_mm',5,1);
            $table->boolean('isPublishedAtBol')->nullable();
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
        Schema::dropIfExists('customvariants');
    }
}
