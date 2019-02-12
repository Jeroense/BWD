<?php

namespace App\Services;
use App\Http\Traits\SmakeApi;
use App\CustomVariant;
use App\Order;
use App\OrderItem;
use App\Customer;
use App\PostAddress;

class OrderService
{
    public $logFile = 'public/logs/message.txt';
    use SmakeApi;

    public function getNewOrders()
    {
        return Order::where('orderStatus', 'new')->pluck('id');
    }

    public function dispatchOrder($id)
    {
        return 'orderstatus';
    }

    public function orderCustomVariant($orderId)
    {
        $this->updateOrderStatus($orderId);
        $orderBody = $this->buildOrderObject($orderId);
        $path = env('CHECKOUT_PATH','');
        $checkoutResponse = $this->postSmakeData($orderBody, $path); // if address is not complete -> statusCode 422 will be returned by Smake

        if($checkoutResponse->getStatusCode() != 201) {  // 201 created
            return $checkoutResponse->getStatusCode();
        }

        $thisOrder = json_decode($checkoutResponse->getBody());
        $orderIsAccepted = $this->IsOrderAccepted($thisOrder->id);

        if ($orderIsAccepted->getStatusCode() != 200) {
            return $orderIsAccepted->getStatusCode();
        }

        $shippingHandle = $this->isValidShippingHandle($thisOrder->id);
        $shippingLine = $this->buildAndSubmitShippingLine($thisOrder->id, $shippingHandle);

        if($shippingLine->getStatusCode() != 200) {
            return $shippingLine->getStatusCode();
        }

        $completedCheckout = $this->finalyzeCheckout($shippingLine);

        if($completedCheckout->getStatusCode() != 200) {
            return $completedCheckout->getStatusCode();
        }

        $data = json_decode($completeCheckout->getBody());

        $orderData = [];
        $orderData['smakeOrderId'] = $data->id;
        $orderData['orderStatus'] = $data->state;
        $orderData['shippingRate'] = $data->shipping_line->price;
        $orderData['shippingMethod'] = $data->title;
        $orderData['orderAmount'] = $data->subtotal;
        $orderData['totalTax'] = $data->total_tax;
        $this->updateOrder($orderId, $orderData);

        return $completedCheckout->getStatusCode();
    }

    public function updateOrderStatus($id)
    {
        $order = Order::find($id);
        $order->orderStatus = 'Initialized';
        $order->save();

        return;
    }

    public function buildOrderObject($orderId)
    {
        $customer = Customer::find(Order::find($orderId)->customerId);
        $deliveryAddress = $customer->hasDeliveryAddress != 0 ? PostAddress::where('customerId', $customer->id)->first() : $customer;
        $lnPrefix = $deliveryAddress->lnPrefix == "" ? "" : ', ' . $deliveryAddress->lnPrefix;

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

        foreach($orderedItems as $item)
        {
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

    public function updateOrder($orderId, $orderData)
    {
        $update = Order::find($orderId);
        $update->smakeOrderId = $orderData['smakeOrderId'];
        $update->orderStatus = $orderData['orderStatus'];
        $update->shippingMethod = $orderData['shippingMethod'];
        $update->shippingRate = $orderData['shippingRate'];
        $update->orderAmount = $orderData['orderAmount'];
        $update->totalTax = $orderData['totalTax'];
        $update->save();

        return;
    }

    public function IsOrderAccepted($id)
    {
        $url = 'checkouts/'.$id.'/shipping-rates';
        $response = $this->getSmakeData($url);

        if ($response->getStatusCode() != 202 && $response->getStatusCode() != 200) {    // reasonPhrase = "Accepted"
            return $response;
        }

        $pollUrl = $response->getHeaders()['Location'][0];  // retrieve poll url

        for($i = 0; $i < 100; $i++)
        {
            usleep(100000);
            $pollResult = $this->Poll($pollUrl);

            if($pollResult->getStatusCode() === 200) {
                return $pollResult;
            }
        }

        return $pollResult;
    }

    public function isValidShippingHandle($id)
    {
        $url = 'checkouts/'.$id.'/shipping-rates';
        $response = $this->getSmakeData($url);
        $shippingOptions = json_decode($response->getBody())->data;
        $shippingHandles = [];

        foreach($shippingOptions as $option)    // build array of available shipping 'handle' options
        {
            array_push($shippingHandles, $option->handle);
        }

        return $shippingHandles[0];
    }

    public function buildAndSubmitShippingLine($id, $shippingLine)
    {
        $app = app();
        $shippingHandle = $app->make('stdClass');
        $shippingHandle->handle = $shippingLine;
        $shippingLine = $app->make('stdClass');
        $shippingLine->shipping = $shippingHandle;
        $shippingResponse = $this->putSmakeData(json_encode($shippingLine), 'checkouts/'.$id);

        if ($shippingResponse->getStatusCode() != 200) {
            // \Session::flash('flash_message', 'Er is iets fout gegaan met het versturen van de order naar Smake, neem contact op met de systeembeheerder');
            return $shippingResponse;
        }

        return $shippingResponse;
    }

    public function finalyzeCheckout($shippingLine)
    {
        $shippingData = json_decode($shippingLine->getBody());
        $app = app();
        $payment = $app->make('stdClass');
        $payment->handle = "invoice";
        $payment->amount = $shippingData->total;
        $completeCheckout = $app->make('stdClass');
        $completeCheckout->payment = $payment;
        $this->log_var('amount: '.$payment->amount, $this->logFile);

        $checkOutResponse= $this->putSmakeData(json_encode($completeCheckout), 'checkouts/'.$shippingData->id.'/complete');

        if ($checkOutResponse->getStatusCode() != 200) {
            return $checkOutResponse;
        }

        return $checkOutResponse;
    }
}

