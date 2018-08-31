<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

trait SmakeApi {

    public function GetJson($method = 'GET', $url = 'products') {

        $headers = [
            'Authorization' => 'Bearer ' . env('SMAKE_KEY',''),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Accept-Language' => 'nl'
        ];

        $client = new Client(['base_uri' => env('SMAKE_URI', '')]);
        $products = json_decode($client->request($method, $url, ['headers' => $headers])->getBody())->data;
        // dd($products);
        return $products;
    }
}
