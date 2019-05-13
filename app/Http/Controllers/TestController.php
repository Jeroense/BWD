<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use App\CustomVariant;
use App\Http\Traits\BolApiV3;
use Illuminate\Support\Facades\Config;
use App\BolToken;
use App\BolProduktieOffer;
use function GuzzleHttp\json_encode;

class TestController extends Controller
{
    use BolApiV3;

    public function getBolOauth(){

        // putResponseInFile($fileName, $code, $phrase, $body)
        $this->request_BOL_Oauth_Token();

        return;
    }

    public function test_if_exists(){

        $offer = BolProduktieOffer::where(['ean' => '7435156898875', 'offerId' => '840ac44d-4348-27d8-e053-828b620a7e46'])->first();
        if($offer->exists()){
            dump('offer bestaat in DB!');dump($offer);
        }
        if($offer->doesntExist()){
            dump('offer bestaat niet in DB!');
        }
    }




    public function test_JSON_and_Session_Data(Request $req){
        $testCustVariant = CustomVariant::find(2);
        $allCustVars = CustomVariant::all();
        // $this->maak_JSON_voor_single_offer_BOL($testCustVariant, true);
        $last_token_entry = BolToken::latest()->first();
        $token_valid_until = $last_token_entry->at_unix_time + $last_token_entry->seconds_valid;
        session(['unix time'=> time()]);
        session(['boltoken geldig tot' => $token_valid_until]);
        // $req->session()->flash('eenmalige', 'flash_message' );
        $mysessionVar = $req->session()->all();
        dump($mysessionVar);
        // dump($req);
        // dump('$GLOBALS is: ');
        // dump($GLOBALS);
        $sess = session('unix time');
        dump($sess);

        return;
        // return $allCustVars; // alleen een return vanuit een Model(php/eloquent) wordt op deze manier als net(rood/blauw)
                            // geformat json in browser weergegeven, en alleen als je ALLEEN de eloquent models returned(geen dump() van iets
                            // anders ervoor, dan dumpt hij het zwart wit)

        // return $this->maak_JSON_voor_single_offer_BOL($testCustVariant, false);
    }

    public function test_Static_Storage(Request $req){

        // dump(\App\MyCustomClasses\StaticStorage::$process_status);
        // dump(Config::all());
        dump($req->session()->all());
        dump(session());
        dump(app('boltokendata'));
        return;
    }

    public function singletonWerkingVoorbeeldBoltokenData(){

        $de_token_singleton = app('boltokendata');
        $de_token_singleton->setBolTokenString('een test-token!');
        $de_token_singleton->setBolTokenValidUntil(100);
        dd($de_token_singleton, $de_token_singleton);
    }

    public function prepare_CSV_Offer_Export_DEMO(){

        $this->prepare_CSV_Offers_export('demo');
    }

    public function  prepare_CSV_Offer_Export_PROD(){

        $this->prepare_CSV_Offers_export('prod');
    }

    public function uploadSingleOfferToBolV3_DEMO(){

        $this->upload_Single_Offer_To_BOL_V3_DEMO(1);
    }



    public function uploadSingleOfferToBolV3_PROD(){

        $this->upload_Single_Offer_To_BOL_V3_PROD(2);
    }

    // public function uploadMultipleOffersToBolV3_DEMO(){

    //     $custVarCollection = CustomVariant::all();
    //     if($custVarCollection->count() > 0){
    //         $this->upload_Multiple_Offers_To_BOL_V3_DEMO($custVarCollection);
    //     }
    // }

    public function getBolOffers(){

        $bol_prod_offers_in_db = BolProduktieOffer::all();
        // hier nog job/queu van maken
        if($bol_prod_offers_in_db->count() == 0){
            return 'Geen bol produktie offers in lokale DB!';
        }
            foreach($bol_prod_offers_in_db as $lokale_db_offers){
            // "offerId":"38dff9a2-dc45-4201-85f2-cb0ae0cd80d5","ean":"7435156898868"  // dit is het produkt dat ik heb aangemaakt zonder catalog bekend ean
            $bol_offer_by_id_response_array = $this->get_Bol_Offer_by_Id_PROD("prod", $lokale_db_offers->offerId);   // blijkt 28 reqs/sec toegestaan  retailer/offers/{offer_id}

                if($bol_offer_by_id_response_array['bolstatuscode'] != 200){
                    return 'Error bij request naar offers/{offerId}. Status code niet 200 !';
                }
            // $this->putResponseInFile("bol-offer-response-by-id-{$serverType}.txt", $bol_response_array['bolstatuscode'], $bol_response_array['bolreasonphrase'],
            // $bol_response_array['bolbody'], $bol_response_array['x_ratelimit_limit'], $bol_response_array['x_ratelimit_reset'], $bol_response_array['x_ratelimit_remaining'], (string)time());
                if(isset($bol_offer_by_id_response_array['bolbody'])){
                    $single_offer_as_stdclass = json_decode($bol_offer_by_id_response_array['bolbody']);

                    $bol_offer_in_db = BolProduktieOffer::where(
                            ['offerId' => $single_offer_as_stdclass->offerId, 'ean' => $single_offer_as_stdclass->ean])->first();

                    $product_title = '';
                    $not_publishable_reason = 'Is publishable. No errors!';

                    // ofwel de property: ->unknownProductTitle  ofwel property:  ->store->productTitle    is aanwezig in reply
                    if( isset($single_offer_as_stdclass->unknownProductTitle) ){
                        $product_title = $single_offer_as_stdclass->unknownProductTitle;
                    }
                    if( isset($single_offer_as_stdclass->store->productTitle) ){
                        $product_title = $single_offer_as_stdclass->store->productTitle;
                    }
                    if( isset($single_offer_as_stdclass->notPublishableReasons[0]->description) ){   // is niet aanwezig in response
                        $not_publishable_reason = $single_offer_as_stdclass->notPublishableReasons[0]->description;// als alles ok/publishable is
                    }

                    $bol_offer_in_db->update([
                        'referenceCode' => $single_offer_as_stdclass->referenceCode,
                        'onHoldByRetailer' => $single_offer_as_stdclass->onHoldByRetailer,
                        'unknownProductTitle' => $product_title,
                        'bundlePricesQuantity' => $single_offer_as_stdclass->pricing->bundlePrices[0]->quantity,
                        'bundlePricesPrice' => $single_offer_as_stdclass->pricing->bundlePrices[0]->price,
                        'stockAmount' => $single_offer_as_stdclass->stock->amount,
                        'correctedStock' => $single_offer_as_stdclass->stock->correctedStock,
                        'stockManagedByRetailer' => $single_offer_as_stdclass->stock->managedByRetailer,
                        'fulfilmentType' => $single_offer_as_stdclass->fulfilment->type,
                        'fulfilmentDeliveryCode' => $single_offer_as_stdclass->fulfilment->deliveryCode,
                        'fulfilmentConditionName' => $single_offer_as_stdclass->condition->name,
                        'fulfilmentConditionCategory' => $single_offer_as_stdclass->condition->category,
                        'notPublishableReasonsCode' => $single_offer_as_stdclass->notPublishableReasons[0]->code,
                        'notPublishableReasonsDescription' => $not_publishable_reason
                    ]);
                }
            }
    }

    public function getBolOrdersV3(){

        $this->get_Orders_from_BOL_V3('demo');
    }

    public function getBolOrdersV3_PROD(){

        $this->get_Orders_from_BOL_V3('prod');
    }

    public function getBolOrderByIdV3(){
        // demo orderId's zijn: "orderId":"7616222250"   "orderId":"7616222700"  en  "orderId":"7616247328"
        $this->get_Order_From_BOL_V3_by_Id('demo',  "7616222700");
    }

    // public function getProcStatus_EntId_EventType_PRODSERVER(){

    //     //  $this->getProcessStatusBy_EntityId_and_EventType_PROD( '118419857', 'CREATE_OFFER_EXPORT');
    // }

    public function getProcessStatusById($id){

        $this->geefProcesStatusById('demo', $id);
    }

    public function getProcStatus_ByProcessStatusId_PRODSERVER($procstatusid){

        $this->geefProcesStatusById('prod', $procstatusid);
    }


    public function get_CSV_Offer_Export_PROD(string $offerexportid){

        $this->getCSVOfferExportPROD('prod', $offerexportid);
    }

    public function dump_and_put_ProdCSVFile_in_BOL_produktie_offers_table(){

        $csv_file_as_array = $this->zet_CSV_export_file_om_in_array('bol-get-csv-export-response-prod.csv');

        if($csv_file_as_array == 'Ontbrekende offer-export CSV-file!'){
            dump($csv_file_as_array);

            return;
        }

        if($csv_file_as_array != 'Ontbrekende offer-export CSV-file!'){

        dump($csv_file_as_array);

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

        foreach($csv_file_as_array as $key ){
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
    }
}

    public function testJobqeueRedisThrottling(){

        for($i = 0; $i < 8; $i++){

            $mytext = 'vanuit testJobqeueRedisThrottling, message: ' . $i;
            \App\Jobs\TestRedisThrottlingJob::dispatch($mytext);
        }
        return 'De view vanuit de TestController@testJobqeueRedisThrottling';
    }

    public function test() {

        // $authKey = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImQ3MGNmNmQwNjRiZjRhNTRiMDZlNTJhOTA2NzU5ZjI0Y2YzYTQ3NmQxZTUxODk2OGVkNzg0ZmMzYzc2NDg4OGIzNmZiYjE1ZWVlNDEwOThjIn0.eyJhdWQiOiIxIiwianRpIjoiZDcwY2Y2ZDA2NGJmNGE1NGIwNmU1MmE5MDY3NTlmMjRjZjNhNDc2ZDFlNTE4OTY4ZWQ3ODRmYzNjNzY0ODg4YjM2ZmJiMTVlZWU0MTA5OGMiLCJpYXQiOjE1MzEzMDkwMzYsIm5iZiI6MTUzMTMwOTAzNiwiZXhwIjoxNTYyODQ1MDM2LCJzdWIiOiI3MjMiLCJzY29wZXMiOltdfQ.UQSrhQQ0nurikXNElN49kytFUQJ2cqYOVGGb2sS2APj50zxxhPS3vO6FbRm4bcInjSMxh0Rxu3gBffettdF6uvn9N6IkJdxEAal8LjEbxq3KdO9SfDx1cJilF4pLg1yE_WaMHA-VXYsqpX_OseZiNw4dLzkktJLvaHyY-g3yxS-_qApQFigwm5xHGdbLKSw0AXHplkVQk5VZ7d8w8dUcqnVXyDtQSuXmMACsVVDUPUEwoqFxrUdcy3S4ssFtVUqUJ0hHPlJmEYeTx7NcCEYUqIoPwtOkSiwV15Jv1f5d85yE8bCYXGDnTFo0PMh40QQq8yjeZaNI8Xhc9D5i85yBRKe5wMNC5_NVKv7gN8IBm68Eabf5s3bPl3hUNL_2rZk186GQAiWQyicNTUAu0Glf-n-CAkVDQdrtH-5tVtmGK-kdfcDuyEPKn6lyROpubVL7ljJtUDMELnhLFC9FQQxujK6I24pN_5SsDIYtvE7uWl_ZNHoxZm6sQIDjGE87Ec_XtHK8s9ecXbqWPy-e6tlrUaAKFX8loeiwfKPP08mETr1ZbGm0vLjnB5qbsVZUSjT32mqPWNLpiBVDmP65wnEKbMgpaJVLISD9N6U6ApuDELnKSubuIj2gUMyPvBvxrCptUwsP_2YzFNfHOo_MvqEskw10THRYgEq8EarBJCzM';
        // $smakeUrl = 'https://api.smake.io/v2/apps/';
        // $smakeAppId = '';

        $url = 'https://api.myjson.com/bins/1fmmo8';
        $client = new Client([
        'verify' => 'C:\php\certificates\cacert.pem',
        'Content-type'=> 'application/json',
        'Accept' => 'application/json',
        // 'Authorization' => 'Bearer ' + $authKey + '>'
        ]);

        try {
            // $request = $client->get($smakeUrl + $smakeAppId);
            $request = $client->get($url);

        } catch (\Exception $e) {
            echo Psr7\str($e->getRequest());
            echo Psr7\str($e->getResponse());
        }

        $products = json_decode($request->getBody())->data;
        return view('test',compact('products'));
    }
}
