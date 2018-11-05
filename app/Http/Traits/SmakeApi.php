<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use App\Variant;

trait SmakeApi {

    public function GetProducts($method = 'GET', $url = 'products') {

        $headers = [
            'Authorization' => 'Bearer ' . env('SMAKE_KEY',''),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Accept-Language' => 'nl'
        ];

        try {
            $client = new Client([
                'base_uri' => env('SMAKE_URI', '')
            ]);

            $products = $client->request($method, $url, [
                'headers' => $headers
            ]);
        } catch (\Exception $e) {
            return $e->getResponse();
        }
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

    public function UploadMedia($designPath, $fileName, $cLength, $destinationUrl = 'media') {
        $body = fopen($designPath . $fileName, 'r');

        $headers = [
            'Authorization' => 'Bearer ' . env('SMAKE_KEY',''),
            'Content-Type' => 'application/png',
            'Content-Length' => $cLength,
            'Accept-Language' => 'nl'
        ];

        try {
            $client = new Client(['base_uri' => env('SMAKE_URI', '')]);
            $response = $client->request('POST', $destinationUrl, [
                'headers' => $headers,
                'body' => $body
            ]);
        } catch (\Exception $e) {
            return $e->getResponse();
        }
        return $response;



        // ***** Actual response *****
        // {#385 ▼
        //     +"id": 4183885
        //     +"is_test": 0
        //     +"file_name": "104bf6b113b3414296bf916620a0ebbe.png"
        //     +"size": 26722
        //     +"mime_type": "image/png"
        //     +"download_url": "https://api.smake.io/v2/apps/10036/media/4183885/download"
        //     +"created_at": "2018-11-03T11:37:23+00:00"
        //     +"updated_at": "2018-11-03T11:37:23+00:00"
        //  }

        // example response when uploading media designs
        // {
        //     "id": 1,
        //     "is_test": true,
        //     "file_name": "39b8196929f742e7bccab01a643b6524.jpeg",
        //     "size": 42840,
        //     "mime_type": "image/jpeg",
        //     "download_url": "https://api.smake.io/v2/media/1/download",
        //     "created_at": "2017-09-28T08:40:44+00:00",
        //     "updated_at": "2017-09-28T08:40:44+00:00"
        // }
    }

    // public function UploadCustomVariant($customVariant) {

    //     $headers = [
    //         'Authorization' => 'Bearer ' . env('SMAKE_KEY',''),
    //         'Content-Type' => 'application/json',
    //         'Content-Length' => $cLength,
    //         'Accept-Language' => 'nl'
    //     ];

    //     $destinationUrl = 'variants/(parentVariantId)/design';

    //     $client = new Client(['base_uri' => env('SMAKE_URI', '')]);
    //     $response = $client->request('POST', $destinationUrl, [
    //             $customVariant
    //     ]

    //     ])->getBody());
    //     return $response;
    // }
}
