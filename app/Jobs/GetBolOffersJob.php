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

        Redis::throttle('getboloffers')->allow(20)->every(1)->then(function () {   // 20 job per 1 seconden
            // Job logic...

                // blijkt 28 reqs/sec toegestaan  retailer/offers/{offer_id}
                $bol_offer_by_id_response_array = $this->get_Bol_Offer_by_Id_PROD($this->serverType, $this->offerId);

                if($bol_offer_by_id_response_array['bolstatuscode'] != 200){
                    return 'Error bij request naar offers/{offerId}. Status code niet 200 !';

                    }

                    if( isset($bol_offer_by_id_response_array['bolbody']) && strpos($bol_offer_by_id_response_array['bolheaders']['Content-Type'][0], 'json') != false ){

                        $single_offer_as_stdclass = json_decode($bol_offer_by_id_response_array['bolbody']);

                        $bol_offer_in_db = BolProduktieOffer::where(
                                ['offerId' => $single_offer_as_stdclass->offerId, 'ean' => $single_offer_as_stdclass->ean])->first();

                        $product_title = '';
                        $not_publishable_reason = 'Is publishable. No errors!';

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

                        // nog eventueel alle response-velden controleren met isset()?
                        $bol_offer_in_db->update([
                            'referenceCode' => isset($single_offer_as_stdclass->referenceCode) ? $single_offer_as_stdclass->referenceCode : null,
                            'onHoldByRetailer' => $single_offer_as_stdclass->onHoldByRetailer,
                            'unknownProductTitle' => $product_title,    // deze naam nog in table aanpassen naar 'producttitle'
                            'bundlePricesQuantity' => $single_offer_as_stdclass->pricing->bundlePrices[0]->quantity,
                            'bundlePricesPrice' => $single_offer_as_stdclass->pricing->bundlePrices[0]->price,
                            'stockAmount' => $single_offer_as_stdclass->stock->amount,
                            'correctedStock' => $single_offer_as_stdclass->stock->correctedStock,
                            'stockManagedByRetailer' => $single_offer_as_stdclass->stock->managedByRetailer,
                            'fulfilmentType' => $single_offer_as_stdclass->fulfilment->type,
                            'fulfilmentDeliveryCode' => $single_offer_as_stdclass->fulfilment->deliveryCode,
                            'fulfilmentConditionName' => $single_offer_as_stdclass->condition->name,
                            'fulfilmentConditionCategory' => $single_offer_as_stdclass->condition->category,
                            'notPublishableReasonsCode' => $single_offer_as_stdclass->notPublishableReasons[0]->code,
                            'notPublishableReasonsDescription' => $not_publishable_reason
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


