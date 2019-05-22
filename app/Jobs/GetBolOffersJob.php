<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\BolProduktieOffer;
use Illuminate\Support\Facades\Redis;
use App\Http\Traits\BolApiV3;

class GetBolOffersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, BolApiV3;

    protected $serverType;
    protected $offerId;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($serverType, $lokaleOfferId)
    {
        $this->serverType = $serverType;
        $this->offerId = $lokaleOfferId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        Redis::throttle('getboloffers')->allow(10)->every(1)->then(function () {   // 20 job per 1 seconden
            // Job logic...

                // blijkt 28 reqs/sec toegestaan  retailer/offers/{offer_id}
                $bol_offer_by_id_response_array = $this->get_Bol_Offer_by_Id_PROD($this->serverType, $this->offerId);

                if($bol_offer_by_id_response_array['bolstatuscode'] != 200){

                     // dan eerst wellicht deze job weer her-releasen naar de queu
                    // return $this->release(10);  // ??
                    return 'Error bij request naar offers/{offerId}. Status code niet 200 !';
                    }



                    if( isset($bol_offer_by_id_response_array['bolbody']) && strpos($bol_offer_by_id_response_array['bolheaders']['Content-Type'][0], 'json') !== false ){

                        $single_offer_as_stdclass = json_decode($bol_offer_by_id_response_array['bolbody']);

                            // controle of minimale fields gezet zijn in de response-body
                            if( !isset($single_offer_as_stdclass->offerId) || !isset($single_offer_as_stdclass->ean) ){

                                return 'bol-offer-by-id  response bevat niet de minimaal benodigde informatie zoals offerId en ean!';
                            }
                            //



                        $bol_offer_in_db = BolProduktieOffer::where(
                                ['offerId' => $single_offer_as_stdclass->offerId, 'ean' => $single_offer_as_stdclass->ean])->first();

                        $product_title = '';
                        $not_publishable_reason = 'Is published. No errors!';

                        // ofwel de property: ->unknownProductTitle  ofwel property:  ->store->productTitle    is aanwezig in reply
                        if( isset($single_offer_as_stdclass->unknownProductTitle) ){
                            $product_title = $single_offer_as_stdclass->unknownProductTitle;
                        }
                        if( isset($single_offer_as_stdclass->store->productTitle) ){
                            $product_title = $single_offer_as_stdclass->store->productTitle;
                        }
                        if( isset($single_offer_as_stdclass->notPublishableReasons[0]->description) ){                   // is niet aanwezig in response
                            $not_publishable_reason = $single_offer_as_stdclass->notPublishableReasons[0]->description;  // als alles ok/publishable is
                        }


                        $bol_offer_in_db->update([
                            'referenceCode' => isset($single_offer_as_stdclass->referenceCode) ? $single_offer_as_stdclass->referenceCode : null,
                            'onHoldByRetailer' => isset($single_offer_as_stdclass->onHoldByRetailer) ? $single_offer_as_stdclass->onHoldByRetailer : $bol_offer_in_db->onHoldByRetailer,
                            'unknownProductTitle' => $product_title,    // deze naam nog in table aanpassen naar 'producttitle'
                            'bundlePricesQuantity' => isset($single_offer_as_stdclass->pricing->bundlePrices[0]->quantity) ? $single_offer_as_stdclass->pricing->bundlePrices[0]->quantity : $bol_offer_in_db->bundlePricesQuantity,
                            'bundlePricesPrice' => isset($single_offer_as_stdclass->pricing->bundlePrices[0]->price) ? $single_offer_as_stdclass->pricing->bundlePrices[0]->price : $bol_offer_in_db->bundlePricesPrice,
                            'stockAmount' => isset($single_offer_as_stdclass->stock->amount) ? $single_offer_as_stdclass->stock->amount : $bol_offer_in_db->stockAmount,
                            'correctedStock' => isset($single_offer_as_stdclass->stock->correctedStock) ? $single_offer_as_stdclass->stock->correctedStock : $bol_offer_in_db->correctedStock,
                            'stockManagedByRetailer' => isset($single_offer_as_stdclass->stock->managedByRetailer) ? $single_offer_as_stdclass->stock->managedByRetailer : $bol_offer_in_db->stockManagedByRetailer,
                            'fulfilmentType' => isset($single_offer_as_stdclass->fulfilment->type) ? $single_offer_as_stdclass->fulfilment->type : $bol_offer_in_db->fulfilmentType,
                            'fulfilmentDeliveryCode' => isset($single_offer_as_stdclass->fulfilment->deliveryCode) ? $single_offer_as_stdclass->fulfilment->deliveryCode : $bol_offer_in_db->fulfilmentDeliveryCode,
                            'fulfilmentConditionName' => isset($single_offer_as_stdclass->condition->name) ? $single_offer_as_stdclass->condition->name : $bol_offer_in_db->fulfilmentConditionName,
                            'fulfilmentConditionCategory' => isset($single_offer_as_stdclass->condition->category) ? $single_offer_as_stdclass->condition->category : $bol_offer_in_db->fulfilmentConditionCategory,
                            'notPublishableReasonsCode' => isset($single_offer_as_stdclass->notPublishableReasons[0]->code) ? $single_offer_as_stdclass->notPublishableReasons[0]->code : $bol_offer_in_db->notPublishableReasonsCode,
                            'notPublishableReasonsDescription' => isset($not_publishable_reason) ? $not_publishable_reason : $bol_offer_in_db->notPublishableReasonsDescription
                        ]);
                        dump('In GetBolOffersJob!   bol_produktie_offers table succesvol geupdated voor offer-id: ');
                        dump($single_offer_as_stdclass->offerId);
                    }

        }, function () {
            // Could not obtain lock...
            return $this->release(10);
        });
    }

}


