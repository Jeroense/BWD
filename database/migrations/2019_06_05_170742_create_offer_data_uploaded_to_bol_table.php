<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfferDataUploadedToBolTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // deze table bevat de bol-offer data, uit de JSON-body, die ge-POST wordt bij een POST  op:  retailer/offers
        // of bij een PUT op: /retailer/offers/{offer-id}
        // dus bij aanmaken van een nieuw offer, of een update van een bestaande offer, op de plaza-api.
        // deze data moet onthouden worden, aangezien na de initiele POST/PUT-request deze data anders niet meer in de lokale DB beschikbaar is.
        // De bol/plaza-api geeft het merendeel van deze data ook niet terug, bij de proces-status response.
        // het doel van deze table is dus: om de POST data van de json-body voor een offer te onthouden, om deze na een
        // process-status response met status 'SUCCES' en eventType 'CREATE_OFFER'  de dan aan te maken lokale BolProduktieOffer entry
        // te voorzien van de juiste data. Want: we willen niet afhankelijk zijn van de offer-export-CSV file.

        Schema::create('offer_data_uploaded_to_bol', function (Blueprint $table) {
            $table->increments('id');
            $table->string('offerId')->nullable();       // hierin komt de 'entityId' uit de successvolle BolProcesStatus-response
                                                        // offerId wel nullable, omdat bij de initiele POST van een nieuw offer er nog geen
                                                        // offerId bekend is.
            $table->string('ean');
            $table->string('productTitle')->nullable();
            $table->string('deliveryCode')->nullable();
            $table->unsignedInteger('stock')->nullable();
            $table->boolean('stockManagedByRetailer')->nullable();
            $table->float('price',6,2)->nullable();
            $table->unsignedInteger('quantityPrice')->nullable();      // is: $bolQtyPrice->quantity
            $table->boolean('onHoldByRetailer')->nullable();
            $table->string('fulfilment')->default('FBR')->nullable();
            $table->string('condition')->default('NEW')->nullable();
            $table->string('status')->default('OPEN');      // 'OPEN' or 'CLOSED'  of dit record 'verwerkt' is.
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
        Schema::dropIfExists('offer_data_uploaded_to_bol');
    }
}
