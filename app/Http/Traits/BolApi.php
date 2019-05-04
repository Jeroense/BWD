<?php

namespace App\Http\Traits;

// use Illuminate\Http\Request;
use GuzzleHttp\Psr7;
use GuzzleHttp\Client;
// use GuzzleHttp\Exception\GuzzleException;
// use GuzzleHttp\Exception\RequestException;
// use GuzzleHttp\Exception\ClientException;
// use GuzzleHttp\Exception\ServerException;


trait BolAPi {


public function generateBolHeaders($privkey, $pubkey, $http_meth, $http_cont, $rest_uri){

    $http_method = strtoupper($http_meth); $http_content_type = $http_cont;
    $rest_endpoint_length = strpos($rest_uri, '?') ? strpos($rest_uri, '?') : strlen($rest_uri); // om ev querystring-deel eruit te knippen
    $rest_endpoint_uri = substr($rest_uri, 0, $rest_endpoint_length);

    $bol_date = gmdate('D, d M Y H:i:s T');  // gewenst format is bv: 'Wed, 17 Feb 2016 00:00:00 GMT'

    $bol_signature_string  = $http_method . "\n\n"  . $http_content_type . "\n" . $bol_date . "\n" . "x-bol-date:"
     . $bol_date . "\n" . $rest_endpoint_uri;

    $my_hmac_hash_sha256 = hash_hmac('sha256', $bol_signature_string, $privkey, true); // raw output true
    $my_base64_encoded = base64_encode($my_hmac_hash_sha256);
    $bol_auth = $pubkey . ":" . $my_base64_encoded;

    $myreturndata = ['bolsignaturestring' => $bol_signature_string,
                     'boldate' => $bol_date,
                     'doublehashedsigstring' => $my_base64_encoded,
                     'sha256encoded' => $my_hmac_hash_sha256,
                     'bolauthheader' => $bol_auth];

    return $myreturndata;
}

// BOL_URI=https://plazaapi.bol.com

public function makePlazaApiRequest($rest_endpoint ,$method = "get", $accept = "application/xml", $content_type = "application/xml",
                                    $requestBody = null){

    $my_bol_headers = $this->generateBolHeaders(env('BOL_PR_KEY_TEST_ACC'), env('BOL_PUB_KEY_TEST_ACC'), $method, $content_type , $rest_endpoint);

    $headers = [
        'Content-Type' => $content_type,
        'Accept' => $accept,
        'X-BOL-Date' => $my_bol_headers['boldate'],
        'X-BOL-Authorization' => $my_bol_headers['bolauthheader']
    ];


        $client = new Client(['base_uri' => env('BOL_URI', '')]);

        if(strtoupper($method) == "GET"){
            $bolresponse = $client->request($method, $rest_endpoint, ['headers' => $headers, 'http_errors' => false]);   // 'http errors'=> false, zou geen exceptions mogen geven zodat ik gewoon de codes en de resp body als xml terugkrijg..

            $bol_resp_body = $bolresponse->getBody();
            $bol_resp_headers = $bolresponse->getHeaders();
            $bol_response_code = $bolresponse->getStatusCode();
            $bol_reason_phrase = $bolresponse->getReasonPhrase();

            $bolresponsedata = ['bolheaders' => $bol_resp_headers,
                                'bolbody' => $bol_resp_body,
                                'bolstatuscode' => $bol_response_code,
                                'bolreasonphrase' => $bol_reason_phrase,
                                'requestheaders' => $headers];

            return $bolresponsedata;
        }
        if(strtoupper($method) == "DELETE" || strtoupper($method) == "PUT"){

            // try {
            $bolresponse = $client->request($method, $rest_endpoint, ['http_errors' => false ,'headers' => $headers, 'body' => $requestBody]); // 'http errors' => false, zou geen exceptions mogen geven zodat ik gewoon de codes en de resp body als xml terugkrijg..

            $bol_resp_body = $bolresponse->getBody();
            $bol_resp_headers = $bolresponse->getHeaders();
            $bol_response_code = $bolresponse->getStatusCode();
            $bol_reason_phrase = $bolresponse->getReasonPhrase();

            $bolresponsedata = ['bolheaders' => $bol_resp_headers,
                                'bolbody' => $bol_resp_body,
                                'bolstatuscode' => $bol_response_code,
                                'bolreasonphrase' => $bol_reason_phrase,
                                'requestheaders' => $headers,
                                'requestbody' => $requestBody];

            return $bolresponsedata;
            }

// !!! Worth mentioning that the 'http_errors' => false option can be passed in the Guzzle request which disables throwing exceptions.
//  You can then get the body with $response->getBody() no matter what the status code is, and you can test the status code if
// necessary with $response->getStatusCode() !!!

            // onderstaande is alleen maar lastig met deze shit exceptions!  Beste is om in de request aan bol
            // ($bolresponse = $client->request('http_errors' => false))  --> 'https_errors' => false
            // mee te geven, dan heb je al die shit exceptions niet, en kun je gewoon de responsebody met ->getBody() als xml string krijgen en
            // deze opslaan in een simplexmlobject en onderzoeken op errors!! isset($myxmlobj->ValidationErrors->ValidationError->ErrorCode){}  etc

            // bij een 400 bad (client )request krijg je als xml-reply:

$validation_error_response= <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Errors xmlns="https://plazaapi.bol.com/offers/xsd/api-2.0.xsd">
<ValidationErrors>
<ValidationError>
<ErrorCode>41102</ErrorCode>
<ErrorMessage>The EAN is not valid.</ErrorMessage>
<Field>EAN</Field>
<Value>1234567890123</Value>
</ValidationError>
<ValidationError>
<ErrorCode>41104</ErrorCode>
<ErrorMessage>The delivery code is not valid; check the documentation for valid values.</ErrorMessage>
<Field>DeliveryCode</Field>
<Value>Error-test!</Value>
</ValidationError>
</ValidationErrors>
</Errors>
XML;

            //-----------------------------------------------------------------------------------------------------
            // catch(\Exception $e){
            //     dd($e, $e->getMessage());  // dit returned de error message als een string
            // }

            // catch(ClientException $e){
            //     // dd($e);
            //     $psr7_req = Psr7\str($e->getRequest());       // dit is de PSR7 implementatie, moet volgens de interface
            //     $psr7_resp = Psr7\str($e->getResponse());  // dit is de PSR7 implementatie, moet volgens de interface
            //     $error_resp_body = $e->getResponse()->getBody();
            //     $code = ($e->getResponse()->getStatusCode());
            //     $reason = $e->getResponse()->getReasonPhrase();
            //     $other_psr7_resp = $e->getResponse(); // instantie van: GuzzleHttp\Psr7\Response

            //     dump($error_resp_body ,$e, $psr7_resp, $code, $reason, $other_psr7_resp);

            //     return ['faulty_client_request' => $psr7_req, 'server_error_response' => $psr7_resp, 'code' => $code, 'reason' => $reason];
            // }
            // catch(ServerException $e){
            //     $req = Psr7\str($e->getRequest());
            //     $resp = Psr7\str($e->getResponse());
            //     $code = ($e->getResponse()->getStatusCode());
            //     $reason = $e->getResponse()->getReasonPhrase();

            //     return ['good_client_request' => $req, 'internal_server_error_response' => $resp, 'code' => $code, 'reason' => $reason];
            // }
            // catch(RequestException $e){
            //     $req = Psr7\str($e->getRequest());
            //     $resp = Psr7\str($e->getResponse());
            //     return ['network_error_request' => $req, 'network_error_response' => $resp];
            // }
        // }
        //------------------------------------------------------------------------------------------------------
}


}
    //  werkt niet met PHP_EOL!
    // $bol_signature_string  = $http_method . PHP_EOL . PHP_EOL . $http_content_type . PHP_EOL . $bol_date . PHP_EOL . "x-bol-date:"
    //  . $bol_date . PHP_EOL . $rest_endpoint_uri;
