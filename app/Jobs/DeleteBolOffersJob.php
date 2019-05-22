<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Redis;
use App\Http\Traits\BolApiV3;
use App\BolProduktieOffer;
use App\BolProcesStatus;
use App\CustomVariant;

class DeleteBolOffersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, BolApiV3;

    protected $bol_offer;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(BolProduktieOffer $offer)
    {
        $this->bol_offer = $offer;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Redis::throttle('delete_bol_offer')->allow(1)->every(1)->then(function () {

            // file_put_contents( storage_path( 'app/public') . '/' . 'BolOffersUploadThrottling_log.txt', ((string)date('D, d M Y H:i:s:v') . "\r\n" . \microtime(true) . "\r\n" . $this->bol_offer . "\r\n\r\n"), FILE_APPEND );
                dump('Sending DELETE request to bol for: ', $this->bol_offer->offerId);

                $bol_Delete_OfferResponse =  $this->make_V3_PlazaApiRequest('demo', "offers/{$this->bol_offer->offerId}", 'delete');

                dump($bol_Delete_OfferResponse['bolstatuscode'])  ;dump($bol_Delete_OfferResponse['bolbody']);

                $this->update_BolProcessStatus_Table($bol_Delete_OfferResponse);

            }, function () {
                // Could not obtain lock...

                file_put_contents( storage_path( 'app/public') . '/' . 'TestRedisThrottling_log.txt', ((string)date('D, d M Y H:i:s:v') . "\r\n" . \microtime(true) .  "\r\n" . "Could not obtain lock.." . "\r\n\r\n"), FILE_APPEND );

                 return $this->release(3);
            });
    }

    public function update_BolProcessStatus_Table(array $bol_response){

        if( $bol_response['bolstatuscode'] == 202 && isset( $bol_response['bolbody'] )
            && strpos( $bol_response['bolheaders']['Content-Type'][0], 'json' ) !== false ){

                dump('in de update_bol_processStatus_Table functie!!');

                $resp_object = json_decode($bol_response['bolbody']);

                    //extra check of alle benodigde data aanwezig is in response-object
                    if( !isset($resp_object->id) || !isset($resp_object->eventType) || !isset($resp_object->status) || !isset($resp_object->createTimestamp)
                        || !isset($resp_object->links[0]->href) || !isset($resp_object->links[0]->method) ){

                        dump('Missing one of these in bol-delete-offer proces-status response: id, eventType, status, createTimestamp, link_to_self or method_to_self');
                        return 'Missing one of these in bol-delete-offer proces-status response: id, eventType, status, createTimestamp, link_to_self or method_to_self';
                    }
                    //

                $process_status_in_db = BolProcesStatus::where(['process_status_id' => $resp_object->id, 'eventType' => $resp_object->eventType])->latest()->first();

                if($process_status_in_db != null){

                    $process_status_in_db->update([
                        'entityId' => isset($resp_object->entityId) ? $resp_object->entityId : $process_status_in_db->entityId,
                        'eventType' => $resp_object->eventType,
                        'description' => isset($resp_object->description) ? $resp_object->description : $process_status_in_db->description,
                        'status' => $resp_object->status,
                        'createTimestamp' => $resp_object->createTimestamp,
                        'link_to_self' => $resp_object->links[0]->href,
                        'method_to_self' => $resp_object->links[0]->method,
                    ]);
                    dump('updated proces-status for delete response: '); dump($process_status_in_db->id);
                }

                if($process_status_in_db == null){

                    BolProcesStatus::create([
                        'process_status_id' => $resp_object->id,
                        'entityId' => isset($resp_object->entityId) ? $resp_object->entityId : null,
                        'eventType' => $resp_object->eventType,
                        'description' => isset($resp_object->description) ? $resp_object->description : null,
                        'status' => $resp_object->status,
                        'createTimestamp' => $resp_object->createTimestamp,
                        'link_to_self' => $resp_object->links[0]->href,
                        'method_to_self' => $resp_object->links[0]->method,
                    ]);
                    dump('created proces-status for delete response: '); dump($process_status_in_db->id);
                }
                    //--------------------------------------------------------------------------
                    // {"id":1,
                    // "entityId":"38dff9a2-dc45-4201-85f2-cb0ae0cd80d5",
                    // "eventType":"DELETE_OFFER",
                    // "description":"Delete offer with id 38dff9a2-dc45-4201-85f2-cb0ae0cd80d5.",
                    // "status":"PENDING",
                    // "createTimestamp":"2019-05-17T14:33:31.132+02:00",
                    // "links":[
                    //         {"rel":"self",
                    //         "href":"https://api.bol.com/retailer-demo/process-status/1",
                    //         "method":"GET"}]
                    // }
                    //---------------------------------------------------------------------------


                    // nu de delete-opdracht succesvol geinitieerd is, de betreffende customvariant , kolom 'isPublishedAtBol' status aanpassen:
                    $customVariantMarkedForDeleteOnBol = CustomVariant::where(['ean' => $this->bol_offer->ean])->first();
                    dump('customvariant marked for delete on bol: ');dump($customVariantMarkedForDeleteOnBol->ean);
                    $customVariantMarkedForDeleteOnBol->update(['isPublishedAtBol' => 'unpublish_initiated']);
        }

    }
}
