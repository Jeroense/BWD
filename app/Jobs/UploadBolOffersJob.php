<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Redis;
use App\Http\Traits\BolApiV3;

class UploadBolOffersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, BolApiV3;

    private $bol_offer;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($offer)
    {
        $this->bol_offer = $offer;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Redis::throttle('upload_bol_offer')->allow(1)->every(1)->then(function () {

        // file_put_contents( storage_path( 'app/public') . '/' . 'BolOffersUploadThrottling_log.txt', ((string)date('D, d M Y H:i:s:v') . "\r\n" . \microtime(true) . "\r\n" . $this->bol_offer . "\r\n\r\n"), FILE_APPEND );
            dump('Uploading to bol: ', $this->bol_offer);

            // $bolOfferResponse =  $this->make_V3_PlazaApiRequest('demo', 'offers', 'post', $this->bol_offer);
            // dump($bolOfferResponse);

        }, function () {
            // Could not obtain lock...

            file_put_contents( storage_path( 'app/public') . '/' . 'TestRedisThrottling_log.txt', ((string)date('D, d M Y H:i:s:v') . "\r\n" . \microtime(true) .  "\r\n" . "Could not obtain lock.." . "\r\n\r\n"), FILE_APPEND );

             return $this->release(3);
        });
    }
}
