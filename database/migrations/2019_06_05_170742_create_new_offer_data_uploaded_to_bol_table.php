<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewOfferDataUploadedToBolTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // deze table bevat de bol-offer data, die ge-POST wordt bij een POST  op:  retailer/offers
        // dus bij aanmaken van een nieuw offer op de plaza-api.
        // deze data moet onthouden worden, aangezien de initiele POST-request data anders verloren gaat.
        // De bol/plaza-api geeft het merendeel van deze data ook niet terug, bij de proces-status response

        Schema::create('new_offer_data_uploaded_to_bol', function (Blueprint $table) {
            $table->increments('id');
            $table->string('offerId');                  // hierin komt de 'entityId' uit de successvolle BolProcesStatus-response

            $table->string('ean');
            $table->string('productTitle');
            $table->string('deliveryCode');
            $table->unsignedInteger('stock');
            $table->boolean('stockManagedByRetailer')->nullable();
            $table->float('price',6,2);
            $table->unsignedInteger('quantityPrice');      // is: $bolQtyPrice->quantity
            $table->boolean('onHoldByRetailer');
            $table->string('fulfilment')->default('FBR');
            $table->string('condition')->default('NEW');
            $table->string('refcode')->nullable();
            $table->timestamps();


            // $short_shirt_name = substr($offerData['var'], 0 , 4);

            // $refcode = "{$short_shirt_name}-{$offerData['bas']}-{$offerData['siz']}"; // ref-code length:  max 20 chars

            // $onHoldByRetailer = key_exists('onh', $offerData) ? true : false;


            // $stock = isset($offerData['sto']) ? (int)$offerData['sto'] : 0;

            // $fulFillment = 'FBR';


            // $bolConditionObject = new \stdClass();
            // $bolConditionObject->name = 'NEW';       //  Enum:"NEW" "AS_NEW" "GOOD" "REASONABLE" "MODERATE"
            // $bolConditionObject->category = 'NEW';   //  Enum:"NEW" "SECONDHAND"

            // $bolQtyPrice = new \stdClass();
            // $bolQtyPrice->quantity = 1;
            // $bolQtyPrice->price = (Double)$offerData['sal'];

            // $bolPricingBundle = new \stdClass();
            // $bolPricingBundle->bundlePrices = [$bolQtyPrice];

            // $bolStockObject = new \stdClass();
            // $bolStockObject->amount = $stock;
            // $bolStockObject->managedByRetailer = true;

            // $bolFulFillmentObject = new \stdClass();
            // $bolFulFillmentObject->type = $fulFillment;
            // $bolFulFillmentObject->deliveryCode = $offerData['del'];

            // $bolOfferObject = new \stdClass();
            // $bolOfferObject->ean = $offerData['ean'];
            // $bolOfferObject->condition = $bolConditionObject;
            // $bolOfferObject->referenceCode = $refcode;
            // $bolOfferObject->onHoldByRetailer = $onHoldByRetailer;
            // $bolOfferObject->unknownProductTitle =  $offerData['var'];
            // $bolOfferObject->pricing = $bolPricingBundle;
            // $bolOfferObject->stock = $bolStockObject;
            // $bolOfferObject->fulfilment = $bolFulFillmentObject;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('offer_data_uploaded_to_bols');
    }
}
