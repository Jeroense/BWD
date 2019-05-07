<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OrderService;
use App\Services\OfferService;
use App\Http\Traits\DebugLog;
use app\Test;

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


    public function update_All_Process_statusses_In_Local_DB(){

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
        $this->autoOrder->getOrdersFromBol();

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

}

