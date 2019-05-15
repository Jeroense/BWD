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
            $table->integer('variantId')->unsigned()->nullable();   //record id van variants table
            $table->integer('smakeVariantId')->unsigned()->nullable(); // vanuit smake verkregen
            $table->string('variantName');                          // ook title voor bol
            $table->string('ean');                                  //  beter is om ean->unique() te maken
            $table->string('size');
            $table->double('price',8,2)->nullable();
            $table->double('tax',8,2)->nullable();
            $table->double('taxRate',8,2)->nullable();
            $table->double('total',8,2)->nullable();
            $table->string('fileName');
            $table->string('baseColor');
            $table->integer('compositeMediaId')->unsigned();
            $table->integer('smakeCompositeMediaId')->unsigned()->nullable();
            $table->integer('productionMediaId')->unsigned();
            $table->integer('smakeProductionMediaId')->unsigned()->nullable();

            $table->boolean('isInBolCatalog');  // per 19-4 toegevoegd (voor sftp produktinfo)
            $table->double('width_mm',5,1);
            $table->double('height_mm',5,1);

            $table->double('salePrice',8,2);                //  7-3
            $table->string('boldeliverycode');              // table erbij levertijden/deliverycodes selectbox
            $table->string('boldescription')->nullable();               // onduidelijk of required, alleen gebruikt als condition != 'new'

            $table->string('isPublishedAtBol')->nullable(); // initiated, pending, published, failure, not_yet_in_catalog
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
