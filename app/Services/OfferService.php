<?php

namespace App\Services;
// use Illuminate\Http\Request;

use App\Http\Traits\BolApiV3;
use App\Http\Traits\DebugLog;
// use App\CustomVariant;
use App\BolProduktieOffer;
use App\BolProcesStatus;
use App\Jobs\GetBolOffersJob;
use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;

class OfferService
{
        use BolApiV3;


        // doe initiele opdracht tot aanmaken csv-offer-export-file, en zet de process-status response in 'bol_proces_statuses'
        public function prepare_CSV_Offer_Export($serverType){

            $bol_generate_offers_csv_resp = $this->prepare_CSV_Offers_export($serverType);


            if( ($bol_generate_offers_csv_resp['bolstatuscode'] != 202) ||  (!isset($bol_generate_offers_csv_resp['bolbody']) ) ){

                dump( 'response op de aanmaak request voor een offer-export is niet 202!');

                return 'statuscode geen 202';
            }

            $process_status_object = json_decode($bol_generate_offers_csv_resp['bolbody']);

            $latest_proc_status_db_entry = BolProcesStatus::where(['process_status_id' => $process_status_object->id,
               'eventType' => $process_status_object->eventType ])->latest()->first();

               // na ::where([])->latest()->first(), kreeg ik hier errors met ->exists() dus dan maar met != null controleren of aanwezig
               if($latest_proc_status_db_entry != null){

                $this->update_Bol_Process_Status($process_status_object);
               }
               else{
                $this->create_Bol_Process_Status_after_POST_Make_offer_Export_CSV($process_status_object);
               }

            return;
        }


        public function create_Bol_Process_Status_after_POST_Make_offer_Export_CSV(\stdClass $process_status_object){

            BolProcesStatus::create(['process_status_id' => $process_status_object->id,
                                     'entityId' => isset( $process_status_object->entityId) ? $process_status_object->entityId : null,
                                     'eventType' => $process_status_object->eventType,
                                     'description' => isset($process_status_object->description) ? $process_status_object->description : null,
                                     'status' => $process_status_object->status,
                                     'errorMessage' => isset($process_status_object->errorMessage) ? $process_status_object->errorMessage : null,
                                     'createTimestamp' => $process_status_object->createTimestamp,
                                     'link_to_self' => $process_status_object->links[0]->href,
                                     'method_to_self' => $process_status_object->links[0]->method
                                     ]);

            // in de hoop, dat de bol api in staat is, direct na de initiele process-status response na een POST opdracht tot
            // het aanmaken van een offer-export-csv, om een geupdate process-status te geven, met een 'entityId'. neen. duurt langer. Heeft geen zin!
            // $this->getUpdatedProcessStatusFrom_prepare_CSV_Offer_Export_Response('prod', $process_status_object->id);

            file_put_contents( storage_path( 'app/public') . '/' . 'scheduler-log.txt', ((string)date('D, d M Y H:i:s') . "\r\n" . 'create_Bol_Process_Status_after_POST_Make_offer_Export_CSV is aangeroepen!'), FILE_APPEND );

            return;
        }


        public function update_Bol_Process_Status(\stdClass $process_status_object){

            // geen "entityId" aanwezig in response proces status na, POST/opdracht creeren prod-offer-csv-export.
            $de_BolProcesStatus = BolProcesStatus::where(['process_status_id' => $process_status_object->id,
                                                        //   'entityId' => isset($process_status_object->entityId) ? $process_status_object->entityId : null,
                                                          'eventType' => $process_status_object->eventType ])->first();

            // als er nieuwe data in $process_status_object aanwezig is: zet dit in 'bol_proces_statuses', zo niet? zet de oude waarde terug.
            $de_BolProcesStatus->update([
                                    //  'process_status_id' => $process_status_object->id,
                                    'entityId' => isset( $process_status_object->entityId) ? $process_status_object->entityId : $de_BolProcesStatus->entityId,
                                     'eventType' => $process_status_object->eventType,
                                     'description' => isset($process_status_object->description) ? $process_status_object->description : $de_BolProcesStatus->description,
                                     'status' => $process_status_object->status,
                                     'errorMessage' => isset($process_status_object->errorMessage) ? $process_status_object->errorMessage : $de_BolProcesStatus->errorMessage,
                                    //  'link_to_self' => $process_status_object->links[0]->href,
                                    //  'method_to_self' => $process_status_object->links[0]->method
                                     ]);
            return;
        }




        // als response(code) na opdracht tot aanmaak csv export 202 is, gelijk een GET doen naar 'link_to_self' uit deze response.
        // de response uit deze GET bevat, bij 200, als het goed is, het 'entityId', dit is hier: het offerexportId.
        // throttling: reqs 100/minuut toegestaan, nog.. geen job aanmaken , is maar 1 request
        public function getUpdatedProcessStatusFrom_prepare_CSV_Offer_Export_Response($serverType ,$process_status_id){

            $endpoint = "process-status/{$process_status_id}";
            $process_status_response = $this->make_V3_PlazaApiRequest($serverType, $endpoint);
            $bolStatusCode = $process_status_response['bolstatuscode'];

            if($bolStatusCode != 200){
                return "process-status/{$process_status_id} response statuscode is: {$bolStatusCode}";
            }

            if( !isset($process_status_response['bolbody']) ){
                return 'lege response body';
            }

            $process_status_body_as_object = json_decode($process_status_response['bolbody']);
            $this->update_Bol_Process_Status($process_status_body_as_object);

            return;
        }


        public function update_process_status_create_offer_export(){

            $latest_create_offer_export_db_entry = BolProcesStatus::where(['eventType' => 'CREATE_OFFER_EXPORT'])->latest()->first();

            // if( $latest_create_offer_export_db_entry->exists() ){  // geeft error, dus met != null controleren
            if( $latest_create_offer_export_db_entry != null ){  // geeft error, dus met != null controleren

                if($latest_create_offer_export_db_entry->status = 'PENDING'){
                    // $resp =  $this->make_V3_PlazaApiRequest('prod', "process-status/{$latest_create_offer_export_db_entry->process_status_id}");
                    $resp = $this->geefProcesStatusById('prod', $latest_create_offer_export_db_entry->process_status_id);

                    if($resp['bolstatuscode'] != 200){
                            return;
                    }

                    $status_data = json_decode($resp['bolbody']);

                    $latest_create_offer_export_db_entry->update([
                        'entityId' => isset( $status_data->entityId) ? $status_data->entityId : $latest_create_offer_export_db_entry->entityId,
                        'eventType' => $status_data->eventType,
                        'description' => isset($status_data->description) ? $status_data->description : $latest_create_offer_export_db_entry->description,
                        'status' => $status_data->status,
                        'errorMessage' => isset($status_data->errorMessage) ? $status_data->errorMessage : $latest_create_offer_export_db_entry->errorMessage,

                    ]);
                }

                $latest_create_offer_export_db_entry = BolProcesStatus::where(['eventType' => 'CREATE_OFFER_EXPORT'])->latest()->first();

                // nu het volgende: een flag in proces_statuses table zetten, of dat er vanuit deze db_entry al eens een offer-export-csv
                // succesvol opgehaald is (geweest).
                if($latest_create_offer_export_db_entry != null && $latest_create_offer_export_db_entry->status = 'SUCCESS' && $latest_create_offer_export_db_entry->csv_success == false){

                    $this->get_CSV_Offer_Export_PROD($latest_create_offer_export_db_entry);
                }

                return;
            }
            return;
        }




        public function get_CSV_Offer_Export_PROD(BolProcesStatus $process_status_model_instance){


            // $latest_succesfull_made_offer_export_db_entry = BolProcesStatus::where(['eventType' => 'CREATE_OFFER_EXPORT', 'status' => 'SUCCESS'])->latest()->first();

            // if( $latest_succesfull_made_offer_export_db_entry->exists() ){

                // $latest_csv_offer_export_id = $latest_succesfull_made_offer_export_db_entry->entityId;
                $latest_csv_offer_export_id = $process_status_model_instance->entityId;

                // slaat, bij 200, de verkregen csv offer export data op in file: storage_path( 'app/public') . '/' .  bol-get-csv-export-response-{$serverType}.csv
                $csvFileName = $this->getCSVOfferExportPROD('prod', $latest_csv_offer_export_id);

                if($csvFileName == 'status niet 200'){
                    return 'BOL response-code voor reply vanuit laatste DB offer-export entry is geen 200!';
                }

                $csv_array = $this->zet_CSV_export_file_om_in_array($csvFileName);

                if($csv_array == 'no csv file!'){
                    return 'no csv file!';
                }

                // hier wellicht goed punt in code om: de flag: "csv_success" op true te zetten.
                // op dit punt is is er een up-to-date csv-file aangemaakt.
                $process_status_model_instance->update(['csv_success' => 1]);   // boolean = tinyint  true is 1


                $this->zet_CSV_array_Data_in_BOL_produktie_offers_table($csv_array);
                // dump('in get_CSV_Offer_Export_PROD');

                return;
            // }
        }


        public function zet_CSV_array_Data_in_BOL_produktie_offers_table($csv_arr){


            foreach($csv_arr as $key ){
                if( BolProduktieOffer::where( ['offerId' => $key['offerId'], 'ean' => $key['ean'] ])->first()->exists() ){

                    $te_updaten_prod_offer = BolProduktieOffer::where( ['offerId' => $key['offerId'], 'ean' => $key['ean'] ])->first();
                    $te_updaten_prod_offer->update([

                        'fulfilmentConditionName' => $key['conditionName'],
                        'fulfilmentConditionCategory' => $key['conditionCategory'],
                        'bundlePricesPrice' => $key['bundlePricesPrice'],
                        'fulfilmentDeliveryCode' => $key['fulfilmentDeliveryCode'],
                        'stockAmount' => $key['stockAmount'],
                        'onHoldByRetailer' => $key['onHoldByRetailer'] == 'true' ? true : false,
                        'fulfilmentType' => $key['fulfilmentType']
                        // mutationDateTime nog te doen, wat is dit format?
                    ]);
                }

                if(BolProduktieOffer::where( ['offerId' => $key['offerId'], 'ean' => $key['ean'] ])->first()->doesntExist() ){

                    BolProduktieOffer::create([
                        'offerId' =>  $key['offerId'],
                        'ean' =>  $key['ean'],
                        'fulfilmentConditionName' =>  $key['conditionName'],
                        'fulfilmentConditionCategory' =>  $key['conditionCategory'],
                        'bundlePricesPrice' =>  $key['bundlePricesPrice'],
                        'fulfilmentDeliveryCode' =>  $key['fulfilmentDeliveryCode'],
                        'stockAmount' =>  $key['stockAmount'],
                        'onHoldByRetailer' =>  $key['onHoldByRetailer'] == 'true' ? true : false,
                        'fulfilmentType' =>  $key['fulfilmentType'],
                         // mutationDateTime nog te doen, wat is dit format?
                    ]);
                }
            }
            $this->get_BolOffers_By_Id();
        }


        // om de bol_produktie_offers table te updaten, het best NA een succesvol verwerkte get-CSV-offer-export request.
        // de toegelaten rate van deze request is: ca. 28 requests per 3 seconden, is ca. 9 req/ sec
        public function get_BolOffers_By_Id(){

            $bol_prod_offers_in_db = BolProduktieOffer::all();

            if($bol_prod_offers_in_db->count() == 0){
                return 'Geen bol produktie offers in lokale DB!';
            }
                foreach($bol_prod_offers_in_db as $lokale_db_offers){
                // "offerId":"38dff9a2-dc45-4201-85f2-cb0ae0cd80d5","ean":"7435156898868"  // dit is het produkt dat ik heb aangemaakt zonder catalog bekend ean

                GetBolOffersJob::dispatch('prod', $lokale_db_offers->offerId);


                }
        }


                // public function check_if_CSV_Offer_Export_RDY($serverType){

        //     // nu laatste proces-status ophalen waar 'eventType' = 'CREATE_OFFER_EXPORT'
        //     $laatste_create_offer_export_proc_status_db_entry = BolProcesStatus::where(['eventType' => 'CREATE_OFFER_EXPORT'])->latest()->first();
        //     // rest-endpoint uit de BolProcesStatus halen
        //     $process_status_id = $laatste_create_offer_export_proc_status_db_entry->process_status_id;

        //     $process_status_by_id_response = $this->geefProcesStatusById($serverType, $process_status_id);

        //     if($process_status_by_id_response['bolstatuscode'] == 200 && strpos($process_status_by_id_response['bolheaders']['Content-Type'][0], 'json') != false ){

        //         $resp_object = json_decode($process_status_by_id_response['bolbody']);

        //         if($laatste_create_offer_export_proc_status_db_entry->process_status_id == $resp_object->id){ // wellicht overbodige controle

        //             // bij 'mass-assignment' (met->update([])) wordt de timestamps -> updated_at hier nu niet bijgewerkt?
        //             $laatste_create_offer_export_proc_status_db_entry->update([

        //                 'entityId' => isset( $resp_object->entityId) ? $resp_object->entityId : null,
        //                 'eventType' => $resp_object->eventType,
        //                 'description' => isset($resp_object->description) ? $resp_object->description : null,
        //                 'status' => $resp_object->status,
        //                 'errorMessage' => isset($resp_object->errorMessage) ? $resp_object->errorMessage : null,

        //             ]);
        //                 dump('laatse offer-export db-entry is ge-updated!!');
        //             // $laatste_db_entry = BolProcesStatus::where(['eventType' => 'CREATE_OFFER_EXPORT'])->latest()->first();

        //             return;
        //         }
        //     }
        // }


}
