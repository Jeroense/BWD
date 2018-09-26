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

        $client = new Client([
            'base_uri' => env('SMAKE_URI', '')
        ]);
        $products = json_decode($client->request($method, $url, [
            'headers' => $headers
        ])->getBody())->data;
        return $products;
    }

    public function GetMedia($imageId) {

        $resource = fopen('productImages/' . 'logo'. '.png', 'w');

        $headers = [
            'Accept' => 'image/png',
        ];
        $options = [
            'sink' => $resource,
            'http_errors' => false
        ];

        $client = new Client([
            'base_uri' => env('MEDIA_URI', '')
        ]);

        $media = $client->get($imageId, $options);
        fclose($resource);
        return $media;
    }
}
