<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use App\Variant;
use App\Http\Traits\DebugLog;

trait SmakeApi {

    public $logFile = 'public/logs/message.txt';
    use DebugLog;

    public function getSmakeData($url) {
        $headers = [
            'Authorization'   => 'Bearer ' . env('SMAKE_KEY',''),
            'Content-Type'    => 'application/json',
            'Accept'          => 'application/json',
            'Accept-Language' => 'nl'
        ];

        try {
            $client = new Client(['base_uri' => env('SMAKE_URI', '')]);

            $response = $client->request('GET', $url, [
                'headers' => $headers
            ]);
        } catch (\Exception $e) {
            return $e->getResponse();
        }
        return $response;
    }

    public function postSmakeData($body, $destinationUrl) {
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

    public function downloadMedia($id, $fileName, $path) {
        $resource = fopen($path . '/' . $fileName . '.png', 'w');
        $headers = [
            'Accept' => 'image/png',
        ];
        $options = [
            'sink' => $resource,
            'http_errors' => false
        ];

        $client = new Client([
            'base_uri' => env('BASE_TSHIRT_URL', '')
        ]);

        $tshirtImage = $client->get((string)$id, $options);
        fclose($resource);
        return $fileName . '.png';
    }
}
