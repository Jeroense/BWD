<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Traits\SmakeApi;
use App\CompositeMediaDesign;
use App\FrontCustomization;
use App\Order;
use App\OrderItem;
use App\Customer;
use App\Variant;
use App\Attribute;
use App\Front;
// use App\View;
use App\Design;

class OrderController extends Controller
{
    use SmakeApi;

    public function dashboard() {
        return view('orders.dashboard');
    }

    public function create($id)
    {
        return view('orders.create');
    }

    public function store(Request $request)
    {
        ini_set("log_errors", 1);
        ini_set("error_log", "logs/errors.log");
        $orderItems = json_decode($request->get('orderItems'));
        error_log($request->get('orderItems'));
        // dd($request);
        try {
            $newOrder = new Order();
            $newOrder->shippingMethod = 'Versand';
            $newOrder->save();

            $orderItems = json_decode($request->get('orderItems'));
            foreach($orderItems as $orderItem) {
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
}


