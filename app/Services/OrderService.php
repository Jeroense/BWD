<?php

namespace App\Services;
use App\Http\Traits\SmakeApi;
use App\Http\Traits\DebugLog;
use App\CustomVariant;
use App\Order;
use App\OrderItem;
use App\Customer;

class OrderService {

    public $logFile = 'public/logs/message.txt';
    use SmakeApi;
    use DebugLog;

    public function createOrder($orderItems) {
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
        return;
    }

    public function getNewOrders() {
        return Order::where('orderStatus', 'new')->pluck('id');
    }

    public function dispatchOrder($id) {
        return 'orderstatus';
    }

    public function orderCustomVariant($orderId) {
                                                                                            // $this->log_var('start of orderVariant id = '.$orderId, $this->logFile);

        // $newOrderId = Order::where('id', $orderId)->value('id');



                                                                                            // $this->log_array($orderedItems, $this->logFile);

        $orderBody = $this->buildOrderObject($orderId);
        $this->log_json('buildOrderObject', 'orderBody', $orderBody);
        $path = env('CHECKOUT_PATH','');
                                                                                            // $this->log_json('orderVariant', 'checkoutBody', $checkoutBody);
        $checkoutResponse = $this->postSmakeData($orderBody, $path);
        if ($checkoutResponse->getStatusCode() == 201) {
                                                                                            // $this->log_var('$checkoutResponse->getStatusCode()', $this->logFile);
            if ($this->IsOrderAccepted()) {
                                                                                            // $this->log_var('$this->orderIsAccepted()', $this->logFile);
                if($this->isValidShippingHandle()) {
                    $shippingLine = $this->buildAndSubmitShippingLine();
                    if($shippingLine != null) {
                        $completedCheckout = $this->finalyzeCheckout($shippingLine);
                        if($completedCheckout != null) {
                            $this->updateOrder($completedCheckout);
                            return 'success';
                        };
                    }
                }
            }
        }
        return 'error';
    }

    public function buildOrderObject($orderId) {
        // $customerId = Order::find($orderId)->customerId;
        // $this->log_var('customer ID =>'.$customerId, $this->logFile);
        // $customer = Customer::find($customerId);

        $customer = Customer::find(Order::find($orderId)->customerId);
$this->log_DBrecord($customer, $this->logFile);
        $app = app();

        $billingAddress = $app->make('stdClass');
        $billingAddress->first_name = 'Barry';
        $billingAddress->last_name = 'Bles';
        $billingAddress->street1 = 'Ulenpasweg 2F4';
        $billingAddress->zip = '7041 GB';
        $billingAddress->city = "'s-Heerenberg";
        $billingAddress->country_code = 'NL';
        $billingAddress->province_code = 'GD';
        $billingAddress->phone = '0314653130';
        $billingAddress->email = 'info@internetsport.nl';

        $shippingAddress = $app->make('stdClass');
        $shippingAddress->first_name = 'Barry';
        $shippingAddress->last_name = 'Bles';
        $shippingAddress->street1 = 'Ulenpasweg 2F4';
        $shippingAddress->zip = '7041 GB';
        $shippingAddress->city = "'s-Heerenberg";
        $shippingAddress->country_code = 'NL';
        $shippingAddress->province_code = 'GD';
        $shippingAddress->phone = '0314653130';
        $shippingAddress->email = 'info@internetsport.nl';
        $checkout = $app->make('stdClass');
        $checkout->email = 'info@internetsport.nl';

        $orderedItems = OrderItem::where('orderId', $orderId)->get();
        $items = array();

        foreach($orderedItems as $item) {
            $smakeVariantId = CustomVariant::where('id', $item->variantId)->value('smakeVariantId');
            $itemObject = $app->make('stdClass');
            $itemObject->variant_id = $smakeVariantId;
            $itemObject->quantity = $item->qty;
            array_push($items, $itemObject);
        }

        $checkout->items = $items;
        $checkout->shipping_address = $shippingAddress;
        return json_encode((array)$checkout);
    }

    public function updateOrder($completedCheckout) {
        $thisOrder = json_decode($completedCheckout->getBody());
        $order = Order::find($variantId);

        $order->smakeOrderId = $thisOrder->id;
        $order->orderStatus = $thisOrder->state;
        $order->shippingRate = $shippingLine->shipping_line->price;
        $order->orderAmount = $shippingLine->subtotal;
        $order->totalTax = $shippingLine->total_tax;
        $order->save();
        return;
    }

    public function IsOrderAccepted() {
        $url = 'checkouts/'.$thisOrder->id.'/shipping-rates';
        $response = $this->getSmakeData($url);
                                                                                        // $this->log_response($url, 'getSmakeData', $response);

        if ($response->getStatusCode() != 202 || $response->getStatusCode() != 200) {    // reasonPhrase = "Accepted"
            return false;
        }

        $pollUrl = $response->getHeaders()['Location'][0];  // retrieve poll url

        for($i = 0; $i < 100; $i++) {
            usleep(100000);
            $pollResult = $this->Poll($pollUrl);
            if($pollResult->getStatusCode() === 200) {
                return true;
            }
        }

        return false;
    }

    public function isValidShippingHandle() {
        $url = 'checkouts/'.$thisOrder->id.'shipping-rates';
        $response = $this->getSmakeData($url);
        $shippingOptions = json_decode($response->getBody())->data;
        $shippingHandles = [];

        foreach($options as $option) {  // build array of available shipping 'handle' options
            array_push($shippingHandles, $option->handle);
        }

        $defaultHandle = env('SHIPPING_HANDLE', '');

        if (!in_array($defaultHandle, $shippingHandles)) {
            return false;
        }

        return true;
    }

    public function buildAndSubmitShippingLine() {
        $app = app();
        $shippingHandle = $app->make('stdClass');
        $shippingHandle->handle = env('SHIPPING_HANDLE', '');
        $shippingLine = $app->make('stdClass');
        $shippingLine->shipping = $shippingHandle;

        $shippingResponse = $this->postSmakeData(json_encode($shippingLine), 'checkouts/'.$thisOrder);

        if ($shippingResponse->getStatusCode() != 200) {
            // \Session::flash('flash_message', 'Er is iets fout gegaan met het versturen van de order naar Smake, neem contact op met de systeembeheerder');
            return null;
        }

        return $shippingResponse;
    }

    public function finalyzeCheckout($shippingLine) {
        $payment = $app->make('stdClass');
        $payment->handle = "invoice";
        $payment->amount = $shippingLine->total;
        $completeCheckout = $app->make('stdClass');
        $completeCheckout->payment = $payment;
// dd($completeCheckout);
        // $checkOutResponse= $this->postSmakeData(json_encode($completeCheckout), 'checkouts/'.$thisOrder.'/complete');

        if ($checkOutResponse->getStatusCode() != 200) {
            return null;
        }

        return $checkOutResponse;
    }
}

