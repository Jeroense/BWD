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
use App\Design;
use App\Services\OrderService;

class OrderController extends Controller
{
    protected $orders;
    public function __construct(OrderService $service) {
        $this->orders = $service;
    }

    use SmakeApi;

    public function dashboard() {
        return view('orders.dashboard');
    }

    public function index() {
        return view('orders.index');
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
        $orderItems = json_decode($request->get('orderItems'));

        $this->orders->createOrder($orderItems);

        // try {
        //     $newOrder = new Order();
        //     $newOrder->shippingMethod = 'versand-niederlade-69';
        //     $newOrder->save();


        //     foreach($orderItems as $orderItem) {
        //         $newItem = new OrderItem();
        //         $newItem->orderId = $newOrder->id;
        //         $newItem->qty = $orderItem->items->qty;
        //         $newItem->variantId = $orderItem->items->variantId;
        //         $newItem->save();
        //     }
        // }
        // catch (\Exception $e) {
        //     return response()->json('Er is iets fout gegaan bij het aanmaken van de order', 500);
        // }
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


