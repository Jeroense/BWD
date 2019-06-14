<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Http\Traits\BolApiV3;
use App\BolProcesStatus;
use Illuminate\Support\Facades\Redis;
use App\BolProduktieOffer;
use App\CustomVariant;
use App\OfferDataUploadedToBol;
use Illuminate\Support\Facades\App;

class GetBolProcesStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, BolApiV3;

    protected $bol_process_status;

    public $tries = 3;     // max number of tries for this job
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(BolProcesStatus $proces_status)
    {
        $this->bol_process_status = $proces_status;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Redis::throttle('checking_pending_process_statusses')->allow(5)->every(1)->then(function ()
        {


                dump('checking_pending_process_status for: ', $this->bol_process_status->process_status_id, $this->bol_process_status->eventType);

                $bol_proc_status_response =  $this->make_V3_PlazaApiRequest_for_process_status_Full_URL($this->bol_process_status->link_to_self,
                \strtolower( $this->bol_process_status->method_to_self));

                // log proces-status response in file
                file_put_contents( storage_path( 'app/public') . '/' . "bol-proces-status-response-PROD.txt",
                ((string)date('D, d M Y H:i:s:v') . "\r\n" . \microtime(true) . "\r\n" . $bol_proc_status_response['bolstatuscode'] . "\r\n" .
                 $bol_proc_status_response['bolreasonphrase'] . "\r\n\r\n" . $bol_proc_status_response['bolbody'] .
                 "\r\n\r\n"), FILE_APPEND );
                //

                if($bol_proc_status_response['bolstatuscode'] != 200 ||
                    strpos( $bol_proc_status_response['bolheaders']['Content-Type'][0], 'json' ) === false)
                {
                    return;
                }

                dump($bol_proc_status_response['bolstatuscode'])  ;dump($bol_proc_status_response['bolbody']);

                $this->update_BolProcessStatus_Table($bol_proc_status_response);

        }, function () {
            // Could not obtain lock...

            file_put_contents( storage_path( 'app/public') . '/' . 'GetBolProcesStatusJob_eror_no_lock_log.txt', ((string)date('D, d M Y H:i:s:v') . "\r\n" . \microtime(true) .  "\r\n" . "Could not obtain lock.." . "\r\n\r\n"), FILE_APPEND );

                return $this->release(3);
        });
    }


    public function update_BolProcessStatus_Table(array $bol_response)
    {

        if( $bol_response['bolstatuscode'] == 200 && !empty( $bol_response['bolbody'] )  ) // wat dubbel, maar goed

            {
                dump('in de update_bol_processStatus_Table functie!!');

                $resp_object = json_decode($bol_response['bolbody']);

                // check om te kijken of deze minimale velden gezet zijn in de proces-status-response-body
                if( empty($resp_object->id) || empty($resp_object->eventType) || empty($resp_object->status) )
                {
                    return 'Geen proces-status->ID of geen proces-status->eventType of geen proces-status->status in response aanwezig!';
                }






                $this->bol_process_status->update([
                    'entityId' => !empty($resp_object->entityId) ? $resp_object->entityId : $this->bol_process_status->entityId,
                    'eventType' => $resp_object->eventType,
                    'description' => !empty($resp_object->description) ? $resp_object->description : $this->bol_process_status->description,
                    'status' => $resp_object->status,
                    'errorMessage' => !empty($resp_object->errorMessage) ? $resp_object->errorMessage : $this->bol_process_status->errorMessage,
                    'createTimestamp' => !empty($resp_object->createTimestamp) ? $resp_object->createTimestamp : $this->bol_process_status->createTimestamp,
                    'link_to_self' => !empty($resp_object->links[0]->href) ? $resp_object->links[0]->href : $this->bol_process_status->link_to_self,
                    'method_to_self' => !empty($resp_object->links[0]->method) ? $resp_object->links[0]->method : $this->bol_process_status->method_to_self,
                    ]);


                $fresh_process_status = $this->bol_process_status->fresh();

                if($fresh_process_status->status !== 'SUCCESS')
                {
                    return;
                }

                switch($fresh_process_status->eventType)
                {
                    case 'CREATE_OFFER':


                    // eerst request doen (op GetBolOffersJob queue gooien) GET naar: offers/{offerid}
                    // van daaruit wordt een BolProduktieOffer record aangemaakt mbv deze proces-status
                    // om dit record te updaten (met name voor: 'notPublishableReasonsCode' en 'notPublishableReasonsDescription')
                    \App\Jobs\GetBolOffersJob::dispatch('prod', $fresh_process_status->entityId)    ;

                    break;

                    case 'DELETE_OFFER':

                        $local_bol_prod_offer = BolProduktieOffer::where(['offerId' => $fresh_process_status->entityId])->first();

                        if($local_bol_prod_offer == null)
                        {
                            return;
                        }

                        $local_bol_prod_offer->delete();

                        $custom_variant = CustomVariant::where(['ean' => $local_bol_prod_offer->ean])->first();

                        if($custom_variant == null)  // dit zou natuurlijk niet moeten kunnen voorkomen, maar goed
                        {
                            return;
                        }

                        $custom_variant->update(['isPublishedAtBol' => 'unpublished_at_api']);


                    break;

                    case 'UPDATE_OFFER':                                        // voor update 'onhold' en  'deliveryCode'
                    \App\Jobs\GetBolOffersJob::dispatch('prod', $fresh_process_status->entityId);
                    break;

                    case 'UPDATE_OFFER_PRICE':                                  // voor update prijs
                        \App\Jobs\GetBolOffersJob::dispatch('prod', $fresh_process_status->entityId);
                    break;

                    case 'UPDATE_OFFER_STOCK':                                  // stock update
                        \App\Jobs\GetBolOffersJob::dispatch('prod', $fresh_process_status->entityId);
                    break;

                    default:
                    return;
                }

                //---------------------------------------------------------------------------------------------------------------
                // nu zou je (in theorie) alvast, bij eventType 'CREATE_OFFER' of eventType 'DELETE_OFFER', en bij status 'SUCCESS'
                // het bijhorende BolProduktieOffer-record en het bijhorende CustomVariant-record kunnen deleten/adden.
                // maar ook al geeft de proces status response (na enkele minuten) een 'SUCCES' voor een 'DELETE_OFFER' , dan nog is deze mutatie
                // meestal niet zichtbaar in de hierna gegenereerde offer-export-csv-file! (de gedelete-offer staat dan vaak nog
                // gewoon gelist als aangeboden/published). Het is dus de vraag WANNEER iets precies verwerkt is door bol.
                // deze records worden zowieso per succesvolle gegenereerde offer-export-csv up-gedate, maar dit duurt meestal
                // ca. een uur.. of nog langer, dus zo weinig mogelijk uitgaan van offer export csv
                //
                // bovenstaande geldt ook voor updates van prijs, stock en onhold/deliverycode

                // als je er vanuit gaat, dat de proces-status-repsonse leading is, d.w.z. , dat de produkt-export-csv-file
                // 'achterloopt', dan zou je na proces-status responses met status 'SUCCESS' bij eventTypes 'CREATE_OFFER'
                // 'DELETE_OFFER' , 'UPDATE_OFFER_PRICE' 'UPDATE_OFFER_STOCK'  etc direct de records BolProduktieOffer en CustomVariant
                // moeten kunnen aanpassen, en besluiten, slechts eenmaal per dag een produkt-export-csv-file aan te maken
                //
                // Het volgende lijkt juist:
                //
                // scenario: ik heb 4 BolProduktieOffers published on bol:

                // offerId's :

                // 840ac44d-4348-27d8-e053-828b620a7e46
                // d8dfb377-b8b5-4c61-b741-4e13d445b64d
                // e4600c52-bed5-4c71-bf74-fbd8b985173e
                // 464af259-0b9a-4254-b85f-b95ccafad30a
                //
                // nu doe ik een DELETE voor offer met id: d8dfb377-b8b5-4c61-b741-4e13d445b64d. Na 5 minuten is dit een 'SUCCESS'

                // Nu vraag ik elke half uur een offer-export-csv op.
                // beide opeenvolgende offer-export-csv-file's (na 30 min en na een uur) bevatten (nog steeds) het offer met id:

                // d8dfb377-b8b5-4c61-b741-4e13d445b64d    , als zijnde 'published':

// 200 OK

// offerId,ean,conditionName,conditionCategory,conditionComment,bundlePricesPrice,fulfilmentDeliveryCode,stockAmount,onHoldByRetailer,fulfilmentType,mutationDateTime
// 464af259-0b9a-4254-b85f-b95ccafad30a,7435156898837,NEW,NEW,,25.00,4-8d,30,true,FBR,2019-06-04 11:03:12.26 UTC
// 840ac44d-4348-27d8-e053-828b620a7e46,7435156898875,NEW,NEW,,19.95,1-8d,100,false,FBR,2019-03-14 10:41:25.596 UTC
// e4600c52-bed5-4c71-bf74-fbd8b985173e,7435156898820,NEW,NEW,,25.00,4-8d,28,true,FBR,2019-06-04 18:22:24.997 UTC
// d8dfb377-b8b5-4c61-b741-4e13d445b64d,7435156898844,NEW,NEW,,19.95,3-5d,5,true,FBR,2019-06-04 18:38:42.282 UTC


                //

                // Als ik nu (wat ik geprogrammeerd heb als zijnde de normale proces-gang na het binnenhalen van een succesvolle offer-export-csv)
                // voor elke offerId uit deze export-csv een request doe naar offers/{offerId} dan krijg ik ALLEEN voor

                // GET offers/d8dfb377-b8b5-4c61-b741-4e13d445b64d    :

                // 404 Not Found

                // {
                //  "type":"http://api.bol.com/problems",
                //  "title":"Not Found",
                //  "status":404,
                //  "detail":"Offer with id: d8dfb377-b8b5-4c61-b741-4e13d445b64d not found.",
                //  "host":"Instance-001",
                //  "instance":"https://api.bol.com/retailer/offers/d8dfb377-b8b5-4c61-b741-4e13d445b64d"
                // }


                // Conclusie: de BolProcess status lijkt 'leading' te zijn.

                // Niet te vaak offer-exports-csv-file's ophalen!!

                // De offer-export-csv file is vaak niet up-to date/ duurt erg lang.
                // Als je frequent de offer-exports-csv-file ophaalt, worden alle offerId's die hier in staan,
                //  in de BolProduktieOffer-table gezet,
                // (ook, zoals blijkt) zitten hier, niet meer up to date offerid's, bij van reeds verwijderde offers.

                // Dus wellicht beste om offer-exports-csv-file 1x per dag/week ophalen, ochtends?

        }
    }
}

















                // $process_status_in_db = BolProcesStatus::where(['process_status_id' => $resp_object->id,
                //                                                 'eventType' => $resp_object->eventType,

                //                                                ])->first();

                // if($process_status_in_db != null){

                //     $process_status_in_db->update([
                //         'entityId' => isset($resp_object->entityId) ? $resp_object->entityId : $process_status_in_db->entityId,
                //         'eventType' => $resp_object->eventType,
                //         'description' => isset($resp_object->description) ? $resp_object->description : $process_status_in_db->description,
                //         'status' => $resp_object->status,
                //         'errorMessage' => isset($resp_object->errorMessage) ? $resp_object->errorMessage : $process_status_in_db->errorMessage,
                //         'createTimestamp' => isset($resp_object->createTimestamp) ? $resp_object->createTimestamp : $process_status_in_db->createTimestamp,
                //         'link_to_self' => isset($resp_object->links[0]->href) ? $resp_object->links[0]->href : $process_status_in_db->link_to_self,
                //         'method_to_self' => isset($resp_object->links[0]->method) ? $resp_object->links[0]->method : $process_status_in_db->method_to_self,
                //     ]);
                // }

                // if($process_status_in_db == null){

                //     BolProcesStatus::create([
                //         'process_status_id' => $resp_object->id,
                //         'entityId' => isset($resp_object->entityId) ? $resp_object->entityId : null,
                //         'eventType' => $resp_object->eventType,
                //         'description' => isset($resp_object->description) ? $resp_object->description : null,
                //         'status' => $resp_object->status,
                //         'errorMessage' => isset($resp_object->errorMessage) ? $resp_object->errorMessage : null,
                //         'createTimestamp' => isset($resp_object->createTimestamp) ? $resp_object->createTimestamp : 'createTimestamp_is_empty',
                //         'link_to_self' => isset($resp_object->links[0]->href) ? $resp_object->links[0]->href : 'link_to_self_empty',
                //         'method_to_self' => isset($resp_object->links[0]->method) ? $resp_object->links[0]->method : 'method_to_self_empty',
                //     ]);
                // }
