<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Traits\SmakeApi;
use App\Http\Traits\DebugLog;
use App\CompositeMediaDesign;
use App\FrontCustomization;
use App\Order;
use App\OrderItem;
use App\Customer;
use App\Variant;
use App\Attribute;
use App\Front;
use App\Design;

class OrderController extends Controller
{
    public $logFile = 'public/logs/message.txt';
    use DebugLog;
    use SmakeApi;

    public function dashboard()
    {
        return view('orders.dashboard');
    }

    public function index()
    {
        return view('orders.index');
    }

    public function create(Request $request)
    {
        //
    }

    public function store(Request $request)
    {
        $orderItems = json_decode($request->get('orderItems'));
        $customerId = json_decode($request->get('customer'));

        try {
            $newOrder = new Order();
            $newOrder->orderStatus = 'new';
            $newOrder->customerId = $customerId;
            $newOrder->shippingMethod = 'versand-niederlade-69';
            $newOrder->save();

            foreach($orderItems as $orderItem)
            {
                $newItem = new OrderItem();
                $newItem->orderId = $newOrder->id;
                $newItem->qty = $orderItem->items->qty;
                $newItem->variantId = $orderItem->items->variantId;
                $newItem->save();
            }
        }
        catch (\Exception $e) {
            return response()->json('Er is iets fout gegaan bij het aanmaken van de order', 500);
        }

        return response()->json($newOrder->id, 200);
    }

    public function checkOrder(Request $request)
    {
        return('checOrder');
    }

    public function listOrder()
    {
        return('checOrder');
    }
}


