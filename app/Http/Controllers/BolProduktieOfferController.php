<?php

namespace App\Http\Controllers;

use App\BolProduktieOffer;
use App\BolProcesStatus;
use App\Jobs\GetBolOffersJob;
use Illuminate\Http\Request;
use App\Http\Traits\BolApiV3;
use function GuzzleHttp\json_decode;

class BolProduktieOfferController extends Controller
{
    use BolApiV3;

    public function index(){

        $bol_produktie_offers = BolProduktieOffer::all();

        return view('boloffers.index', compact('bol_produktie_offers'));
    }


    public function prepare_CSV_Offer_Export_PRODUCTION(){

        $bol_generate_offers_csv = $this->prepare_CSV_Offers_export('prod');


        if( ($bol_generate_offers_csv['bolstatuscode'] != 202) ||  (!isset($bol_generate_offers_csv['bolbody']) ) ){

            dd( 'response op de aanmaak request voor een offer-export is niet 202!');
        }

        $process_status_object = json_decode($bol_generate_offers_csv['bolbody']);

        BolProcesStatus::where(['process_status_id' => $process_status_object->id,
                                'entityId' => isset( $process_status_object->entityId) ? $process_status_object->entityId : null,
                                'eventType' => $process_status_object->eventType ])->exists() ? $this->update_Bol_Process_Status($process_status_object) : $this->create_Bol_Process_Status($process_status_object);

        $process_status = BolProcesStatus::where(['process_status_id' => $process_status_object->id,
                                                  'entityId' =>  isset( $process_status_object->entityId) ? $process_status_object->entityId : null,
                                                  'eventType' => $process_status_object->eventType ])->first();

        // echo('Bol response body als stdClass na json_decode:');
        // dump($process_status_object);
        // echo('BolProcesStatus::where');
        // dump($process_status);
        return view('boloffers.generateofferscsv', compact('process_status'));
    }


    public function update_Bol_Process_Status(\stdClass $process_status_object){

        // geen "entityId" in link naar proces status na POST/opdracht creeren prod-offer-csv-export?
        $de_BolProcesStatus = BolProcesStatus::where(['process_status_id' => $process_status_object->id,
                                                      'entityId' => isset($process_status_object->entityId) ? $process_status_object->entityId : null,
                                                      'eventType' => $process_status_object->eventType ])->first();

        $de_BolProcesStatus->update([
                                //  'process_status_id' => $process_status_object->id,
                                'entityId' => isset( $process_status_object->entityId) ? $process_status_object->entityId : null,
                                 'eventType' => $process_status_object->eventType,
                                 'description' => isset($process_status_object->description) ? $process_status_object->description : null,
                                 'status' => $process_status_object->status,
                                 'errorMessage' => isset($process_status_object->errorMessage) ? $process_status_object->errorMessage : null,
                                //  'link_to_self' => $process_status_object->links[0]->href,
                                //  'method_to_self' => $process_status_object->links[0]->method
                                 ]);
        return;
    }


    public function create_Bol_Process_Status(\stdClass $process_status_object){

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
        return;
    }

    public function check_if_CSV_Offer_Export_PRODUCTION_RDY(){

        // nu laatste proces-status ophalen waar 'eventType' = 'CREATE_OFFER_EXPORT'
        $laatste_create_offer_export_proc_status_db_entry = BolProcesStatus::where(['eventType' => 'CREATE_OFFER_EXPORT'])->latest()->first();
        // rest-endpoint uit de BolProcesStatus halen
        $process_status_id = $laatste_create_offer_export_proc_status_db_entry->process_status_id;

        $process_status_by_id_response = $this->geefProcesStatusById('prod', $process_status_id);

        if($process_status_by_id_response['bolstatuscode'] == 200 && strpos($process_status_by_id_response['bolheaders']['Content-Type'][0], 'json') != false ){

            $resp_object = json_decode($process_status_by_id_response['bolbody']);

            if($laatste_create_offer_export_proc_status_db_entry->process_status_id == $resp_object->id){

                // bij 'mass-assignment' (met->update([])) wordt de timestamps -> updated_at niet bijgewerkt!
                $laatste_create_offer_export_proc_status_db_entry->update([

                    'entityId' => isset( $resp_object->entityId) ? $resp_object->entityId : null,
                    'eventType' => $resp_object->eventType,
                    'description' => isset($resp_object->description) ? $resp_object->description : null,
                    'status' => $resp_object->status,
                    'errorMessage' => isset($resp_object->errorMessage) ? $resp_object->errorMessage : null,

                ]);
                    dump('laatse offer-export db-entry is ge-updated!!');
                $laatste_db_entry = BolProcesStatus::where(['eventType' => 'CREATE_OFFER_EXPORT'])->latest()->first();

                return view('boloffers.iscsvexportready', compact('laatste_db_entry'));
            }
        }
    }

    public function get_CSV_Offer_Export_PROD(){


        $latest_succesfull_made_offer_export_db_entry = BolProcesStatus::where(['eventType' => 'CREATE_OFFER_EXPORT', 'status' => 'SUCCESS'])->latest()->first();

        if( $latest_succesfull_made_offer_export_db_entry->exists() ){

            $latest_csv_offer_export_id = $latest_succesfull_made_offer_export_db_entry->entityId;

            // slaat, bij 200, de verkregen csv offer export data op in file: storage_path( 'app/public') . '/' .  bol-get-csv-export-response-{$serverType}.csv
            $csvFileName = $this->getCSVOfferExportPROD('prod', $latest_csv_offer_export_id);

            if($csvFileName == 'status niet 200'){
                return 'BOL response-code voor reply vanuit laatste DB offer-export entry is geen 200!';
            }

            $csv_array = $this->zet_CSV_export_file_om_in_array($csvFileName);

            if($csv_array == 'no csv file!'){
                return 'no csv file!';
            }

            $this->zet_CSV_array_Data_in_BOL_produktie_offers_table($csv_array);
            dump('in get_CSV_Offer_Export_PROD');

            return;
        }
    }


    public function zet_CSV_array_Data_in_BOL_produktie_offers_table($csv_arr){



        // $table->string('offerId');
        // $table->string('ean')->unique();    // unique om ev later aan gereferenced te worden door een FK uit andere table
        // $table->string('referenceCode')->nullable();
        // $table->boolean('onHoldByRetailer');
        // $table->string('unknownProductTitle')->nullable();
        // $table->unsignedInteger('bundlePricesQuantity')->nullable();
        // $table->double('bundlePricesPrice',8,2);
        // $table->unsignedInteger('stockAmount');
        // $table->unsignedInteger('correctedStock')->nullable();
        // $table->boolean('stockManagedByRetailer')->nullable();
        // $table->string('fulfilmentType');
        // $table->string('fulfilmentDeliveryCode');
        // $table->string('fulfilmentConditionName');
        // $table->string('fulfilmentConditionCategory');
        // $table->string('notPublishableReasonsCode')->nullable();
        // $table->string('notPublishableReasonsDescription')->nullable();
        // $table->dateTimeTz('mutationDateTime')->nullable();
        // $fg = BolProduktieOffer::where(['offerId' => $csv_file_as_array[0]['offerId']]);

        foreach($csv_arr as $key ){
            if( BolProduktieOffer::where( ['offerId' => $key['offerId'], 'ean' => $key['ean'] ])->exists() ){

                $te_updaten_prod_offer = BolProduktieOffer::where( ['offerId' => $key['offerId'], 'ean' => $key['ean'] ]);
                $te_updaten_prod_offer->update([

                    'fulfilmentConditionName' => $key['conditionName'],
                    'fulfilmentConditionCategory' => $key['conditionName'],
                    'bundlePricesPrice' => $key['bundlePricesPrice'],
                    'fulfilmentDeliveryCode' => $key['fulfilmentDeliveryCode'],
                    'stockAmount' => $key['stockAmount'],
                    'onHoldByRetailer' => $key['onHoldByRetailer'] == 'true' ? true : false,
                    'fulfilmentType' => $key['fulfilmentType']
                    // mutationDateTime nog te doen, wat is dit format?
                ]);
            }

            if(BolProduktieOffer::where( ['offerId' => $key['offerId'], 'ean' => $key['ean'] ])->doesntExist() ){

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



            // $bol_offer_by_id_response_array = $this->get_Bol_Offer_by_Id_PROD("prod", $lokale_db_offers->offerId);   // blijkt 28 reqs/sec toegestaan  retailer/offers/{offer_id}

            //     if($bol_offer_by_id_response_array['bolstatuscode'] != 200){
            //         return 'Error bij request naar offers/{offerId}. Status code niet 200 !';
            //     }
            // // $this->putResponseInFile("bol-offer-response-by-id-{$serverType}.txt", $bol_response_array['bolstatuscode'], $bol_response_array['bolreasonphrase'],
            // // $bol_response_array['bolbody'], $bol_response_array['x_ratelimit_limit'], $bol_response_array['x_ratelimit_reset'], $bol_response_array['x_ratelimit_remaining'], (string)time());
            //     if( isset($bol_offer_by_id_response_array['bolbody']) && strpos($bol_offer_by_id_response_array['bolheaders']['Content-Type'][0], 'json') != false ){

            //         $single_offer_as_stdclass = json_decode($bol_offer_by_id_response_array['bolbody']);

            //         $bol_offer_in_db = BolProduktieOffer::where(
            //                 ['offerId' => $single_offer_as_stdclass->offerId, 'ean' => $single_offer_as_stdclass->ean])->first();

            //         $product_title = '';
            //         $not_publishable_reason = 'Is publishable. No errors!';

            //         // ofwel de property: ->unknownProductTitle  ofwel property:  ->store->productTitle    is aanwezig in reply
            //         if( isset($single_offer_as_stdclass->unknownProductTitle) ){
            //             $product_title = $single_offer_as_stdclass->unknownProductTitle;
            //         }
            //         if( isset($single_offer_as_stdclass->store->productTitle) ){
            //             $product_title = $single_offer_as_stdclass->store->productTitle;
            //         }
            //         if( isset($single_offer_as_stdclass->notPublishableReasons[0]->description) ){                   // is niet aanwezig in response
            //             $not_publishable_reason = $single_offer_as_stdclass->notPublishableReasons[0]->description;  // als alles ok/publishable is
            //         }

            //         // nog eventueel alle response-velden controleren met isset()?
            //         $bol_offer_in_db->update([
            //             'referenceCode' => $single_offer_as_stdclass->referenceCode,
            //             'onHoldByRetailer' => $single_offer_as_stdclass->onHoldByRetailer,
            //             'unknownProductTitle' => $product_title,
            //             'bundlePricesQuantity' => $single_offer_as_stdclass->pricing->bundlePrices[0]->quantity,
            //             'bundlePricesPrice' => $single_offer_as_stdclass->pricing->bundlePrices[0]->price,
            //             'stockAmount' => $single_offer_as_stdclass->stock->amount,
            //             'correctedStock' => $single_offer_as_stdclass->stock->correctedStock,
            //             'stockManagedByRetailer' => $single_offer_as_stdclass->stock->managedByRetailer,
            //             'fulfilmentType' => $single_offer_as_stdclass->fulfilment->type,
            //             'fulfilmentDeliveryCode' => $single_offer_as_stdclass->fulfilment->deliveryCode,
            //             'fulfilmentConditionName' => $single_offer_as_stdclass->condition->name,
            //             'fulfilmentConditionCategory' => $single_offer_as_stdclass->condition->category,
            //             'notPublishableReasonsCode' => $single_offer_as_stdclass->notPublishableReasons[0]->code,
            //             'notPublishableReasonsDescription' => $not_publishable_reason
            //         ]);
            //         dump('bol_produktie_offers table succesvol geupdated voor offer-id: ');
            //         dump($single_offer_as_stdclass->offerId);
            //     }
            }  // endforeach
    }

}

