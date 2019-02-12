<?php
namespace App\Http\Traits;
trait DebugLog
{
    // ini_set("log_errors", 1);
    // ini_set("error_log", "logs/errors.log");

    public function log_responseBody($text, $command, $response) {
        $data = json_decode($response->getBody());
        $formattedData = json_encode($data, JSON_PRETTY_PRINT);
        $logFile = fopen('public/logs/message.txt', 'a');
        fwrite($logFile, '***** '.date(DATE_RFC2822).PHP_EOL);
        fwrite($logFile, '*** called function: '. $text.PHP_EOL);
        fwrite($logFile, '*** command: '. $command.PHP_EOL.PHP_EOL);
        fwrite($logFile, $formattedData.PHP_EOL.PHP_EOL);
        fclose($logFile);
        return null;
    }

    public function log_json($response) {
        $data = json_decode($response);
        $formattedData = json_encode($data, JSON_PRETTY_PRINT);
        $logFile = fopen('public/logs/message.txt', 'a');
        fwrite($logFile, '***** '.date(DATE_RFC2822).PHP_EOL);
        // fwrite($logFile, '*** called function: '. $text.PHP_EOL);
        // fwrite($logFile, '*** command: '. $command.PHP_EOL.PHP_EOL);
        fwrite($logFile, $formattedData.PHP_EOL.PHP_EOL);
        fclose($logFile);
        return null;
    }

    // public function log_json($text, $command, $response) {
    //     $data = json_decode($response);
    //     $formattedData = json_encode($data, JSON_PRETTY_PRINT);
    //     $logFile = fopen('public/logs/message.txt', 'a');
    //     fwrite($logFile, '***** '.date(DATE_RFC2822).PHP_EOL);
    //     fwrite($logFile, '*** called function: '. $text.PHP_EOL);
    //     fwrite($logFile, '*** command: '. $command.PHP_EOL.PHP_EOL);
    //     fwrite($logFile, $formattedData.PHP_EOL.PHP_EOL);
    //     fclose($logFile);
    //     return null;
    // }
    public function log_DBrecord($record, $file = 'logs/message.txt') {
        $reflector = new \ReflectionClass($record);
        $classProperty = $reflector->getProperty('attributes');
        $classProperty->setAccessible(true);
        $data = $classProperty->getValue($record);
        $logFile = fopen($file, 'a');
        fwrite($logFile, '***** '.date(DATE_RFC2822).PHP_EOL);
        foreach($data as $key => $value) {
            fwrite($logFile, $key .' => '.  $value.PHP_EOL);
        }
        fwrite($logFile, PHP_EOL);
        fclose($logFile);
        return null;
    }

    public function log_item($key, $item) {
        $logFile = fopen('logs/message.txt', 'a');
        fwrite($logFile, '***** '.date(DATE_RFC2822).PHP_EOL);
        fwrite($logFile, $key . ' => ' . $item.PHP_EOL.PHP_EOL);
        fclose($logFile);
        return null;
    }

    public function log_var($var, $file = 'logs/message.txt') {
        $logFile = fopen($file, 'a');
        fwrite($logFile, '***** '.date(DATE_RFC2822).PHP_EOL);
        fwrite($logFile, $var.PHP_EOL.PHP_EOL);
        fclose($logFile);
        return null;
    }

    public function log_array($items, $file = 'logs/message.txt') {
        $logFile = fopen($file, 'a');
        fwrite($logFile, '***** '.date(DATE_RFC2822).PHP_EOL);
        foreach($items as $item) {
            fwrite($logFile, $item.PHP_EOL);
        }
        fwrite($logFile, PHP_EOL);
        fclose($logFile);
        return null;
    }
}

