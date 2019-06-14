<?php

namespace App\Http\Controllers;

use App\BolProduktieOffer;
use App\BolProcesStatus;
use App\CustomVariant;
use App\Jobs\GetBolOffersJob;
use App\Jobs\UploadBolOffersJob;
use App\Jobs\DeleteBolOffersJob;
use Illuminate\Http\Request;
use App\Http\Traits\BolApiV3;
use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;
use Symfony\Component\Finder\Iterator\CustomFilterIterator;

class BolProduktieOfferController extends Controller
{
    use BolApiV3;

    public function index()
    {

        $bol_produktie_offers = BolProduktieOffer::all();

        foreach($bol_produktie_offers as $offer)
        {
            $custVariant = CustomVariant::where(['ean' => $offer['ean']])->first();

            if($custVariant != null)
            {

                $offer['fileName'] = $custVariant->fileName;
            }


        }

        return view('boloffers.index', compact('bol_produktie_offers'));
    }


    public function select_customvariants_to_publish_on_BOL()
    {


        $notYetPublishedCustomVariants = CustomVariant::where('isPublishedAtBol',  '=',  null)
                ->orWhere('isPublishedAtBol',  '=',  '')->orWhere('isPublishedAtBol',  '=',  'unpublish_at_api_initiated')
                ->orWhere('isPublishedAtBol',  '=',  'unpublished_at_api')->get();


        return view('boloffers.publish.select', ['cvars' => $notYetPublishedCustomVariants] );
    }


    public function updateBolOffer(BolProduktieOffer $offer)
    {
        // je kunt de properties van een model aanroepen/zetten als een object (met '$offer->ean'), maar ook als een array, (met $offer['ean'])
        $custVariant = CustomVariant::where(['ean' => $offer->ean])->first();

        if($custVariant != null)
        {

            $offer['fileName'] = $custVariant->fileName;
            // $offer->fileName = $custVariant->fileName;  // dit kan/mag ook! een nieuwe property toevoegen en zetten.
        }
        // dd($offer);
        return view('boloffers.update', compact('offer'));
    }


    public function update_price_BolOffer(BolProduktieOffer $offer, Request $req)
    {  // regex:/^\d+(\.\d{1,2})?$/  voor 2 decimalen achter de .
        $req->validate([
                         "salePrice" => "numeric|required|regex:/^\d+(\.\d{1,2})?$/|max:9999"
        ]);


        $new_price = (float)$req['salePrice'];

        $price_object = new \stdClass();
        $price_object->quantity = 1;   // dit is standaard, en alleen 1 is nu toegestaan door bol
        $price_object->price = $new_price;

        $bundle_prices_object = new \stdClass();
        $bundle_prices_object->bundlePrices = [$price_object];


        $update_price_body_object = new \stdClass();
        $update_price_body_object->pricing = $bundle_prices_object;

        $put_body_for_price_update = json_encode($update_price_body_object);

        // dump( $put_body_for_price_update );
        // dump($offer); dump($req->all()); dump( (float)$req['salePrice']);

        $bol_prijs_update_response = $this->make_V3_PlazaApiRequest("prod", "offers/{$offer->offerId}/price", "put", $put_body_for_price_update);

        // log antwoord vanuit bol, je krijgt een proces-status terug
        $this->putResponseInFile("updateProduktieOfferPrijs-response-prod.txt", $bol_prijs_update_response['bolstatuscode'],
                                $bol_prijs_update_response['bolreasonphrase'], $bol_prijs_update_response['bolbody']);
        //

        // dan BolProcesStatus table updaten met de response
        if($bol_prijs_update_response['bolstatuscode'] != 202 || !isset($bol_prijs_update_response['bolbody']))
        {
            return  redirect()->back()->withInput()->with(['code'=> $bol_prijs_update_response['bolstatuscode'],
                                                           'phrase' => $bol_prijs_update_response['bolreasonphrase']
                                                          ]);
        }

        $process_status_info = json_decode($bol_prijs_update_response['bolbody']);

        BolProcesStatus::create([
            'process_status_id' => $process_status_info->id,
            'entityId' => $process_status_info->entityId,
            'eventType' => $process_status_info->eventType,
            'description' => $process_status_info->description,
            'status' => $process_status_info->status,
            'errorMessage' => isset($process_status_info->errorMessage) ? $process_status_info->errorMessage : null,
            'createTimestamp' => $process_status_info->createTimestamp,
            'link_to_self' => $process_status_info->links[0]->href,
            'method_to_self' => $process_status_info->links[0]->method

        ]);

        return view('boloffers.priceupdated', ['offer' => $offer,
                                               'price' => $req->input('salePrice'),
                                              ]);
    }


    public function update_onhold_and_deliverycode_BolOffer(BolProduktieOffer $offer, Request $req)
    {
        $req->validate([
            "deliveryCode" => "required"
        ]);

        // is input met naam 'onhold' aanwezig? dan value  'on' in $req aanwezig, zo niet dan ''off - not on hold'
         $onhold = $req->input('onhold', 'off - not on hold');
        //

        // maak body aan voor put request om dit bol-offer te updaten voor 'onhold' en 'deliverycode'
        $fulfilment_object = new \stdClass();
        $fulfilment_object->type = 'FBR';
        $fulfilment_object->deliveryCode = $req->input('deliveryCode');

        $update_onhold_and_deliveryCode_object_voor_put = new \stdClass();
        $update_onhold_and_deliveryCode_object_voor_put->onHoldByRetailer = $onhold == 'on' ? true : false;
        $update_onhold_and_deliveryCode_object_voor_put->fulfilment = $fulfilment_object;

        $json_put_body = json_encode($update_onhold_and_deliveryCode_object_voor_put);
        //

        // maak update request naar bol-produktie ivm update deliveryCode en 'onhold by retailer'
        $bol_offer_update_resp = $this->make_V3_PlazaApiRequest("prod", "offers/{$offer->offerId}", "put", $json_put_body);
        //


        // log antwoord vanuit bol, je krijgt een proces-status terug
        $this->putResponseInFile("updateProduktieOffer-OnHold-DeliveryCode-response-prod.txt", $bol_offer_update_resp['bolstatuscode'],
        $bol_offer_update_resp['bolreasonphrase'], $bol_offer_update_resp['bolbody']);
        //

        // check status
        if($bol_offer_update_resp['bolstatuscode'] != 202 || empty($bol_offer_update_resp['bolbody']))
        {
            return redirect()->back()->withInput()->with(['code'=> $bol_offer_update_resp['bolstatuscode'],
                                                          'phrase' => $bol_offer_update_resp['bolreasonphrase']
                                                        ]);
        }

        $process_status_info = json_decode($bol_offer_update_resp['bolbody']);

        // voor zekerheid om crashes tegen te gaan
        if( $process_status_info->eventType != 'UPDATE_OFFER' || empty($process_status_info->status))
        {
            return;
        }

        BolProcesStatus::create([
            'process_status_id' => $process_status_info->id,
            'entityId' => $process_status_info->entityId,       // deze moet in principe aanwezig zijn, het is een put van een bestaand offer
            'eventType' => $process_status_info->eventType,
            'description' => $process_status_info->description,
            'status' => $process_status_info->status,
            'errorMessage' => isset($process_status_info->errorMessage) ? $process_status_info->errorMessage : null,
            'createTimestamp' => $process_status_info->createTimestamp,
            'link_to_self' => $process_status_info->links[0]->href,
            'method_to_self' => $process_status_info->links[0]->method

        ]);

        // dan alvast de deliverycode in de customVariants-table updaten. Deze wordt na elke succesvolle
        // csv-offer export weer ge-update. Zo klopt het veld 'Del.Code' op url: /boloffers/boloffer-check-initial-status
        $custVar = CustomVariant::where(['ean' => $offer->ean])->first();
        if($custVar != null)
        {
            $custVar->update(['boldeliverycode' => $req->input('deliveryCode')]);
        }

        // dump($offer); dump($req->all()); dump($onhold);

        return view('boloffers.onholdanddeliverycodeupdated', ['offer' => $offer,
                                                               'deliverycode' => $req->input('deliveryCode'),
                                                               'onhold' => $req->input('onhold')
                                                               ]);

    }


    public function update_stock_BolOffer(BolProduktieOffer $offer, Request $req)
    {
        // nog te doen: 'managedByRetailer' !  voorlopig zet ik dit op: true
        $req->validate([
            "stock" => "numeric|required|max:999"
        ]);

        // maak put-body-object aan voor updaten van stock op bol
        $stock_object = new \stdClass();
        $stock_object->amount = $req->input('stock');
        $stock_object->managedByRetailer = true;

        $put_body = json_encode($stock_object);
        //

        // maak update request naar bol-produktie ivm stock update voor dit bol-offer
        $bol_offer_update_resp = $this->make_V3_PlazaApiRequest("prod", "offers/{$offer->offerId}/stock", "put", $put_body);
        //

        // log antwoord vanuit bol, je krijgt een proces-status terug
        $this->putResponseInFile("updateProduktieOffer-Stock-response-prod.txt", $bol_offer_update_resp['bolstatuscode'],
        $bol_offer_update_resp['bolreasonphrase'], $bol_offer_update_resp['bolbody']);
        //

                // dan BolProcesStatus table updaten met de response
                if($bol_offer_update_resp['bolstatuscode'] != 202 || empty($bol_offer_update_resp['bolbody']))
                {
                    return redirect()->back()->withInput()->with(['code'=> $bol_offer_update_resp['bolstatuscode'],
                                                     'phrase' => $bol_offer_update_resp['bolreasonphrase']
                                                    ]);
                }

                $process_status_info = json_decode($bol_offer_update_resp['bolbody']);

                BolProcesStatus::create([
                    'process_status_id' => $process_status_info->id,
                    'entityId' => $process_status_info->entityId,       // deze krijg je gelijk terug, heb je zelf meegegeven als offerId
                    'eventType' => $process_status_info->eventType,
                    'description' => $process_status_info->description,
                    'status' => $process_status_info->status,
                    'errorMessage' => isset($process_status_info->errorMessage) ? $process_status_info->errorMessage : null,
                    'createTimestamp' => $process_status_info->createTimestamp,
                    'link_to_self' => $process_status_info->links[0]->href,
                    'method_to_self' => $process_status_info->links[0]->method

                ]);

                \App\OfferDataUploadedToBol::create([
                    'process_status_id' => $process_status_info->id,
                    'eventType' => $process_status_info->eventType,
                    'offerId' => $offer->offerId,
                    'ean' => $offer->ean,
                    'stock' => $req->input('stock'),
                    'stockManagedByRetailer' => true
                ]);

        return view('boloffers.stockupdated', ['offer' => $offer, 'stock' => $req->input('stock')]);
        // dump($offer); dump($req->all());
    }



    // public function updatedBolOffer(Request $req)
    // {

    //     dd($req);
    //     // return view('boloffers.updated', compact('offer'));
    // }



    public function deleteBolOffer(BolProduktieOffer $offer)
    {

        // dump($offer);
        DeleteBolOffersJob::dispatch($offer, 'prod');

        return view('boloffers.offerdeleted', compact('offer'));
    }



    public function dump_and_upload_offers_to_be_published_on_BOL(Request $req)
    {



        $inputs_from_request = $req->all();
        if( \key_exists('_token', $inputs_from_request) )
        {

            unset($inputs_from_request['_token'] );
        }
        // dd($inputs_from_request);

        $arr_van_te_valideren_stockfor_input_namen = [];
        $arr_van_te_valideren_salePrice_input_namen = [];
        $arr_van_te_valideren_deliveryCode_input_namen = [];

        $hoofd_array = [];          $temp_array = [];
        $updated_hoofd_array = [];  $updated_temp_array = [];

        foreach($inputs_from_request as $key => $val)
        {

            $temp_array[$key] = $val;

            if( strpos($key, 'baseColor') !== false )    // moet hier !== gebruiken..
            {

                array_push($hoofd_array, $temp_array);
                    $temp_array = [];
            }
        }
        // dd($hoofd_array);

        // om te bepalen wat de namen zijn van de stockfor_ , salePrice_ en deliveryCode_  input velden, die validated moeten worden..
        foreach($hoofd_array as $arr)
        {
            $arrkeys = array_keys($arr);
            foreach($arrkeys as $key)
            {
                if( strpos($key, 'publish') !== false)
                {
                    $de_publish_key_naam = $key;
                    $de_bijhorende_stockfor_input_naam = \str_replace('publish', 'stockfor', $de_publish_key_naam );
                    $de_bijhorende_salePrice_input_naam = \str_replace('publish', 'salePrice', $de_publish_key_naam );
                    $de_bijhorende_deliveryCode_input_naam = \str_replace('publish', 'deliveryCode', $de_publish_key_naam );

                    array_push($arr_van_te_valideren_stockfor_input_namen, $de_bijhorende_stockfor_input_naam);
                    array_push($arr_van_te_valideren_salePrice_input_namen, $de_bijhorende_salePrice_input_naam);
                    array_push($arr_van_te_valideren_deliveryCode_input_namen, $de_bijhorende_deliveryCode_input_naam);
                }
            }
        }
        // dump($arr_van_te_valideren_stockfor_input_namen);

        // valideren van alleen de stockfor_ inputs bij offers, die een key: publish_ hebben..
            foreach($arr_van_te_valideren_stockfor_input_namen as $stockfor_input_naam)
            {
                $req->validate([
                                $stockfor_input_naam => 'required|integer|max:999',
                                ]);
            }

            foreach($arr_van_te_valideren_salePrice_input_namen as $salePrice_input_naam)
            {
                $req->validate([
                                 $salePrice_input_naam => 'numeric|required|regex:/^\d+(\.\d{1,2})?$/|max:9999',
                                ]);
            }

            foreach($arr_van_te_valideren_deliveryCode_input_namen as $deliveryCode_input_naam)
            {
                $req->validate([
                                $deliveryCode_input_naam => 'required|string',
                                ]);
            }

            // dump($arr_van_te_valideren_deliveryCode_input_namen);
            // dump($arr_van_te_valideren_salePrice_input_namen);
            // dd($arr_van_te_valideren_stockfor_input_namen);

        foreach($hoofd_array as $arr)
        {

            foreach($arr as $key => $val)
            {
                $arr[substr($key, 0, 3)] = $val;
                unset($arr[$key]);
            }
           array_push($updated_hoofd_array, $arr);
        }

        // dd($updated_hoofd_array);

        $array_met_alle_offer_objecten = $this->maak_ObjectenArray_voor_single_offers_BOL_from_Array($updated_hoofd_array);

        session(['offer_arr' => $array_met_alle_offer_objecten]);

        // dd($array_met_alle_offer_objecten); return;

        foreach($array_met_alle_offer_objecten as $offer)
        {

            $json_offer = json_encode($offer);
            UploadBolOffersJob::dispatch('prod', $json_offer);  // hier kiezen: 'prod' of 'demo' server!!!
        }



        // $collection = collect($array_met_alle_offer_objecten);
        // $sent_eans = $collection->pluck('ean');
        // $custVars_sent_to_bol = CustomVariant::whereIn('ean', $sent_eans)->get();

        foreach($array_met_alle_offer_objecten as $offer)
        {
            $offer->fileName = CustomVariant::where(['ean' => $offer->ean])->value('fileName'); // dit werkt..
            // dump($offer);
        }

        return view('boloffers.publish.sent',  ['sent' => $array_met_alle_offer_objecten]);
    }

    public function checkBolOffersStatus()
    {
        $custVars_with_publish_at_api_initiated = CustomVariant::where(['isPublishedAtBol' => 'publish_at_api_initiated'])
            ->orWhere( ['isPublishedAtBol' => 'unpublish_at_api_initiated'])->get();

        return view('boloffers.publish.initiated', ['cvars' => $custVars_with_publish_at_api_initiated]);
    }

    // heb het idee dat: ook al zit je throttle-matig (ca 10 reqs/uur) nog binnen de grenzen,
    // als je binnen enkele minuten, b.v. 3 x achter elkaar een nieuwe POST prepare_CSV_Offer_Export_PRODUCTION() doet,
    // de plaza-api met 500 errort. Plaza-api kan dit niet aan.
    public function prepare_CSV_Offer_Export_PRODUCTION(){

        $bol_generate_offers_csv_resp = $this->prepare_CSV_Offers_export('prod');


        if( ($bol_generate_offers_csv_resp['bolstatuscode'] != 202) ||  (!isset($bol_generate_offers_csv_resp['bolbody']) ) ){

            dump( 'response op de aanmaak request voor een offer-export is niet 202!');

            return 'statuscode geen 202';
        }

        $process_status_object = json_decode($bol_generate_offers_csv_resp['bolbody']);

        $latest_proc_status_db_entry = BolProcesStatus::where(['process_status_id' => $process_status_object->id,
        // 'entityId' => isset( $process_status_object->entityId) ? $process_status_object->entityId : null,
           'eventType' => $process_status_object->eventType ])->latest()->first(); // als ::where([])->latest()->first(), dan geen ->exists() mogelijk, dan met != null controleren of aanwezig

           if($latest_proc_status_db_entry != null){

            $this->update_Bol_Process_Status($process_status_object);
           }
           else{
            $this->create_Bol_Process_Status($process_status_object);
           }

        // BolProcesStatus::where(['process_status_id' => $process_status_object->id,
        //                      // 'entityId' => isset( $process_status_object->entityId) ? $process_status_object->entityId : null,
        //                         'eventType' => $process_status_object->eventType ])->latest()->first()->exists() ? $this->update_Bol_Process_Status($process_status_object) : $this->create_Bol_Process_Status($process_status_object);

        $process_status = BolProcesStatus::where(['process_status_id' => $process_status_object->id,
                                             //   'entityId' =>  isset( $process_status_object->entityId) ? $process_status_object->entityId : null,
                                                  'eventType' => $process_status_object->eventType ])->latest()->first();

        dump($process_status);
            // in de hoop, dat de bol api in staat is, direct na de initiele process-status response na een POST opdracht tot
            // het aanmaken van een offer-export-csv, om een geupdate process-status te geven, met een 'entityId'
            sleep(1); // neen. tijd is te kort.. met scheduler op intervals de proces-statussen nagaan.
            $this->getUpdatedProcessStatusFrom_prepare_CSV_Offer_Export_Response('prod', $process_status_object->id);

        return view('boloffers.generateofferscsv', compact('process_status'));
    }


    public function update_Bol_Process_Status(\stdClass $process_status_object){

            // geen "entityId" aanwezig in response proces status na, POST/opdracht creeren prod-offer-csv-export.
            $de_BolProcesStatus = BolProcesStatus::where(['process_status_id' => $process_status_object->id,
                                                        //   'entityId' => isset($process_status_object->entityId) ? $process_status_object->entityId : null,
                                                          'eventType' => $process_status_object->eventType ])->latest()->first();

            dump('In: update_Bol_Process_Status(\stdClass $process_status_object). r.77');
            dump($de_BolProcesStatus);

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
            dump('In: update_Bol_Process_Status(\stdClass $process_status_object). r.92');

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


        // als response(code) na opdracht tot aanmaak csv export 202 is, gelijk een GET doen naar 'link_to_self' uit deze response.
        // de response uit deze GET bevat, bij 200, als het goed is, het 'entityId', dit is hier: het offerexportId.
        // throttling: reqs 100/minuut toegestaan, nog.. geen job aanmaken , is maar 1 request
        public function getUpdatedProcessStatusFrom_prepare_CSV_Offer_Export_Response($serverType ,$process_status_id){

            $endpoint = "process-status/{$process_status_id}";
            $process_status_response =  $this->geefProcesStatusById($serverType, $process_status_id);
            // $process_status_response = $this->make_V3_PlazaApiRequest($serverType, $endpoint);
            $bolStatusCode = $process_status_response['bolstatuscode'];

            if($bolStatusCode != 200){
                return "process-status/{$process_status_id} response statuscode is: {$bolStatusCode}";
            }

            if( !isset($process_status_response['bolbody']) ){
                return 'lege response body';
            }

            $process_status_body_as_object = json_decode($process_status_response['bolbody']);
            $this->update_Bol_Process_Status($process_status_body_as_object);

            dump('In getUpdatedProcessStatusFrom_prepare_CSV_Offer_Export_Response()');
            dump($process_status_body_as_object);  //  hier na 1 seconde, nog geen 'entityId' in aanwezig!

            return redirect()->route('boloffers.index');
        }



    public function check_if_CSV_Offer_Export_PRODUCTION_RDY(){

        // nu laatste proces-status ophalen waar 'eventType' = 'CREATE_OFFER_EXPORT'
        $laatste_create_offer_export_proc_status_db_entry = BolProcesStatus::where(['eventType' => 'CREATE_OFFER_EXPORT'])->latest()->first();

        if($laatste_create_offer_export_proc_status_db_entry->doesntExist()){

            dump('geen bol_proces_status entry aanwezig met ["eventType" => "CREATE_OFFER_EXPORT"]');
            return 'geen CREATE_OFFER_EXPORT entry in bol_proces_statuses-table!';
        }

        // rest-endpoint uit de BolProcesStatus halen
        $process_status_id = $laatste_create_offer_export_proc_status_db_entry->process_status_id;

        $process_status_by_id_response = $this->geefProcesStatusById('prod', $process_status_id);

        if($process_status_by_id_response['bolstatuscode'] == 200 && strpos($process_status_by_id_response['bolheaders']['Content-Type'][0], 'json') != false ){

            $resp_object = json_decode($process_status_by_id_response['bolbody']);

            if($laatste_create_offer_export_proc_status_db_entry->process_status_id == $resp_object->id){

                // bij 'mass-assignment' (met->update([])) zouden de timestamps -> updated_at bijgewerkt moeten worden, hier niet?
                // onderstaande update-functie werkt wel.
                $laatste_create_offer_export_proc_status_db_entry->update([

                    'entityId' => isset( $resp_object->entityId) ? $resp_object->entityId : $laatste_create_offer_export_proc_status_db_entry->entityId,
                    'eventType' => $resp_object->eventType,
                    'description' => isset($resp_object->description) ? $resp_object->description :  $laatste_create_offer_export_proc_status_db_entry->description,
                    'status' => $resp_object->status,
                    'errorMessage' => isset($resp_object->errorMessage) ? $resp_object->errorMessage :  $laatste_create_offer_export_proc_status_db_entry->errorMessage,

                ]);
                $laatste_create_offer_export_proc_status_db_entry->touch(); // om updated_at te forceren?

                    dump('laatse offer-export db-entry is ge-updated!!');

                $laatste_db_entry = BolProcesStatus::where(['eventType' => 'CREATE_OFFER_EXPORT'])->latest()->first();

                return view('boloffers.iscsvexportready', compact('laatste_db_entry'));
            }
        }
    }

    public function get_CSV_Offer_Export_PROD(){


        $latest_succesfull_made_offer_export_db_entry = BolProcesStatus::where(['eventType' => 'CREATE_OFFER_EXPORT', 'status' => 'SUCCESS'])->latest()->first();

        if( $latest_succesfull_made_offer_export_db_entry->exists() ){  // nu werkt ->exists() WEL..met ->latest()->first().. ???
            dump('regel 333 werkt! $latest_succesfull_made_offer_export_db_entry->exists() geeft geen error..');

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


            // $bol_produktie_offers = BolProduktieOffer::all();

            // return view('boloffers.index', compact('bol_produktie_offers'));
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

