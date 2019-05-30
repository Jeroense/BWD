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

class GetBolProcesStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, BolApiV3;

    protected $bol_process_status;
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
        Redis::throttle('checking_pending_process_statusses')->allow(5)->every(1)->then(function () {


                dump('checking_pending_process_status for: ', $this->bol_process_status->process_status_id, $this->bol_process_status->eventType);

                $bol_proc_status_response =  $this->make_V3_PlazaApiRequest_for_process_status_Full_URL($this->bol_process_status->link_to_self,
                \strtolower( $this->bol_process_status->method_to_self));

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

        if( $bol_response['bolstatuscode'] == 200 && isset( $bol_response['bolbody'] )   // wat dubbel, maar goed
            && strpos( $bol_response['bolheaders']['Content-Type'][0], 'json' ) !== false )
            {
                dump('in de update_bol_processStatus_Table functie!!');

                $resp_object = json_decode($bol_response['bolbody']);

                // check om te kijken of deze minimale velden gezet zijn in de proces-status-response-body
                if( !isset($resp_object->id) || !isset($resp_object->eventType) || !isset($resp_object->status) )
                {
                    return 'Geen proces-status->ID of geen proces-status->eventType of geen proces-status->status in response aanwezig!';
                }






                $this->bol_process_status->update([
                    'entityId' => isset($resp_object->entityId) ? $resp_object->entityId : $this->bol_process_status->entityId,
                    'eventType' => $resp_object->eventType,
                    'description' => isset($resp_object->description) ? $resp_object->description : $this->bol_process_status->description,
                    'status' => $resp_object->status,
                    'errorMessage' => isset($resp_object->errorMessage) ? $resp_object->errorMessage : $this->bol_process_status->errorMessage,
                    'createTimestamp' => isset($resp_object->createTimestamp) ? $resp_object->createTimestamp : $this->bol_process_status->createTimestamp,
                    'link_to_self' => isset($resp_object->links[0]->href) ? $resp_object->links[0]->href : $this->bol_process_status->link_to_self,
                    'method_to_self' => isset($resp_object->links[0]->method) ? $resp_object->links[0]->method : $this->bol_process_status->method_to_self,
                    ]);






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
