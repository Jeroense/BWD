<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class TestController extends Controller
{
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
