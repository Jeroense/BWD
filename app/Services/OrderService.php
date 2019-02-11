<?php

namespace App\Services;
use App\Http\Traits\SmakeApi;
use App\Http\Traits\DebugLog;
use App\CustomVariant;
use App\Order;
use App\OrderItem;
use App\Customer;
use App\PostAddress;


// $this->log_json( ** $response Body **, 'checkoutBody', $checkoutBody);
// $this->log_var( ** $var **, $this->logFile);
// $this->log_DBrecord( ** DBrecord **, $this->logFile);

class OrderService {

    public $logFile = 'public/logs/message.txt';
    use SmakeApi;
    use DebugLog;

    public function getNewOrders() {
        return Order::where('orderStatus', 'new')->pluck('id');
    }

    public function dispatchOrder($id) {
        return 'orderstatus';
    }

    public function orderCustomVariant($orderId) {
        $this->updateOrderStatus($orderId);
        $orderBody = $this->buildOrderObject($orderId);
        // $this->log_json('orderBody', 'buildOrderObject', $orderBody);

        $path = env('CHECKOUT_PATH','');
        $checkoutResponse = $this->postSmakeData($orderBody, $path); // if address is not complete -> statusCode 422 will be returned by Smake
        $this->log_responseBody( 'postSmakeData', 'checkoutBody', $checkoutResponse);
        return 0;
        if ($checkoutResponse->getStatusCode() == 201) {
            if ($this->IsOrderAccepted()) {
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

    public function updateOrderStatus($id) {
        $order = Order::find($id);
        $order->orderStatus = 'Initialized';
        $order->save();

        return;
    }

    public function buildOrderObject($orderId) {
        $customer = Customer::find(Order::find($orderId)->customerId);
        $deliveryAddress = $customer->hasDeliveryAddress != 0 ? PostAddress::where('customerId', $customer->id)->first() : $customer;
        $lnPrefix = $customer->lnPrefix == "" ? "" : ', ' . $customer->lnPrefix;

        $app = app();
        $shippingAddress = $app->make('stdClass');
        $shippingAddress->first_name = $deliveryAddress->firstName;
        $shippingAddress->last_name = $deliveryAddress->lastName . $lnPrefix;
        $shippingAddress->street1 = $deliveryAddress->street . ' ' . $deliveryAddress->houseNr;
        $shippingAddress->zip = $deliveryAddress->postalCode;
        $shippingAddress->city = $deliveryAddress->city;
        $shippingAddress->country_code = $deliveryAddress->countryCode;
        $shippingAddress->province_code = $deliveryAddress->provinceCode;
        $shippingAddress->phone = $deliveryAddress->phone;
        $shippingAddress->email = $deliveryAddress->email;

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

        $orderedItems = OrderItem::where('orderId', $orderId)->get();
        $items = array();

        foreach($orderedItems as $item) {
            $smakeVariantId = CustomVariant::where('id', $item->variantId)->value('smakeVariantId');
            $itemObject = $app->make('stdClass');
            $itemObject->variant_id = $smakeVariantId;
            $itemObject->quantity = $item->qty;
            array_push($items, $itemObject);
        }

        $checkout = $app->make('stdClass');
        $checkout->email = 'info@internetsport.nl';
        $checkout->items = $items;
        $checkout->shipping_address = $shippingAddress;
        $checkout->billing_address = $billingAddress;

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

