<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBolProduktieOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // in deze migratie/DB table verwerk ik de data uit 2 requests:
        // eerst de data uit de offer-export-csv file (die eerst opgehaald moet worden, en alle offer-id's bevat):   offers/export/{offer-export-id},
        // daarna de data uit de individuele offer-endpoints: offfers/{offerid}
        // hierdoor moeten er een aantal kolommen nullable zijn: anders de table niet in 1e instantie te vullen..
        Schema::create('bol_produktie_offers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('offerId');
            $table->string('ean')->unique();    // unique om ev later aan gereferenced te worden door een FK uit andere table
            $table->string('referenceCode')->nullable();
            $table->boolean('onHoldByRetailer');
            $table->string('unknownProductTitle')->nullable();
            $table->unsignedInteger('bundlePricesQuantity')->nullable();
            $table->double('bundlePricesPrice',8,2);
            $table->unsignedInteger('stockAmount');
            $table->unsignedInteger('correctedStock')->nullable();
            $table->boolean('stockManagedByRetailer')->nullable();
            $table->string('fulfilmentType');
            $table->string('fulfilmentDeliveryCode');
            $table->string('fulfilmentConditionName');
            $table->string('fulfilmentConditionCategory');
            $table->string('notPublishableReasonsCode')->nullable();
            $table->string('notPublishableReasonsDescription')->nullable();
            $table->string('mutationDateTime')->nullable();
            $table->timestamps();



// If you really want to create a foreign key to a non-primary key, it MUST be a column that has a unique constraint on it.
// De column 'ean' in customvariants heeft geen ->unique() ...
            // $table->foreign('ean')
            // ->references('ean')
            // ->on('customvariants')
            // ->onUpdate('cascade')
            // ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bol_produktie_offers');
    }
}
