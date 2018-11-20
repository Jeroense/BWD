<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use App\Variant;

trait SmakeApi {

    public function UploadCustomVariant($body, $destinationUrl) {
        $headers = [
            'Authorization'   => 'Bearer ' . env('SMAKE_KEY',''),
            'Content-Type'    => 'application/json',
            'Accept'          => 'application/json',
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
    }

    public function Poll($url) {
        $headers = [
            'Authorization'   => 'Bearer ' . env('SMAKE_KEY',''),
            'Content-Type'    => 'application/json',
            'Accept'          => 'application/json',
            'Accept-Language' => 'nl'
        ];

        try {
            $client = new Client([]);

            $pollResult = $client->request('GET', $url, [
                'headers' => $headers
            ]);
        } catch (\Exception $e) {
            return $e->getResponse();
        }
        return $pollResult;
    }

    public function GetCustomVariant($url) {
        $headers = [
            'Authorization'   => 'Bearer ' . env('SMAKE_KEY',''),
    'Content-Type'            => 'application/json',
            'Accept'          => 'application/json',
            'Accept-Language' => 'nl'
        ];

        try {
            $client = new Client([
                'base_uri' => env('SMAKE_URI', '')
            ]);

            $response = $client->request('GET', 'designed-variants/'.$url, [
                'headers' => $headers
            ]);
        } catch (\Exception $e) {
            return $e->getResponse();
        }
        return $response;
    }

    public function GetProducts($method = 'GET', $url = 'products') {
        $headers = [
            'Authorization'   => 'Bearer ' . env('SMAKE_KEY',''),
            'Content-Type'    => 'application/json',
            'Accept'          => 'application/json',
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

    public function UploadMedia($designPath, $fileName, $contentLength, $destinationUrl = 'media') {
        $body = fopen($designPath . $fileName, 'r');
        $headers = [
            'Authorization'   => 'Bearer ' . env('SMAKE_KEY',''),
            'Content-Type'    => 'application/png',
            'Content-Length'  => $contentLength,
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
    }

    public function CheckoutOrder($body, $url) {
        dd($body);
        $headers = [
            'Authorization'   => 'Bearer ' . env('SMAKE_KEY',''),
            'Content-Type'    => 'application/json',
            'Accept'          => 'application/json',
            'Accept-Language' => 'nl'
        ];

        try {
            $client = new Client(['base_uri' => env('SMAKE_URI', '')]);
            $response = $client->request('POST', $url, [
                'headers' => $headers,
                'body' => $body
            ]);
        } catch (\Exception $e) {
            return $e->getResponse();
        }
        return $response;
    }
}
