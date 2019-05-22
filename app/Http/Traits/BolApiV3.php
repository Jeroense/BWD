<?php

namespace App\Http\Traits;

use GuzzleHttp\Client;
use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;
use App\CustomVariant;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

use App\BolToken;
use PhpParser\Node\Expr\Cast\Double;

trait BolApiV3 {

    // BOL_URI_TEST_V3=https://api.bol.com/retailer-demo/
    // BOL_URI_V3=https://api.bol.com/retailer/


    private $oauth_access_token = [];

    public function request_BOL_Oauth_Token(){

        $bol_token_server = 'https://login.bol.com';

        // https://en.wikipedia.org/wiki/Basic_access_authentication
        $basic_auth_header = 'Basic ' . base64_encode( env('BART_BOL_CLIENT_ID_BWD_2') . ':' . env('BART_BOL_CLIENT_SECRET_BWD_2') );


        $headers = [
                    'Accept' => 'application/json',
                    'Authorization' => $basic_auth_header

                    ];

                    // dump($headers);

                    $client = new Client(['base_uri' => $bol_token_server, '']);
                    $bol_outh_response = $client->request('post', '/token?grant_type=client_credentials', ['headers' => $headers, 'http_errors' => false]);

                    dump('Status-code token-server: ');
                    dump($bol_outh_response->getStatusCode());
                    // dump('Response-headers van de bol oauth login token server: ');
                    // dump($bol_outh_response->getHeaders());
                    // bol oauth token server geeft geen x-ratelimit headers!

                    $resp_code = $bol_outh_response->getStatusCode();
                    $resp_phrase = $bol_outh_response->getReasonPhrase();
                    $resp_body = $bol_outh_response->getBody();

                    // public function putResponseInFile($fileName, $code, $phrase, $body)
                    $this->putResponseInFile('bol_oauth_response.txt', $resp_code, $resp_phrase, $resp_body);

                    // The entity body object of a response can be retrieved by calling $response->getBody().
                    //The response EntityBody can be cast to a string, or you can pass true to this method to retrieve the body as a string.
                    // https://guzzle3.readthedocs.io/http-client/response.html
                    // dump((string)$resp_body);
                    // You can easily parse and use a JSON response as an array using the json() method of a response. // WERKT NIET!!
                    // This method will always return an array if the response is valid JSON or if the response body is empty.
                    // You will get an exception if you call this method and the response is not valid JSON.

                    // $js = $bol_outh_response->json();
                    // dump($js);

                    // dump( $bol_outh_response->getHeaders() );


                    if($resp_code !== 200){
                        return 'error. status code not 200';
                    }
                    if($resp_body == null){
                        return 'error. empty response body';
                    }



                    if($resp_body != null && strpos($bol_outh_response->getHeaders()['Content-Type'][0], 'json') !== false ){

                            $this->oauth_access_token = json_decode($resp_body, true);
                            BolToken::truncate();
                            BolToken::create(['access_token' => $this->oauth_access_token['access_token'],
                                              'at_unix_time' => time(),
                                              'seconds_valid' => $this->oauth_access_token['expires_in']]);
                            // dump($this->oauth_access_token);

                            return $this->oauth_access_token['access_token'];

                    }

                    return ['code' => $resp_code, 'phrase' => $resp_phrase, 'body' => $resp_body];
    }


    public function giveBolV3Headers($requestType, bool $getPreparedOfferExportinCSV = false){

        if(BolToken::all()->count() > 0){
            $last_db_token = BolToken::latest()->first();

            $db_bol_access_token_geldig_tot = $last_db_token->at_unix_time + ($last_db_token->seconds_valid - 10);

            if(time() - $db_bol_access_token_geldig_tot < 0){  // DB token nog geldig

                $token = $last_db_token->access_token;
                $basic_auth_header = 'Bearer ' . $token;
                dump('DB token nog geldig. Gebruikt hier access-token uit de DB');
                // dump($basic_auth_header);
            }
            else{
                $basic_auth_header = 'Bearer ' . $this->request_BOL_Oauth_Token();
                dump('Oude DB token is expired. Nieuw token opgevraagd bij bol!');
            }
        }
        else{
            $basic_auth_header = 'Bearer ' . $this->request_BOL_Oauth_Token();
            dump('BolToken table is nog leeg. Nieuw token opgevraagd bij bol!');
        }

        // $basic_auth_header = 'Bearer ' . $this->request_BOL_Oauth_Token();


        $headers = [
                    'Accept'  => 'application/vnd.retailer.v3+json',
                    'Authorization' => $basic_auth_header,
                 ];

        if($getPreparedOfferExportinCSV){

            $headers = [
                'Accept'  => 'application/vnd.retailer.v3+csv',
                'Authorization' => $basic_auth_header,
                'Content-Type' => 'application/x-www-form-urlencoded'
             ];
             return $headers;
        }

        if(strtoupper($requestType) !== 'GET'){

            $headers['Content-Type'] = 'application/vnd.retailer.v3+json';
            }

        return $headers;
    }





    public function prepare_CSV_Offers_export($serverType){

        $csv_endpoint = 'offers/export';
        $post_body = new \stdClass();
        $post_body->format = 'CSV';
        $post_body_json = json_encode($post_body);

        $csv_response_array = $this->make_V3_PlazaApiRequest($serverType, $csv_endpoint, 'post', $post_body_json, false);

        dump($csv_response_array);
        $this->putResponseInFile("bol-generate-csv-export-response-{$serverType}.txt", $csv_response_array['bolstatuscode'], $csv_response_array['bolreasonphrase'],
                                                                $csv_response_array['bolbody'],  $csv_response_array['x_ratelimit_limit'], $csv_response_array['x_ratelimit_reset'], $csv_response_array['x_ratelimit_remaining'], (string)time());


        return $csv_response_array;
    }



    public function getCSVOfferExportPROD($serverType ,$offerexportid){

        $endpoint = "offers/export/{$offerexportid}";
        $csv_file_name = "bol-get-csv-export-response-{$serverType}.csv";

        $bol_response = $this->make_V3_PlazaApiRequest('prod', $endpoint, 'get', null, true);

        // dump( $bol_response );

        if($bol_response['bolstatuscode'] != 200){
            return 'status niet 200';
        }

        $this->putResponseInFile("bol-get-csv-export-response-appended-{$serverType}.txt", $bol_response['bolstatuscode'], $bol_response['bolreasonphrase'],
        $bol_response['bolbody'], $bol_response['x_ratelimit_limit'], $bol_response['x_ratelimit_reset'], $bol_response['x_ratelimit_remaining'], (string)time());

        $this->putCSVResponseInCleanFile($csv_file_name,  $bol_response['bolbody']);  // dit maakt/update de csv-file, die gebruikt wordt om per offerId verder requests te doen

        return $csv_file_name;
    }


    public function zet_CSV_export_file_om_in_array( $csvFileName){

        // Handy one liner to parse a CSV file into an array: de waarden van elke row uit de csv-(file!) wordt een array.
        // $my_csv_array = array_map('str_getcsv', file(storage_path('app/public') . '\AllOffersOnBol'  . '.csv'));
        // Met:  array_walk($my_csv_array, function(&$a) use ($my_csv_array) {$a = array_combine($my_csv_array[0], $a);
        // krijg je een ass. array van de waarden uit de csv file (per row), met de (header)waarden uit de 1e row van de csv file als key.
        // Dit doet hij ook voor de 1e kolom (['EAN' => 'EAN', 'Condition' => 'Condition']).  Dit array is meestal overbodig
        // Dus 1e array-element in hoofd-array weghalen met array_shift()


        // controleren of de csv-file bestaat, en of er minimaal 2 regels niet NULL zijn in de file. r1 is de header,
        // vanaf r2 is er data, data vanaf r2, bevat de string 'UTC'
        if( (!file_exists( storage_path("app/public") . '/' . $csvFileName)) || ( file_exists( storage_path("app/public") . '/' . $csvFileName ) &&
            strpos( file(storage_path("app/public") . '/' . $csvFileName)[1], 'UTC') === false)  ){

            dump('no csv file, or no data in csv file!');

            file_put_contents( storage_path( 'app/public') . '/' . 'csvexport-to-array-error.txt', ((string)date('D, d M Y H:i:s') . "\r\n" . 'error' . "\r\n\r\n"), FILE_APPEND );

            return 'no csv file, or no data in csv file!';
        }
        //

        // $csv_file_path = storage_path("app/public") . '/' . $csvFileName;
        // $csv_file_as_array = file($csv_file_path);  // een array met key = line-number   & val = line-content
        // dump($csv_file_as_array);



            $my_csv_array = array_map('str_getcsv', file(storage_path("app/public") . '/' . $csvFileName ));


            array_walk($my_csv_array, function(&$a) use ($my_csv_array) {
            $a = array_combine($my_csv_array[0], $a);
            });
            array_shift($my_csv_array);  // nu zijn de booleans wel omgezet in string! "TRUE" of "FALSE" ! rekening mee houden bij opslaan in model
            //---------------------------

            return $my_csv_array;

    }






    public function geefProcesStatusById($serverType, $id){

        $endpoint = "process-status/{$id}";
        $bol_response_array = $this->make_V3_PlazaApiRequest($serverType, $endpoint, 'get');
        dump($bol_response_array);
        // dump($bol_response_array['x_ratelimit_limit']);
        // dump($bol_response_array['x_ratelimit_remaining']);
        // dump($bol_response_array['x_ratelimit_reset']);
        if($bol_response_array['bolstatuscode'] !== 200){

            $this->putResponseInFile("bol-proces-status-response-{$serverType}.txt", $bol_response_array['bolstatuscode'], $bol_response_array['bolreasonphrase'],
            $bol_response_array['bolbody'], $bol_response_array['x_ratelimit_limit'], $bol_response_array['x_ratelimit_reset'], $bol_response_array['x_ratelimit_remaining'], (string)time());


            return 'process status by id response code niet 200!';
        }

        $this->putResponseInFile("bol-proces-status-response-{$serverType}.txt", $bol_response_array['bolstatuscode'], $bol_response_array['bolreasonphrase'],
        $bol_response_array['bolbody'], $bol_response_array['x_ratelimit_limit'], $bol_response_array['x_ratelimit_reset'], $bol_response_array['x_ratelimit_remaining'], (string)time());

        return $bol_response_array;

    }

    // public function getProcStatusByProcessStatusID_PRODSERVER(int $procstatusid){

    //     $endpoint = "process-status/{$procstatusid}";

    //     $bol_response_array = $this->make_V3_PlazaApiRequest('prod' ,$endpoint, 'get');
    //     dump($bol_response_array);

    //     $this->putResponseInFile('bol-proces-status-response-PROD-server.txt', $bol_response_array['bolstatuscode'], $bol_response_array['bolreasonphrase'],
    //     $bol_response_array['bolbody']);

    //     return;
    // }

    public function upload_Single_Offer_To_BOL_V3_DEMO($customVariantId){

        try {
        $custVar = CustomVariant::findOrFail($customVariantId);
        }
        catch(ModelNotFoundException $ex){

            $error_mssg = "Custom variant met ID: {$customVariantId} niet in DB gevonden." .  "\r\n" . $ex->getMessage();

            return $error_mssg;
        }

        $bolOfferBody = $this->maak_JSON_voor_single_offer_BOL($custVar, false);

        $bolOfferResponse =  $this->make_V3_PlazaApiRequest('demo', 'offers', 'post', $bolOfferBody);
        dump($bolOfferResponse);
        $this->putResponseInFile("bolPostOfferResponse-demo.txt", $bolOfferResponse['bolstatuscode'],
                                    $bolOfferResponse['bolreasonphrase'],$bolOfferResponse['bolbody']);

            return;

    }


    public function get_Bol_Offer_by_Id_PROD($serverType ,$id){

        $endpoint = "offers/{$id}";

        $bol_response_array = $this->make_V3_PlazaApiRequest('prod', $endpoint, 'get');
        dump($bol_response_array);
        // echo('unix-epoch timeis: ' . time() );
        // echo( "\r\n");
        // echo('x_ratelimit_reset: ' . $bol_response_array['x_ratelimit_reset']);

        $this->putResponseInFile("bol-offer-response-by-id-{$serverType}.txt", $bol_response_array['bolstatuscode'], $bol_response_array['bolreasonphrase'],
        $bol_response_array['bolbody'], $bol_response_array['x_ratelimit_limit'], $bol_response_array['x_ratelimit_reset'], $bol_response_array['x_ratelimit_remaining'], (string)time());

        return $bol_response_array;
    }


    public function upload_Single_Offer_To_BOL_V3_PROD($customVariantId){

        try {
        $custVar = CustomVariant::findOrFail($customVariantId);
        }
        catch(ModelNotFoundException $ex){

            $error_mssg = "Custom variant met ID: {$customVariantId} niet in DB gevonden." .  "\r\n" . $ex->getMessage();

            return $error_mssg;
        }

        $bolOfferBody = $this->maak_JSON_voor_single_offer_BOL($custVar, false);

        $bolOfferResponse =  $this->make_V3_PlazaApiRequest('prod','offers', 'post', $bolOfferBody);
        dump($bolOfferResponse);
        $this->putResponseInFile('bolPostOfferResponse-PROD.txt', $bolOfferResponse['bolstatuscode'],
                                    $bolOfferResponse['bolreasonphrase'],$bolOfferResponse['bolbody']);

            return;

    }







    public function get_Orders_from_BOL_V3(string $server_type){

        $bolOrderResponse =  $this->make_V3_PlazaApiRequest($server_type,  'orders?fulfilment-method=FBR', 'get');
        echo('UNIX/epoch time is: ' . time());
        echo(' x-rate-limit reset at: ');
        echo($bolOrderResponse['bolheaders']['X-RateLimit-Reset'][0]);
        dump($bolOrderResponse);
        $reply_obj = json_decode($bolOrderResponse['bolbody']);
        dump($reply_obj); isset($reply_obj->orders) ? dump('er zijn orders') : dump('geen orders');
        $this->putResponseInFile('bolGetOrdersResponse-' . $server_type .'.txt', $bolOrderResponse['bolstatuscode'],
                                                            $bolOrderResponse['bolreasonphrase'],
                                                            $bolOrderResponse['bolbody']);

    }

    public function get_Order_From_BOL_V3_by_Id(string $server_type, string $orderId){

        $bolOrderResponse =  $this->make_V3_PlazaApiRequest($server_type, "orders/{$orderId}", 'get');
        dump( $bolOrderResponse);
        $this->putResponseInFile('bolGetOrderResponseByID-' . $server_type . '.txt', $bolOrderResponse['bolstatuscode'], $bolOrderResponse['bolreasonphrase'],$bolOrderResponse['bolbody']);

    }











        public function make_V3_PlazaApiRequest($server_type, $rest_endpoint ,$method = "get", $requestBody = null, bool $isGETForCSVExport = false){


            // dump($rest_endpoint, $server_type);


                $headers = $this->giveBolV3Headers($method, $isGETForCSVExport);
                $server_root_url = '';

                switch($server_type){
                    case 'prod':
                    $server_root_url = env('BOL_URI_V3', ''); break;
                    case 'demo':
                    $server_root_url = env('BOL_URI_TEST_V3', ''); break;
                    default: return 'Geef aan welke server, produktie of demo!';
                }


                $client = new Client(['base_uri' => $server_root_url]);


                if(strtoupper($method) == "GET"){
                    // sync
                    $bolresponse = $client->request($method, $rest_endpoint,  ['headers' => $headers, 'http_errors' => false]);
                    // sync




                    return              ['bolheaders' => $bolresponse->getHeaders(),
                                        'bolbody' => (string)$bolresponse->getBody(),
                                        'bolstatuscode' => $bolresponse->getStatusCode(),
                                        'bolreasonphrase' => $bolresponse->getReasonPhrase(),
                                        'requestheaders' => $headers,
                                        'x_ratelimit_limit' => $bolresponse->getHeader('X-RateLimit-Limit')[0],
                                        'x_ratelimit_remaining' => $bolresponse->getHeader('X-RateLimit-Remaining')[0],
                                        'x_ratelimit_reset' => $bolresponse->getHeader('X-RateLimit-Reset')[0]
                                        ];


                }
                if(strtoupper($method) == "DELETE" || strtoupper($method) == "PUT" || strtoupper($method) == "POST"){
                    // kan natuurlijk ook met: else{} jaja

                    $bolresponse = $client->request($method, $rest_endpoint, ['http_errors' => false ,'headers' => $headers, 'body' => $requestBody]);

                    $bol_resp_body = (string)$bolresponse->getBody();
                    $bol_resp_headers = $bolresponse->getHeaders();
                    $bol_response_code = $bolresponse->getStatusCode();
                    $bol_reason_phrase = $bolresponse->getReasonPhrase();

                    return              ['bolheaders' => $bol_resp_headers,
                                        'bolbody' => $bol_resp_body,
                                        'bolstatuscode' => $bol_response_code,
                                        'bolreasonphrase' => $bol_reason_phrase,
                                        'requestheaders' => $headers,
                                        'requestbody' => $requestBody,
                                        'x_ratelimit_limit' => $bolresponse->getHeader('X-RateLimit-Limit')[0],
                                        'x_ratelimit_remaining' => $bolresponse->getHeader('X-RateLimit-Remaining')[0],
                                        'x_ratelimit_reset' => $bolresponse->getHeader('X-RateLimit-Reset')[0]
                                        ];


            }

        }

        public function make_V3_PlazaApiRequest_FOR_CSV_EXPORT_PROD_SERVER($rest_endpoint ,$method = "get"){


            dump($rest_endpoint);

                $headers = $this->giveBolV3Headers_For_CSV_Offer_Export();


                $client = new Client(['base_uri' => env('BOL_URI_V3', '')]);


                if(strtoupper($method) == "GET"){
                    $bolresponse = $client->request($method, $rest_endpoint,  ['headers' => $headers, 'http_errors' => false]);   // 'http errors'=> false, zou geen exceptions mogen geven zodat ik gewoon de codes en de resp body als xml terugkrijg..

                    $bol_resp_body = (string)$bolresponse->getBody();
                    $bol_resp_headers = $bolresponse->getHeaders();
                    $bol_response_code = $bolresponse->getStatusCode();
                    $bol_reason_phrase = $bolresponse->getReasonPhrase();

                    return              ['bolheaders' => $bol_resp_headers,
                                        'bolbody' => $bol_resp_body,
                                        'bolstatuscode' => $bol_response_code,
                                        'bolreasonphrase' => $bol_reason_phrase,
                                        'requestheaders' => $headers];


                }


        }



        public function putResponseInFile($fileName, $code, $phrase, $body, $x_ratelimit_limit = null, $x_ratelimit_reset = null, $x_ratelimit_remaining = null, $unixtime = null)
        {

            if($unixtime == null || $x_ratelimit_limit == null || $x_ratelimit_reset == null || $x_ratelimit_remaining == null){
                dump('putResponseInFIle: ontbreekt unixtime of ontbreekt x_ratelimit header.');
                file_put_contents( storage_path( 'app/public') . '/' . $fileName, ((string)date('D, d M Y H:i:s') . "\r\n" . $code . " " . $phrase  .
                "\r\n\r\n" . (string)$body) . "\r\n\r\n", FILE_APPEND );
                }

            if($unixtime != null && $x_ratelimit_limit != null && $x_ratelimit_reset != null && $x_ratelimit_remaining != null){
                dump('Alle x_ratelimit headers in response aanwezig');
            file_put_contents( storage_path( 'app/public') . '/' . $fileName, ((string)date('D, d M Y H:i:s') . "\r\n" . $unixtime . "\r\n" . $x_ratelimit_limit . "\r\n" . $x_ratelimit_remaining . "\r\n" . $x_ratelimit_reset . "\r\n" . "\r\n" . $code . " " . $phrase  .
            "\r\n\r\n" . (string)$body) . "\r\n\r\n", FILE_APPEND );
            }

            return;
        }

        // putCSVResponseInCleanFile("bol-get-csv-export-response-{$serverType}.txt",  $bol_response['bolbody']);
        public function putCSVResponseInCleanFile($fileName, $csvData){

            file_put_contents( storage_path( 'app/public') . '/' . $fileName, $csvData );
        }

        public function put_JSON_in_File($fileName, $jsondata)
        {

            file_put_contents( storage_path( 'app/public') . '/' . $fileName, (string)date('D, d M Y H:i:s') . "\r\n\r\n" . $jsondata .
            "\r\n\r\n", FILE_APPEND );


            return;
        }

        public function maak_JSON_voor_single_offer_BOL(CustomVariant $custvar, $publish = true, $destock = 100){

            $refcode = "tshirt-{$custvar->variantName}-{$custvar->baseColor}-{$custvar->size}";
            $onHoldByRetailer = !$publish;


            $stock = $destock;

            $fulFillment = 'FBR';


            $bolConditionObject = new \stdClass();
            $bolConditionObject->name = 'NEW';       //  Enum:"NEW" "AS_NEW" "GOOD" "REASONABLE" "MODERATE"
            $bolConditionObject->category = 'NEW';   //  Enum:"NEW" "SECONDHAND"

            $bolQtyPrice = new \stdClass();
            $bolQtyPrice->quantity = 1;
            $bolQtyPrice->price = $custvar->salePrice;

            $bolPricingBundle = new \stdClass();
            $bolPricingBundle->bundlePrices = [$bolQtyPrice];

            $bolStockObject = new \stdClass();
            $bolStockObject->amount = $stock;
            $bolStockObject->managedByRetailer = true;

            $bolFulFillmentObject = new \stdClass();
            $bolFulFillmentObject->type = $fulFillment;
            $bolFulFillmentObject->deliveryCode = $custvar->boldeliverycode;

            $bolOfferObject = new \stdClass();
            $bolOfferObject->ean = $custvar->ean;
            $bolOfferObject->condition = $bolConditionObject;
            $bolOfferObject->referenceCode = $refcode;
            $bolOfferObject->onHoldByRetailer = $onHoldByRetailer;
            $bolOfferObject->unknownProductTitle =  $custvar->variantName;
            $bolOfferObject->pricing = $bolPricingBundle;
            $bolOfferObject->stock = $bolStockObject;
            $bolOfferObject->fulfilment = $bolFulFillmentObject;

            // $singleBolOffer_JSON_body = json_encode($bolOfferObject, JSON_PRETTY_PRINT);
            $singleBolOffer_JSON_body = json_encode($bolOfferObject);

            // dump($singleBolOffer_JSON_body);
            $this->put_JSON_in_File('singleBolOffer-in-JSON.json', $singleBolOffer_JSON_body);

            return $singleBolOffer_JSON_body;
        }



        public function maak_ObjectenArray_voor_single_offers_BOL_from_Array(array $offersData){
        //     [
        //     2 => array:9 [▼
        //     "ean" => "8712626055143"
        //     "siz" => "XL"
        //     "sto" => "6"
        //     "onh" => "on"
        //     "pub" => "on"
        //     "var" => "bol demo dummy produktnaam 2"
        //     "bas" => "Stone Blue"
        //     "sal" => "40"
        //     "del" => "3-5d"
        //   ]
        //   3 => array:7 [▼
        //     "ean" => "8804269223123"
        //     "siz" => "XL"
        //     "sto" => "2"
        //     "var" => "bol demo dummy produktnaam 3"
        //     "bas" => "Stone Blue"
        //     "sal" => "40"
        //     "del" => "3-5d"
        //   ]
        //  ]
            $array_met_offer_objecten = [];

            foreach($offersData as $offerData){

                if(key_exists('pub', $offerData)){

                    $short_shirt_name = substr($offerData['var'], 0 , 4);

                    $refcode = "{$short_shirt_name}-{$offerData['bas']}-{$offerData['siz']}"; // ref-code length:  max 20 chars

                    $onHoldByRetailer = key_exists('onh', $offerData) ? true : false;


                    $stock = isset($offerData['sto']) ? (int)$offerData['sto'] : 0;

                    $fulFillment = 'FBR';


                    $bolConditionObject = new \stdClass();
                    $bolConditionObject->name = 'NEW';       //  Enum:"NEW" "AS_NEW" "GOOD" "REASONABLE" "MODERATE"
                    $bolConditionObject->category = 'NEW';   //  Enum:"NEW" "SECONDHAND"

                    $bolQtyPrice = new \stdClass();
                    $bolQtyPrice->quantity = 1;
                    $bolQtyPrice->price = (Double)$offerData['sal'];

                    $bolPricingBundle = new \stdClass();
                    $bolPricingBundle->bundlePrices = [$bolQtyPrice];

                    $bolStockObject = new \stdClass();
                    $bolStockObject->amount = $stock;
                    $bolStockObject->managedByRetailer = true;

                    $bolFulFillmentObject = new \stdClass();
                    $bolFulFillmentObject->type = $fulFillment;
                    $bolFulFillmentObject->deliveryCode = $offerData['del'];

                    $bolOfferObject = new \stdClass();
                    $bolOfferObject->ean = $offerData['ean'];
                    $bolOfferObject->condition = $bolConditionObject;
                    $bolOfferObject->referenceCode = $refcode;
                    $bolOfferObject->onHoldByRetailer = $onHoldByRetailer;
                    $bolOfferObject->unknownProductTitle =  $offerData['var'];
                    $bolOfferObject->pricing = $bolPricingBundle;
                    $bolOfferObject->stock = $bolStockObject;
                    $bolOfferObject->fulfilment = $bolFulFillmentObject;


                    array_push( $array_met_offer_objecten, $bolOfferObject);
                }
            }

            // $this->put_JSON_in_File('array_van_BolOffers-in-JSON.json', $offer_json_array);

            return $array_met_offer_objecten;
        }


//     Request body voorbeeld van bol ReDoc

// {
//   "ean" : "0045496420253",
//   "condition" : {
//     "name" : "MODERATE",
//     "category" : "SECONDHAND",
//     "comment" : "Description"
//   },
//   "referenceCode" : "RefCode",
//   "onHoldByRetailer" : true,
//   "unknownProductTitle" : "Title",
//   "pricing" : {
//     "bundlePrices" : [ {
//       "quantity" : 1,
//       "price" : 9.99
//     } ]
//   },
//   "stock" : {
//     "amount" : 1,
//     "managedByRetailer" : false
//   },
//   "fulfilment" : {
//     "type" : "FBR",
//     "deliveryCode" : "24uurs-21"
//   }
// }



        // onderstaande functies werken niet, geen multiple offers-upload door plaza-api geaccepteerd!
        //-------------------------------------------------------------------------------------------------------------------------------
        public function upload_Multiple_Offers_To_BOL_V3_DEMO(Collection $customVariants){
            // "violations":[{"reason":"Cannot deserialize instance of `com.bol.service.merchant.api.v3.offers.model.CreateOfferRequest` out of START_ARRAY token."}]}
                 // $dejsoncollectie = $customVariants->toJson();
                 // dump($dejsoncollectie);
                 // dd($customVariants);
                 // foreach($customVariants as $customVariant){
                 //     dump($customVariant->toJson());
                 // }
                 $jsonvanuitcollectionofferbody = $this->maak_JSON_voor_multiple_offer_BOL($customVariants, false);
                 dd($jsonvanuitcollectionofferbody);


                 // $bolOffersResponse =  $this->make_V3_PlazaApiRequest_DEMO_SERVER('offers', 'post', $jsonvanuitcollectionofferbody);

                 // $this->putResponseInFile('bolPostMultipleOfferResponse.txt', $bolOffersResponse['bolstatuscode'],
                 //                             $bolOffersResponse['bolreasonphrase'],$bolOffersResponse['bolbody']);

                     return;

             }

             public function maak_JSON_voor_multiple_offer_BOL(Collection $custvars, $publish = true){   // wordt niet geaccepteerd door bol v3

                $arr_custvar_objecten = [];
                $teller = 1;

                foreach($custvars as $custvar){

                $refcode = 'test' . $teller;
                $onHoldByRetailer = !$publish;
                $unKnownProductTitle = $custvar->variantName;

                $stock = 100;

                $fulFillment = 'FBR';


                $bolConditionObject = new \stdClass();
                $bolConditionObject->name = 'NEW';       //  Enum:"NEW" "AS_NEW" "GOOD" "REASONABLE" "MODERATE"
                $bolConditionObject->category = 'NEW';   //  Enum:"NEW" "SECONDHAND"

                $bolQtyPrice = new \stdClass();
                $bolQtyPrice->quantity = 1;
                $bolQtyPrice->price = $custvar->salePrice;

                $bolPricingBundle = new \stdClass();
                $bolPricingBundle->bundlePrices = [$bolQtyPrice];

                $bolStockObject = new \stdClass();
                $bolStockObject->amount = $stock;
                $bolStockObject->managedByRetailer = true;

                $bolFulFillmentObject = new \stdClass();
                $bolFulFillmentObject->type = $fulFillment;
                $bolFulFillmentObject->deliveryCode = $custvar->boldeliverycode;

                $bolOfferObject = new \stdClass();
                $bolOfferObject->ean = $custvar->ean;
                $bolOfferObject->condition = $bolConditionObject;
                $bolOfferObject->referenceCode = $refcode;
                $bolOfferObject->onHoldByRetailer = $onHoldByRetailer;
                $bolOfferObject->unknownProductTitle =  $custvar->variantName;
                $bolOfferObject->pricing = $bolPricingBundle;
                $bolOfferObject->stock = $bolStockObject;
                $bolOfferObject->fulfilment = $bolFulFillmentObject;

                $teller ++;
                array_push($arr_custvar_objecten, $bolOfferObject);
                }
                // $multiple_BolOffer_JSON_body = json_encode($bolOfferObject, JSON_PRETTY_PRINT);
                $multiple_BolOffer_JSON_body = json_encode($arr_custvar_objecten);


                $this->put_JSON_in_File('multiple-BolOffer-in-JSON.json', $multiple_BolOffer_JSON_body);

                return $multiple_BolOffer_JSON_body;
            }


            public function getProcessStatusBy_EntityId_and_EventType_PROD( string $entityId, string $eventType){

                // EventType: Enum:"CONFIRM_SHIPMENT" "CANCEL_ORDER" "CHANGE_TRANSPORT" "HANDLE_RETURN_ITEM" "CREATE_INBOUND" "DELETE_OFFER" "CREATE_OFFER" "UPDATE_OFFER" "UPDATE_OFFER_STOCK" "UPDATE_OFFER_PRICE"
                // Example: "PROCESS_EXAMPLE"

                        $endpoint = "process-status?entity-id={$entityId}&event-type={$eventType}";

                        $bol_response_array = $this->make_V3_PlazaApiRequest('prod', $endpoint, 'get');
                        dump($bol_response_array);

                        $this->putResponseInFile('bol-proces-status-response-PROD-server.txt', $bol_response_array['bolstatuscode'], $bol_response_array['bolreasonphrase'],
                        $bol_response_array['bolbody']);

                        return;
                    }

    // public function giveBolV3Headers_For_CSV_Offer_Export(){   // aparte headers voor ophalen offer-file in CSV format

    //     $basic_auth_header = 'Bearer ' . $this->request_BOL_Oauth_Token();

    //     $headers = [
    //                 'Accept'  => 'application/vnd.retailer.v3+csv',
    //                 'Authorization' => $basic_auth_header,
    //                 'Content-Type' => 'application/x-www-form-urlencoded'
    //              ];


    //     return $headers;
    // }

    // public function prepare_CSV_Offers_export_prod(){

    //     $csv_endpoint = 'offers/export';
    //     $post_body = new \stdClass();
    //     $post_body->format = 'CSV';
    //     $post_body_json = json_encode($post_body);

    //     $csv_response_array = $this->make_V3_PlazaApiRequest('prod' ,$csv_endpoint, 'post', $post_body_json, false);

    //     dump($csv_response_array);
    //     $this->putResponseInFile('bol-generate-prod-csv-response.txt', $csv_response_array['bolstatuscode'], $csv_response_array['bolreasonphrase'],
    //                                                             $csv_response_array['bolbody']);

    //     return $csv_response_array;
    // }
    }
