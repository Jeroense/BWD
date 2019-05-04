<?php

namespace App\Http\Controllers;

use App\BolProduktieOffer;
use App\BolProcesStatus;
use Illuminate\Http\Request;
use App\Http\Traits\BolApiV3;

class BolProduktieOfferController extends Controller
{
    use BolApiV3;

    public function index(){

        $bol_produktie_offers = BolProduktieOffer::all();

        return view('boloffers.index', compact('bol_produktie_offers'));
    }


    public function prepare_CSV_Offer_Export_PRODUCTION(){

        $bol_generate_offers_csv = $this->prepare_CSV_Offers_export_prod();


        if( ($bol_generate_offers_csv['bolstatuscode'] != 202) ||  (!isset($bol_generate_offers_csv['bolbody']) ) ){

            dd( 'response op de aanmaak request voor een offer-export is niet 200!');
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

    public function check_if_CSV_Offer_Export_PRODUCTION_RDY(){   // nog niet af..

        $laatste_proc_status = BolProcesStatus::latest()->first();
        // rest-endpoint uit de BolProcesStatus halen
        $rest_endpoint = $laatste_proc_status->link_to_self;
        // $proces_status_response = $this->make_V3_PlazaApiRequest();

        return view('boloffers.iscsvexportready');
    }
}
