<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BolRobotController extends Controller
{
    protected $bolService;
    public function __construct(OrderService $service)
    {
        $this->bolService = $service;
    }

    public function processBolOrders()
    {
        $bolOrders = $this->bolService->getOrdersFromBol();
        
        if(count($bolOrders) < 1) {
            return 0;
        }

        $status = $this->bolService->persistBolOrders($bolOrders);
        return $status;
    }
}
