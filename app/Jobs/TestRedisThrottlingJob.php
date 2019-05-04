<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Redis;

class TestRedisThrottlingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $mytext;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($mytext)
    {
        $this->mytext = $mytext;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        Redis::throttle('throttle_test')->allow(3)->every(1)->then(function () {
            // Job logic...
            // $tijd = new \DateTime();
            // $tijd->format('D, d M Y H:i:s:v');
            file_put_contents( storage_path( 'app/public') . '/' . 'TestRedisThrottling_log.txt', ((string)date('D, d M Y H:i:s:v') . "\r\n" . \microtime(true) . "\r\n" . $this->mytext . "\r\n\r\n"), FILE_APPEND );
            // file_put_contents( storage_path( 'app/public') . '/' . 'TestRedisThrottling_log.txt', ( "\r\n" . \microtime(true) . "\r\n" . $this->mytext . "\r\n\r\n"), FILE_APPEND );
        }, function () {
            // Could not obtain lock...

            file_put_contents( storage_path( 'app/public') . '/' . 'TestRedisThrottling_log.txt', ((string)date('D, d M Y H:i:s:v') . "\r\n" . \microtime(true) .  "\r\n" . "Could not obtain lock.." . "\r\n\r\n"), FILE_APPEND );

             return $this->release(3);
        });


    }
}
