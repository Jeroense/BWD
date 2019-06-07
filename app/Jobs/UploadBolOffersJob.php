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

    private $bol_single_offer_json_body;
    private $server_type;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($server , $single_offer_json_body)
    {
        $this->bol_single_offer_json_body = $single_offer_json_body;
        $this->server_type = $server;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Redis::throttle('upload_bol_offer')->allow(1)->every(1)->then(function () {

        // file_put_contents( storage_path( 'app/public') . '/' . 'BolPostOfferResponse_log.txt', ((string)date('D, d M Y H:i:s:v') . "\r\n" . \microtime(true) . "\r\n" . $this->bol_offer . "\r\n\r\n"), FILE_APPEND );
            dump('Uploading to bol: ', $this->bol_single_offer_json_body);

            $bolOfferResponse =  $this->make_V3_PlazaApiRequest($this->server_type, 'offers', 'post', $this->bol_single_offer_json_body);

            dump("Bol response code: ", $bolOfferResponse['bolstatuscode']);

            if($bolOfferResponse['bolstatuscode'] != 202)
            {
                return;
            }

            // log response in text-file
            file_put_contents( storage_path( 'app/public') . '/' . "BolPostOfferResponse-{$this->server_type}.txt", ((string)date('D, d M Y H:i:s:v') . "\r\n" . \microtime(true) . "\r\n" . $bolOfferResponse['bolbody'] . "\r\n\r\n"), FILE_APPEND );

            $this->update_BolProcessStatus_Table($bolOfferResponse);

            // zet na een 202, de bijhorende customVariant, het veld: 'isPublishedAtBol' op: 'publish_at_api_initiated'
            $single_offer_json_body_decoded = json_decode($this->bol_single_offer_json_body);
            $het_ean = $single_offer_json_body_decoded->ean;
            $custVar = \App\CustomVariant::where(['ean' => $het_ean])->first();

            if($custVar != null)   // voor zekerheid, tja...
            {
                $custVar->update(['isPublishedAtBol' => 'publish_at_api_initiated']);
            }

            // nu ook: om niet afhankelijk te zijn van de ERG trage/achterlopende offer-export-csv file (die vaak nog offerId's
            // bevat die reeds een uur etc. geleden deleted zijn) moet de data uit de hier ge-POST-e $this->bol_single_offer_json_body
            // opgelagen worden in een extra lokale db-table ('offer_data_uploaded_to_bol'), om deze data snel te kunnen gebruiken om een record in de
            // BolProduktieOffers-table aan te maken, met correcte data, na een 'GetBolProcesStatus' response met 'eventType' ->
            // 'CREATE_OFFER' en 'status' -> 'SUCCESS'
            // Deze 'GetBolProcesStatus' - response, levert bij 'SUCCESS' alleen maar: 1) het (nieuwe) offerId
            // 2) of het een 'SUCCESS' was. 3) een description, waarin het ean wel vermeldt staat, maar die lastig te ontleden
            // en gebruiken is, kans op fouten na api-updates / aanpassingen door bol etc
            //
            // Om redelijk snel de lokale db aan te passen, mag de hier ge-POST-e data niet verloren gaan.

            // Created Migration: 2019_06_05_170742_create_offer_data_uploaded_to_bols_table

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
