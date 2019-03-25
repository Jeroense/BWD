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

    protected $autoOrder;
    public function __construct(OrderService $service)
    {
        $this->autoOrder = $service;
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
        $bolOrders = $this->getBolOrders();

        if(count($bolOrders) < 1) {
            return 0;
        }

        $status = $this->persistBolOrders($bolOrders);
        return $status;
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

