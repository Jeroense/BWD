<?php
namespace App\Http\Traits;
trait DebugLog
{
    // ini_set("log_errors", 1);
    // ini_set("error_log", "logs/errors.log");

    public function log_response($text, $command, $response) {
        $data = json_decode($response->getBody());
        $formattedData = json_encode($data, JSON_PRETTY_PRINT);
        $logFile = fopen('logs/message.txt', 'a');  // or fopen('logs/message.txt', 'a') to append to file
        fwrite($logFile, '***** '.date(DATE_RFC2822).PHP_EOL);
        fwrite($logFile, '*** called function: '. $text.PHP_EOL);
        fwrite($logFile, '*** command: '. $command.PHP_EOL.PHP_EOL);
        fwrite($logFile, $formattedData.PHP_EOL.PHP_EOL);
        fclose($logFile);
    }

    public function log_item($key, $item) { 
        $resource = fopen('logs/message.txt', 'a');
        fwrite($resource, $key . ' -> ' . $item);
        fclose($resource);
    }
}

