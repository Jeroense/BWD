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
    public function __construct(OrderService $service) {
        $this->autoOrder = $service;
    }

    public function manageBolOrders() {
        $bolOrders[] = $this->getBolOrders();

    }

    public function getBolOrders() {
       // get BolApi list of new orders
       // return array
    }

    public function manageSmakeOrders() {
        //
    }

    public function findAndDispatchOrders() {
        $newOrders = $this->autoOrder->getNewOrders();
        if($newOrders != null) {
            foreach($newOrders as $order) {
                $result = $this->autoOrder->orderCustomVariant($order);
                //handle errors by sending email and log message in errorlog
                //return null
            }
        }
        return null;
    }

    public function changeInt() {
        \DB::table('tests')->where('id', 1)->update(['Integer' => 3]);
        return null;
    }

    public function changeName() {
        \DB::table('tests')->where('id', 1)->update(['String' => str_random(10)]);
        return null;
    }
}

