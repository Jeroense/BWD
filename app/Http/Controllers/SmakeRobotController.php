<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OrderService;
use App\Http\Traits\DebugLog;
use app\Test;

class RobotController extends Controller
{
    public $logFile = 'public/logs/message.txt';
    use DebugLog;

    protected $smakeService;
    public function __construct(OrderService $service)
    {
        $this->smakeService = $service;
    }

    public function publishProducts()
    {
        $newProducts = $this->smakeService->getProductsToBePublished();

        if(count($newProducts) < 1){
            return;
        }

        $productFeed = $this->smakeService->buildProductFeed($newProducts);
        return $this->smakeService->publishProducts($productFeed);
    }

    public function findAndDispatchOrders()
    {
        $newOrders = $this->smakeService->getNewOrders();

        if($newOrders != null) {
            foreach($newOrders as $order) {
                $result = $this->smakeService->orderCustomVariant($order);

                // ???? $this->updateBolOrderStatus($updates);

                //handle errors by sending email and log message in errorlog
                //return null
            }
        }

        return null;
    }

    public function statusCheck()
    {
        $updates = $this->smakeService->orderProgress();
        $status = $this->updateBolOrderStatus($updates);
        return $status;
    }

}

