<?php

namespace App\Services;
use Illuminate\Http\Request;
use App\Http\Traits\SmakeApi;
use App\Http\Traits\BolApiV3;
use App\Http\Traits\DebugLog;
use App\CustomVariant;
use App\Order;
use App\OrderItem;
use App\Customer;
use App\PostAddress;
use App\ProductAttribute;
use App\AttributeValue;
use App\Jobs\GetBolOrdersJob;
use function GuzzleHttp\json_decode;

class OrderService
{
    public $logFile = 'public/logs/message.txt';

    use SmakeApi;
    use DebugLog;
    use BolAPiV3;

    private $bolErrorBody = null;
    private $erIsTenminsteEenOrderItemZonderCancelRequest = false;
    private $nullOrEmptyOrderIDsArePresent = [];
    private $nietBestaandeOrderIDSinOrderResponse = false;



    public function getOrdersFromBol($serverType)
    {



        $bolOrdersResponse = $this->make_V3_PlazaApiRequest($serverType,  'orders?fulfilment-method=FBR', 'get');

        if($bolOrdersResponse['bolstatuscode'] != 200)
        {
            $this->checkAndLogBolErrorResponse($bolOrdersResponse);

            return;
        }




            if( strpos( $bolOrdersResponse['bolheaders']['Content-Type'][0], 'json') === false )
            {

                return 'Geen JSON in bol-orders-response!';
            }

            $this->putResponseInFile("bolGetOrdersResponse-{$serverType}.txt", $bolOrdersResponse['bolstatuscode'],
                $bolOrdersResponse['bolreasonphrase'], $bolOrdersResponse['bolbody']);

            $bolRespBodystdClassObject = json_decode($bolOrdersResponse['bolbody']);


            if( empty($bolRespBodystdClassObject->orders) || empty($bolRespBodystdClassObject->orders[0]->orderId))
            {

                dump( 'Geen openstaande orders vanuit BOL!', $bolRespBodystdClassObject);  // dit klopt niet helemaal..
                return 'Geen openstaande orders vanuit BOL!';
            }

            dump("De 1e order is aanwezig en heeft een orderId");

            // checken of er orders aanwezig zijn, en of elke order in de order response een orderid heeft
            // if( isset($bolRespBodystdClassObject->orders[0]->orderId)  ) {
            //     OrderId isset = true bij String.Empty
                foreach($bolRespBodystdClassObject->orders as $order)
                {
                    dump($order->orderId);
                    if( !isset($order->orderId) || ( isset( $order->orderId ) && (string)$order->orderId == '' ) )
                    {
                        \array_push( $this->nullOrEmptyOrderIDsArePresent, 'ispresent');
                    }
                }
                    if(\in_array('ispresent', $this->nullOrEmptyOrderIDsArePresent))
                    {
                        $this->nietBestaandeOrderIDSinOrderResponse = true;

                    }
                    dump('Zijn er lege orderIds? : ', \in_array('ispresent', $this->nullOrEmptyOrderIDsArePresent) ? 'Ja' : 'Nee');
            //--------------

            if($this->nietBestaandeOrderIDSinOrderResponse)
            {

                $this->putResponseInFile("Missing-orderIDs-in-OrderResponse-{$serverType}", $bolOrdersResponse['bolstatuscode'],
                                            $bolOrdersResponse['bolreasonphrase'], $bolOrdersResponse['bolbody']);

                dump('Ongeldige of ontbrekende OrderIDs in Order response!');


            }




                foreach($bolRespBodystdClassObject->orders as $order)
                {

                    if( empty($order->orderId) || empty($order->orderItems) )
                    {
                        continue;
                    }

                    foreach ($order->orderItems as $item)
                    {

                            if( $item->cancelRequest == false )
                            {
                                $this->erIsTenminsteEenOrderItemZonderCancelRequest = true;
                                dump('Er is tenminste 1 order item zonder cancel request: item: '); dump($item->orderItemId);
                            }
                    }


                    GetBolOrdersJob::dispatch($order, $serverType);

                }

        // }
    }


    public function checkAndLogBolErrorResponse($bol_response)
    {
        $code = (string)$bol_response['bolstatuscode']; $firstNumber = \substr($code, 0, 1);


        if(strpos( $bol_response['bolheaders']['Content-Type'][0], 'json') )
        {
            if( isset($bol_response['bolbody']) )
            {

                $this->bolErrorBody = (string)$bol_response['bolbody'];
            }

            switch($firstNumber)
            {
                case '4':
                    putContent('/client_errors.txt', $code, $bol_response['bolreasonphrase']);
                break;

                case '5':
                    putContent('/server_errors.txt', $code, $bol_response['bolreasonphrase']);
                break;

                default:
                    putContent('/other_errors.txt', $code, $bol_response['bolreasonphrase']);
            }
        }
        return;
    }

    public function putContent($fileName, $code, $phrase)
    {
        file_put_contents( storage_path( 'app/public') . $fileName, ($code . " " . $phrase  . "\r\n\r\n"), FILE_APPEND );
        if($this->bolErrorBody != null){
            file_put_contents( storage_path( 'app/public') . $fileName, $this->bolErrorBody . "\r\n\r\n", FILE_APPEND );
            $this->bolErrorBody = null;
        }
        return;
    }

    // end code bart



    // public function getNewOrders() {
    //     return Order::where('orderStatus', 'new')->pluck('id');
    // }

    // public function dispatchOrder($id) {
    //     return 'orderstatus';
    // }

    // public function orderCustomVariant($orderId) {
    //     $this->updateOrderStatus($orderId);
    //     $orderBody = $this->buildOrderObject($orderId);
    //     // $this->log_json('orderBody', 'buildOrderObject', $orderBody);

    //     $path = env('CHECKOUT_PATH','');
    //     $checkoutResponse = $this->postSmakeData($orderBody, $path); // if address is not complete -> statusCode 422 will be returned by Smake
    //     $this->log_responseBody( 'postSmakeData', 'checkoutBody', $checkoutResponse);
    //     return 0;
    //     if ($checkoutResponse->getStatusCode() == 201) {
    //         if ($this->IsOrderAccepted()) {
    //             if($this->isValidShippingHandle()) {
    //                 $shippingLine = $this->buildAndSubmitShippingLine();
    //                 if($shippingLine != null) {
    //                     $completedCheckout = $this->finalyzeCheckout($shippingLine);
    //                     if($completedCheckout != null) {
    //                         $this->updateOrder($completedCheckout);
    //                         return 'success';
    //                     };
    //                 }
    //             }
    //         }
    //     }
    //     return 'error';
    // }

    // public function updateOrderStatus($id) {
    //     $order = Order::find($id);
    //     $order->orderStatus = 'Initialized';
    //     $order->save();

    //     return;
    // }

    // public function buildOrderObject($orderId) {
    //     $customer = Customer::find(Order::find($orderId)->customerId);
    //     $deliveryAddress = $customer->hasDeliveryAddress != 0 ? PostAddress::where('customerId', $customer->id)->first() : $customer;
    //     $lnPrefix = $customer->lnPrefix == "" ? "" : ', ' . $customer->lnPrefix;

    //     $app = app();
    //     $shippingAddress = $app->make('stdClass');
    //     $shippingAddress->first_name = $deliveryAddress->firstName;
    //     $shippingAddress->last_name = $deliveryAddress->lastName . $lnPrefix;
    //     $shippingAddress->street1 = $deliveryAddress->street . ' ' . $deliveryAddress->houseNr;
    //     $shippingAddress->zip = $deliveryAddress->postalCode;
    //     $shippingAddress->city = $deliveryAddress->city;
    //     $shippingAddress->country_code = $deliveryAddress->countryCode;
    //     $shippingAddress->province_code = $deliveryAddress->provinceCode;
    //     $shippingAddress->phone = $deliveryAddress->phone;
    //     $shippingAddress->email = $deliveryAddress->email;

    //     $billingAddress = $app->make('stdClass');
    //     $billingAddress->first_name = 'Barry';
    //     $billingAddress->last_name = 'Bles';
    //     $billingAddress->street1 = 'Ulenpasweg 2F4';
    //     $billingAddress->zip = '7041 GB';
    //     $billingAddress->city = "'s-Heerenberg";
    //     $billingAddress->country_code = 'NL';
    //     $billingAddress->province_code = 'GD';
    //     $billingAddress->phone = '0314653130';
    //     $billingAddress->email = 'info@internetsport.nl';

    //     $orderedItems = OrderItem::where('orderId', $orderId)->get();
    //     $items = array();

    //     foreach($orderedItems as $item) {
    //         $smakeVariantId = CustomVariant::where('id', $item->variantId)->value('smakeVariantId');
    //         $itemObject = $app->make('stdClass');
    //         $itemObject->variant_id = $smakeVariantId;
    //         $itemObject->quantity = $item->qty;
    //         array_push($items, $itemObject);
    //     }

    //     $checkout = $app->make('stdClass');
    //     $checkout->email = 'info@internetsport.nl';
    //     $checkout->items = $items;
    //     $checkout->shipping_address = $shippingAddress;
    //     $checkout->billing_address = $billingAddress;

    //     return json_encode((array)$checkout);
    // }

    // public function updateOrder($completedCheckout) {
    //     $thisOrder = json_decode($completedCheckout->getBody());
    //     $order = Order::find($variantId);

    //     $order->smakeOrderId = $thisOrder->id;
    //     $order->orderStatus = $thisOrder->state;
    //     $order->shippingRate = $shippingLine->shipping_line->price;
    //     $order->orderAmount = $shippingLine->subtotal;
    //     $order->totalTax = $shippingLine->total_tax;
    //     $order->save();
    //     return;
    // }

    // public function IsOrderAccepted() {
    //     $url = 'checkouts/'.$thisOrder->id.'/shipping-rates';
    //     $response = $this->getSmakeData($url);

    //     if ($response->getStatusCode() != 202 || $response->getStatusCode() != 200) {    // reasonPhrase = "Accepted"
    //         return false;
    //     }

    //     $pollUrl = $response->getHeaders()['Location'][0];  // retrieve poll url

    //     for($i = 0; $i < 100; $i++) {
    //         usleep(100000);
    //         $pollResult = $this->Poll($pollUrl);
    //         if($pollResult->getStatusCode() === 200) {
    //             return true;
    //         }
    //     }

    //     return false;
    // }

    // public function isValidShippingHandle() {
    //     $url = 'checkouts/'.$thisOrder->id.'shipping-rates';
    //     $response = $this->getSmakeData($url);
    //     $shippingOptions = json_decode($response->getBody())->data;
    //     $shippingHandles = [];

    //     foreach($options as $option) {  // build array of available shipping 'handle' options
    //         array_push($shippingHandles, $option->handle);
    //     }

    //     $defaultHandle = env('SHIPPING_HANDLE', '');

    //     if (!in_array($defaultHandle, $shippingHandles)) {
    //         return false;
    //     }

    //     return true;
    // }

    // public function buildAndSubmitShippingLine() {
    //     $app = app();
    //     $shippingHandle = $app->make('stdClass');
    //     $shippingHandle->handle = env('SHIPPING_HANDLE', '');
    //     $shippingLine = $app->make('stdClass');
    //     $shippingLine->shipping = $shippingHandle;

    //     $shippingResponse = $this->postSmakeData(json_encode($shippingLine), 'checkouts/'.$thisOrder);

    //     if ($shippingResponse->getStatusCode() != 200) {
    //         // \Session::flash('flash_message', 'Er is iets fout gegaan met het versturen van de order naar Smake, neem contact op met de systeembeheerder');
    //         return null;
    //     }

    //     return $shippingResponse;
    // }

    // public function finalyzeCheckout($shippingLine) {
    //     $payment = $app->make('stdClass');
    //     $payment->handle = "invoice";
    //     $payment->amount = $shippingLine->total;
    //     $completeCheckout = $app->make('stdClass');
    //     $completeCheckout->payment = $payment;
    //     // dd($completeCheckout);
    //     // $checkOutResponse= $this->postSmakeData(json_encode($completeCheckout), 'checkouts/'.$thisOrder.'/complete');

    //     if ($checkOutResponse->getStatusCode() != 200) {
    //         return null;
    //     }

    //     return $checkOutResponse;
    // }

    /****************/

    // public $logFile = 'public/logs/message.txt';
    // use SmakeApi;
    // use DebugLog;

    /*********** Bol functions ***********/

    public function getProductsToBePublished()
    {
        return CustomVariant::where('isPublishedAtBol', 'initiated')->pluck('id');
    }

    public function buildProductFeed($customVariantIds)
    {
        $feed_file_name = "Borduurwerkdeal" . time();
        $feed_header = '';
        $products = ProductAttribute::with('attrValues')->get();

        foreach($products as $product) {
            $feed_header .= $product->product_attribute_key ."\t";
        }

        // $file = fopen( env('CONTENTFEED_PATH', '') . '/' .  $feed_file_name  . '.txt', 'w');
        $file = fopen( public_path('contentFeed') . '/' .  $feed_file_name  . '.txt', 'w');
        fwrite($file, $feed_header . PHP_EOL);

        foreach($customVariantIds as $id) {
            $product_data = '';
            $shirt = CustomVariant::find($id);

            foreach($products as $product) {
                if(strpos($product->attrValues->attr_value, '->') !== false) {  // dit moet volgens php manual !== zijn, geen !=
                    $column = substr($product->attrValues->attr_value, strpos($product->attrValues->attr_value, '>') + 1);
                    $product_data .= strtoupper($shirt->$column) != 'XXXL' ? $shirt->$column . "\t" : $product_data .= '3XL' . "\t";
                } else {
                    $product_data .= $product->attrValues->attr_value . "\t";
                }
            }

            $shirt->update(['isPublishedAtBol' => 'pending']);
            fwrite($file, $product_data . PHP_EOL);

        }

        fclose($file);
        return $feed_file_name;
    }

    public function publishProducts()
    {

    }

    // public function getBolOrders() // array
    // {
    //     // $orders = [];


    //     return $orders;
    // }

    /*********** Smake functions ***********/

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



    //*********************************************************************************************************** */
        return 0;   // safety net to prevent accidental orders
    //*********************************************************************************************************** */




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

        $data = json_decode($completedCheckout->getBody());

        $orderData = [];
        $orderData['smakeOrderId'] = $data->id;
        $orderData['orderStatus'] = $data->state;
        $orderData['shippingRate'] = $data->shipping_line->price;
        $orderData['shippingMethod'] = $data->shipping_line->handle;
        $orderData['orderAmount'] = $data->subtotal;
        $orderData['totalTax'] = $data->total_tax;
        $this->updateOrder($orderId, $orderData);

        return $completedCheckout->getStatusCode();
    }

    public function updateOrderStatus($id)
    {
        $order = Order::find($id);
        $order->orderStatus = 'initialized';
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
        $shippingAddress->street1 = $deliveryAddress->street . ' ' . $deliveryAddress->houseNr;  // housenrPostfix toevoegen.
        $shippingAddress->zip = $deliveryAddress->postalCode;
        $shippingAddress->city = $deliveryAddress->city;
        $shippingAddress->country_code = $deliveryAddress->countryCode;
        $shippingAddress->province_code = $deliveryAddress->provinceCode;
        $shippingAddress->phone = $deliveryAddress->phone;
        $shippingAddress->email = $deliveryAddress->email;

        // $logo = $app->make('stdClass');
        // $logo->image_id = ****** SmakeImageID
        // $whiteLabelAddress = $app->make('stdClass');
        // $whiteLabelAddress->first_name = $deliveryAddress->firstName;
        // $whiteLabelAddress->last_name = $deliveryAddress->lastName . $lnPrefix;
        // $whiteLabelAddress->street1 = $deliveryAddress->street . ' ' . $deliveryAddress->houseNr;
        // $whiteLabelAddress->zip = $deliveryAddress->postalCode;
        // $whiteLabelAddress->city = $deliveryAddress->city;
        // $whiteLabelAddress->country_code = $deliveryAddress->countryCode;
        // $whiteLabelAddress->province_code = $deliveryAddress->provinceCode;
        // $whiteLabelAddress->phone = $deliveryAddress->phone;
        // $whiteLabelAddress->email = $deliveryAddress->email;
        // $whiteLabelAddress->logo = $logo;

        $billingAddress = $app->make('stdClass');
        $billingAddress->first_name = 'Barry';
        $billingAddress->last_name = 'Bles';
        $billingAddress->street1 = 'Ulenpasweg 2F4';
        $billingAddress->zip = '7041 GB';
        $billingAddress->city = "'s-Heerenberg";
        $billingAddress->country_code = 'NL';
        $billingAddress->province_code = 'GD';
        $billingAddress->phone = '0314653130';
        $billingAddress->email = 'administratie@borduurwerkdeal.nl';

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
        // $checkout->shipping_address = $shippingAddress;
        $checkout->whitelabel_address = $whiteLabelAddress;
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

     //   $checkOutResponse= $this->putSmakeData(json_encode($completeCheckout), 'checkouts/'.$shippingData->id.'/complete');

        if ($checkOutResponse->getStatusCode() != 200) {
            return $checkOutResponse;
        }

        return $checkOutResponse;
    }

    public function orderProgress()
    {
        $CurrentStates = Order::where([
            ['orderStatus', '!=','initialized'],
            ['orderStatus', '!=','new']
        ])->pluck('id');

        if(count($CurrentStates) < 1) {
            return;
        }

        foreach($CurrentStates as $state){
            $result = $this->updateStatus($state);
        }

        return $result;
    }

    public function updateStatus($id)
    {
        $SelectedOrder = Order::find($id);
        $response = $this->getSmakeData('orders?filter[id]='.$SelectedOrder->smakeOrderId);
        $CurrentSmakeStatus = json_decode($response->getBody())->data[0]->state;

        if($CurrentSmakeStatus != $SelectedOrder->orderStatus) {
            $SelectedOrder->orderStatus = $CurrentSmakeStatus;
            $SelectedOrder->save();
        }

        return $response->getStatusCode();
    }
}

