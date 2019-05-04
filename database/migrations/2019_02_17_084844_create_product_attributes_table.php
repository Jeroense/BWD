<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()   //  dit heeft te maken met de bwd_bol mappings table.  dit zijn de bol-produkt  attributes die bol van een produkt wilt weten
    {       //
        Schema::create('product_attributes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id')->unsigned();
            $table->string('product_attribute_key')->nullable(); // elke waarde in deze kolom is een tab seperated header veld op regel 1 van de produkt feed
            $table->timestamps();

            $table->foreign('product_id')
                    ->references('id')
                    ->on('products')
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
        Schema::dropIfExists('product_attributes');
    }
}
