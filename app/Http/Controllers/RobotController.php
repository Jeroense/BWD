<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OrderService;
use App\Services\OfferService;
use App\Http\Traits\DebugLog;
use App\Test;
use App\BolProcesStatus;
use App\Jobs\GetBolProcesStatusJob;

// scheduler trigger de robotcontroller, die triggert de orderservice, mijn code moet in de orderservice
class RobotController extends Controller
{
    public $logFile = 'public/logs/message.txt';
    use DebugLog;

    protected $autoOrder;
    protected $bolOfferService;

    public function __construct(OrderService $service, OfferService $offer_service)
    {
        $this->autoOrder = $service;
        $this->bolOfferService = $offer_service;
    }


    public function requestBolToConstructBolOffersExportCSVFile(){

        $this->bolOfferService->prepare_CSV_Offer_Export('prod');
    }


    public function update_offer_export_process_statusses_in_local_DB(){

        $this->bolOfferService->update_process_status_create_offer_export();
    }


    public function publishProducts()
    {
        $newProducts = $this->autoOrder->getProductsToBePublished();

        if(count($newProducts) < 1){
            return;
        }

        $productFeed = $this->autoOrder->buildProductFeed($newProducts);
        return $this->autoOrder->publishProducts($productFeed);
    }

    public function processBolOrders()
    {

        // $bolOrders = $this->autoOrder->getOrdersFromBol();
        $this->autoOrder->getOrdersFromBol('demo');

        // if(count($bolOrders) < 1) {
        //     return 0;
        // }

        // $status = $this->persistBolOrders($bolOrders);
        // return $status;
        return;
    }

    public function findAndDispatchOrders()
    {
        $newOrders = $this->autoOrder->getNewOrders();

        if($newOrders != null) {
            foreach($newOrders as $order) {
                $result = $this->autoOrder->orderCustomVariant($order);

                // ???? $this->updateBolOrderStatus($updates);

                //handle errors by sending email and log message in errorlog
                //return null
            }
        }

        return null;
    }

    public function statusCheck()
    {
        $updates = $this->autoOrder->orderProgress();
        $status = $this->updateBolOrderStatus($updates);
        return $status;
    }

    public function updatePendingProcessStatusses()
    {
        // alle proces statussen , uitgezonderd die met eventType 'CREATE_OFFER_EXPORT', daar is een andere schedule-functie,
        // en flow voor.
        $pending_process_statusses_in_db = BolProcesStatus::where([ ['status', '=', 'PENDING'],
                                                                    ['eventType', '!=', 'CREATE_OFFER_EXPORT']
                                                                    ])->get();

        if($pending_process_statusses_in_db->count() == 0){
            return;
        }

        foreach($pending_process_statusses_in_db as $proces_status){
            GetBolProcesStatusJob::dispatch($proces_status);
        }
    }

}

