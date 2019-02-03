<?php

namespace App\Services;
use App\Order;
use App\OrderItem;

class OrderService {
    public function makeOrder($orderItems) {
        $newOrder = new Order();
        $newOrder->shippingMethod = 'versand-niederlade-69';
        $newOrder->save();

        foreach($orderItems as $orderItem) {
            dd($orderItem);
            $newItem = new OrderItem();
            $newItem->orderId = $newOrder->id;
            $newItem->qty = $orderItem->items->qty;
            $newItem->variantId = $orderItem->items->variantId;
            $newItem->save();
        }
    }
}
