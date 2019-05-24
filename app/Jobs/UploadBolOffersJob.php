<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Redis;
use App\Http\Traits\BolApiV3;
use App\BolProcesStatus;

class UploadBolOffersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, BolApiV3;

    private $bol_offer;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($offer)
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
        Redis::throttle('upload_bol_offer')->allow(1)->every(2)->then(function () {

        // file_put_contents( storage_path( 'app/public') . '/' . 'BolOffersUploadThrottling_log.txt', ((string)date('D, d M Y H:i:s:v') . "\r\n" . \microtime(true) . "\r\n" . $this->bol_offer . "\r\n\r\n"), FILE_APPEND );
            dump('Uploading to bol: ', $this->bol_offer);

            $bolOfferResponse =  $this->make_V3_PlazaApiRequest('demo', 'offers', 'post', $this->bol_offer);

            dump("Bol response code: ", $bolOfferResponse['bolstatuscode']);

            $this->update_BolProcessStatus_Table($bolOfferResponse);

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

                // check om te kijken of deze minimale velden gezet zijn in de proces-status-response-body
                if( !isset($resp_object->id) || !isset($resp_object->eventType) || !isset($resp_object->status) ){
                    return 'Geen proces-status->ID of geen proces-status->eventType of geen proces-status->status in response aanwezig!';
                }
                //

                $process_status_in_db = BolProcesStatus::where(['process_status_id' => $resp_object->id,
                                                                'eventType' => $resp_object->eventType,
                                                                'description' => $resp_object->description
                                                               ])->first();

                if($process_status_in_db != null){

                    $process_status_in_db->update([
                        'entityId' => isset($resp_object->entityId) ? $resp_object->entityId : $process_status_in_db->entityId,
                        'eventType' => $resp_object->eventType,
                        'description' => $resp_object->description,
                        'status' => $resp_object->status,
                        'errorMessage' => isset($resp_object->errorMessage) ? $resp_object->errorMessage : $process_status_in_db->errorMessage,
                        'createTimestamp' => $resp_object->createTimestamp,
                        'link_to_self' => isset($resp_object->links[0]->href) ? $resp_object->links[0]->href : $process_status_in_db->link_to_self,
                        'method_to_self' => isset($resp_object->links[0]->method) ? $resp_object->links[0]->method : $process_status_in_db->method_to_self,
                    ]);
                }

                if($process_status_in_db == null){

                    BolProcesStatus::create([
                        'process_status_id' => $resp_object->id,
                        'entityId' => isset($resp_object->entityId) ? $resp_object->entityId : null,
                        'eventType' => $resp_object->eventType,
                        'description' => isset($resp_object->description) ? $resp_object->description : null,
                        'status' => $resp_object->status,
                        'errorMessage' => isset($resp_object->errorMessage) ? $resp_object->errorMessage : null,
                        'createTimestamp' => isset($resp_object->createTimestamp) ? $resp_object->createTimestamp : 'createTimestamp_is_empty',
                        'link_to_self' => isset($resp_object->links[0]->href) ? $resp_object->links[0]->href : 'link_to_self_empty',
                        'method_to_self' => isset($resp_object->links[0]->method) ? $resp_object->links[0]->method : 'method_to_self_empty',
                    ]);
                }

        }
    }
}
