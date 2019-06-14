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
    public $tries = 3;
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

        Redis::throttle('getboloffers')->allow(10)->every(1)->then(function () {   // 10 job per 1 seconden
            // Job logic...

                // blijkt 28 reqs/sec toegestaan  retailer/offers/{offer_id}
                // $bol_offer_by_id_response_array = $this->get_Bol_Offer_by_Id($this->serverType, $this->offerId);
                $bol_offer_by_id_response_array = $this->make_V3_PlazaApiRequest($this->serverType, "offers/{$this->offerId}");

                // response loggen
                $this->putResponseInFile("bol-offer-response-by-id-{$this->serverType}.txt", $bol_offer_by_id_response_array['bolstatuscode'], $bol_offer_by_id_response_array['bolreasonphrase'],
                $bol_offer_by_id_response_array['bolbody'], $bol_offer_by_id_response_array['x_ratelimit_limit'], $bol_offer_by_id_response_array['x_ratelimit_reset'], $bol_offer_by_id_response_array['x_ratelimit_remaining'], (string)time());
                //

                if($bol_offer_by_id_response_array['bolstatuscode'] != 200)
                {

                        return 'Error bij request naar offers/{offerId}. Status code niet 200 !';
                }



                    if( !empty($bol_offer_by_id_response_array['bolbody']) && strpos($bol_offer_by_id_response_array['bolheaders']['Content-Type'][0], 'json') !== false )
                    {

                        $single_offer_as_stdclass = json_decode($bol_offer_by_id_response_array['bolbody']);

                            // controle of minimale fields gezet zijn in de response-body
                            if( empty($single_offer_as_stdclass->offerId) || empty($single_offer_as_stdclass->ean) )
                            {

                                return 'bol-offer-by-id  response bevat niet de minimaal benodigde informatie zoals offerId en ean!';
                            }
                            //



                        $bol_offer_in_db = BolProduktieOffer::where(
                                ['offerId' => $single_offer_as_stdclass->offerId, 'ean' => $single_offer_as_stdclass->ean])->first();

                        if($bol_offer_in_db != null)
                        {
                            $this->update_bol_produktie_offer_in_db($bol_offer_in_db, $single_offer_as_stdclass);
                        }
                        if($bol_offer_in_db == null)
                        {
                            $this->create_bol_produktie_offer_in_db($single_offer_as_stdclass);
                        }


                        // $product_title = '';
                        // $not_publishable_reason = 'Is published. No errors!';

                        // // ofwel de property: ->unknownProductTitle  ofwel property:  ->store->productTitle    is aanwezig in reply
                        // if( isset($single_offer_as_stdclass->unknownProductTitle) ){
                        //     $product_title = $single_offer_as_stdclass->unknownProductTitle;
                        // }
                        // if( isset($single_offer_as_stdclass->store->productTitle) ){
                        //     $product_title = $single_offer_as_stdclass->store->productTitle;
                        // }
                        // if( isset($single_offer_as_stdclass->notPublishableReasons[0]->description) ){                   // is niet aanwezig in response
                        //     $not_publishable_reason = $single_offer_as_stdclass->notPublishableReasons[0]->description;  // als alles ok/publishable is
                        // }


                        // $bol_offer_in_db->update([
                        //     'referenceCode' => isset($single_offer_as_stdclass->referenceCode) ? $single_offer_as_stdclass->referenceCode : null,
                        //     'onHoldByRetailer' => isset($single_offer_as_stdclass->onHoldByRetailer) ? $single_offer_as_stdclass->onHoldByRetailer : $bol_offer_in_db->onHoldByRetailer,
                        //     'unknownProductTitle' => $product_title,    // deze naam nog in table aanpassen naar 'producttitle'
                        //     'bundlePricesQuantity' => isset($single_offer_as_stdclass->pricing->bundlePrices[0]->quantity) ? $single_offer_as_stdclass->pricing->bundlePrices[0]->quantity : $bol_offer_in_db->bundlePricesQuantity,
                        //     'bundlePricesPrice' => isset($single_offer_as_stdclass->pricing->bundlePrices[0]->price) ? $single_offer_as_stdclass->pricing->bundlePrices[0]->price : $bol_offer_in_db->bundlePricesPrice,
                        //     'stockAmount' => isset($single_offer_as_stdclass->stock->amount) ? $single_offer_as_stdclass->stock->amount : $bol_offer_in_db->stockAmount,
                        //     'correctedStock' => isset($single_offer_as_stdclass->stock->correctedStock) ? $single_offer_as_stdclass->stock->correctedStock : $bol_offer_in_db->correctedStock,
                        //     'stockManagedByRetailer' => isset($single_offer_as_stdclass->stock->managedByRetailer) ? $single_offer_as_stdclass->stock->managedByRetailer : $bol_offer_in_db->stockManagedByRetailer,
                        //     'fulfilmentType' => isset($single_offer_as_stdclass->fulfilment->type) ? $single_offer_as_stdclass->fulfilment->type : $bol_offer_in_db->fulfilmentType,
                        //     'fulfilmentDeliveryCode' => isset($single_offer_as_stdclass->fulfilment->deliveryCode) ? $single_offer_as_stdclass->fulfilment->deliveryCode : $bol_offer_in_db->fulfilmentDeliveryCode,
                        //     'fulfilmentConditionName' => isset($single_offer_as_stdclass->condition->name) ? $single_offer_as_stdclass->condition->name : $bol_offer_in_db->fulfilmentConditionName,
                        //     'fulfilmentConditionCategory' => isset($single_offer_as_stdclass->condition->category) ? $single_offer_as_stdclass->condition->category : $bol_offer_in_db->fulfilmentConditionCategory,
                        //     'notPublishableReasonsCode' => isset($single_offer_as_stdclass->notPublishableReasons[0]->code) ? $single_offer_as_stdclass->notPublishableReasons[0]->code : $bol_offer_in_db->notPublishableReasonsCode,
                        //     'notPublishableReasonsDescription' => isset($not_publishable_reason) ? $not_publishable_reason : $bol_offer_in_db->notPublishableReasonsDescription
                        // ]);
                        // dump('In GetBolOffersJob!   bol_produktie_offers table succesvol geupdated voor offer-id: ');
                        // dump($single_offer_as_stdclass->offerId);

                        // // ook maar gelijk de bijhorende customVariant velden 'salePrice' en 'boldeliverycode' updaten aan hand van de response:
                        // $custVariant = \App\CustomVariant::where(['ean' => $single_offer_as_stdclass->ean])->first();
                        // if($custVariant != null)
                        // {
                        //     $custVariant->update(['salePrice' => $single_offer_as_stdclass->pricing->bundlePrices[0]->price,
                        //                         'boldeliverycode' => $single_offer_as_stdclass->fulfilment->deliveryCode
                        //                         ]);
                        // }
                    }

        }, function () {
            // Could not obtain lock...
            return $this->release(10);
        });
    }

    public function update_bol_produktie_offer_in_db($existing_bol_produktie_offer, $single_offer_response_as_stdclass)
    {


            $product_title = '';
            $not_publishable_reason = 'Is published. No errors!';

            // ofwel de property: ->unknownProductTitle  ofwel property:  ->store->productTitle    is aanwezig in reply
            if( !empty($single_offer_response_as_stdclass->unknownProductTitle) ){
                $product_title = $single_offer_response_as_stdclass->unknownProductTitle;
            }
            if( !empty($single_offer_response_as_stdclass->store->productTitle) ){
                $product_title = $single_offer_response_as_stdclass->store->productTitle;
            }
            if( !empty($single_offer_response_as_stdclass->notPublishableReasons[0]->description) ){                   // is niet aanwezig in response
                $not_publishable_reason = $single_offer_response_as_stdclass->notPublishableReasons[0]->description;  // als alles ok/publishable is
            }


            $existing_bol_produktie_offer->update([
                'referenceCode' => !empty($single_offer_response_as_stdclass->referenceCode) ? $single_offer_response_as_stdclass->referenceCode : null,
                'onHoldByRetailer' => !empty($single_offer_response_as_stdclass->onHoldByRetailer) ? $single_offer_response_as_stdclass->onHoldByRetailer : $existing_bol_produktie_offer->onHoldByRetailer,
                'unknownProductTitle' => $product_title,    // deze naam nog in table aanpassen naar 'producttitle'
                'bundlePricesQuantity' => !empty($single_offer_response_as_stdclass->pricing->bundlePrices[0]->quantity) ? $single_offer_response_as_stdclass->pricing->bundlePrices[0]->quantity : $existing_bol_produktie_offer->bundlePricesQuantity,
                'bundlePricesPrice' => !empty($single_offer_response_as_stdclass->pricing->bundlePrices[0]->price) ? $single_offer_response_as_stdclass->pricing->bundlePrices[0]->price : $existing_bol_produktie_offer->bundlePricesPrice,
                'stockAmount' => !empty($single_offer_response_as_stdclass->stock->amount) ? $single_offer_response_as_stdclass->stock->amount : $existing_bol_produktie_offer->stockAmount,
                'correctedStock' => !empty($single_offer_response_as_stdclass->stock->correctedStock) ? $single_offer_response_as_stdclass->stock->correctedStock : $existing_bol_produktie_offer->correctedStock,
                'stockManagedByRetailer' => !empty($single_offer_response_as_stdclass->stock->managedByRetailer) ? $single_offer_response_as_stdclass->stock->managedByRetailer : $existing_bol_produktie_offer->stockManagedByRetailer,
                'fulfilmentType' => !empty($single_offer_response_as_stdclass->fulfilment->type) ? $single_offer_response_as_stdclass->fulfilment->type : $existing_bol_produktie_offer->fulfilmentType,
                'fulfilmentDeliveryCode' => !empty($single_offer_response_as_stdclass->fulfilment->deliveryCode) ? $single_offer_response_as_stdclass->fulfilment->deliveryCode : $existing_bol_produktie_offer->fulfilmentDeliveryCode,
                'fulfilmentConditionName' => !empty($single_offer_response_as_stdclass->condition->name) ? $single_offer_response_as_stdclass->condition->name : $existing_bol_produktie_offer->fulfilmentConditionName,
                'fulfilmentConditionCategory' => !empty($single_offer_response_as_stdclass->condition->category) ? $single_offer_response_as_stdclass->condition->category : $existing_bol_produktie_offer->fulfilmentConditionCategory,
                'notPublishableReasonsCode' => !empty($single_offer_response_as_stdclass->notPublishableReasons[0]->code) ? $single_offer_response_as_stdclass->notPublishableReasons[0]->code : $existing_bol_produktie_offer->notPublishableReasonsCode,
                'notPublishableReasonsDescription' => !empty($not_publishable_reason) ? $not_publishable_reason : $existing_bol_produktie_offer->notPublishableReasonsDescription
            ]);
            dump('In GetBolOffersJob!   bol_produktie_offers table succesvol geupdated voor offer-id: ');
            dump($single_offer_response_as_stdclass->offerId);

            // ook maar gelijk de bijhorende customVariant velden 'salePrice' en 'boldeliverycode' updaten aan hand van de response:
            $custVariant = \App\CustomVariant::where(['ean' => $single_offer_response_as_stdclass->ean])->first();
            if($custVariant != null)
            {
                $custVariant->update(['salePrice' => $single_offer_response_as_stdclass->pricing->bundlePrices[0]->price,
                                    'boldeliverycode' => $single_offer_response_as_stdclass->fulfilment->deliveryCode,
                                    'isPublishedAtBol' => 'published_at_api'
                                    ]);
            }
    }



    public function create_bol_produktie_offer_in_db($single_offer_response_as_stdclass)
    {

        $product_title = '';
        $not_publishable_reason = 'Is published. No errors!';

        // ofwel de property: ->unknownProductTitle  ofwel property:  ->store->productTitle    is aanwezig in reply
        if( !empty($single_offer_response_as_stdclass->unknownProductTitle) )
        {
            $product_title = $single_offer_response_as_stdclass->unknownProductTitle;
        }
        if( !empty($single_offer_response_as_stdclass->store->productTitle) )
        {
            $product_title = $single_offer_response_as_stdclass->store->productTitle;
        }
        if( !empty($single_offer_response_as_stdclass->notPublishableReasons[0]->description) ){                   // is niet aanwezig in response
            $not_publishable_reason = $single_offer_response_as_stdclass->notPublishableReasons[0]->description;  // als alles ok/publishable is
        }


        BolProduktieOffer::create([
            'referenceCode' => !empty($single_offer_response_as_stdclass->referenceCode) ? $single_offer_response_as_stdclass->referenceCode : null,
            'onHoldByRetailer' => !empty($single_offer_response_as_stdclass->onHoldByRetailer) ? $single_offer_response_as_stdclass->onHoldByRetailer : false,
            'unknownProductTitle' => $product_title,    // deze naam nog in table aanpassen naar 'producttitle'
            'bundlePricesQuantity' => !empty($single_offer_response_as_stdclass->pricing->bundlePrices[0]->quantity) ? $single_offer_response_as_stdclass->pricing->bundlePrices[0]->quantity : 1,
            'bundlePricesPrice' => !empty($single_offer_response_as_stdclass->pricing->bundlePrices[0]->price) ? $single_offer_response_as_stdclass->pricing->bundlePrices[0]->price : 0.00,
            'stockAmount' => !empty($single_offer_response_as_stdclass->stock->amount) ? $single_offer_response_as_stdclass->stock->amount : 0,
            'correctedStock' => !empty($single_offer_response_as_stdclass->stock->correctedStock) ? $single_offer_response_as_stdclass->stock->correctedStock : 0,
            'stockManagedByRetailer' => !empty($single_offer_response_as_stdclass->stock->managedByRetailer) ? $single_offer_response_as_stdclass->stock->managedByRetailer : 0,
            'fulfilmentType' => !empty($single_offer_response_as_stdclass->fulfilment->type) ? $single_offer_response_as_stdclass->fulfilment->type : 'FBR',
            'fulfilmentDeliveryCode' => !empty($single_offer_response_as_stdclass->fulfilment->deliveryCode) ? $single_offer_response_as_stdclass->fulfilment->deliveryCode : '4-8d',
            'fulfilmentConditionName' => !empty($single_offer_response_as_stdclass->condition->name) ? $single_offer_response_as_stdclass->condition->name : 'NEW',
            'fulfilmentConditionCategory' => !empty($single_offer_response_as_stdclass->condition->category) ? $single_offer_response_as_stdclass->condition->category : 'NEW',
            'notPublishableReasonsCode' => !empty($single_offer_response_as_stdclass->notPublishableReasons[0]->code) ? $single_offer_response_as_stdclass->notPublishableReasons[0]->code : '0',
            'notPublishableReasonsDescription' => $not_publishable_reason
        ]);
    }
}


