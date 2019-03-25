<?php

namespace App\Services;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Traits\SmakeApi;
use App\Http\Traits\BolAPi;
use App\Http\Traits\DebugLog;
use App\CustomVariant;
use App\Order;
use App\OrderItem;
use App\Customer;
use App\PostAddress;
use App\ProductAttribute;
use App\AttributeValue;

class OrderService
{
    public $logFile = 'public/logs/message.txt';

    use SmakeApi;
    use DebugLog;
    use BolAPi;

    private $bolErrorBody = null;
    private $erIsTenminsteEenOrderItemZonderCancelRequest = false;
    private $nullOrEmptyOrderIDsArePresent = [];
    private $nietBestaandeOrderIDSinOrderResponse = false;

    public function getOrdersFromBol(){
        $deXml = require 'mytestxml.php';

        // om de xml test str te gebruiken wel eerst (test)customvariant entries aanmaken met de bijbehorende eans, title, reference codes
        CustomVariant::truncate();
        CustomVariant::create([
                'variantName' => 't-shirt football',
                'ean' => '5412810182312',
                'size' => '4XL',
                'filename' => 'plaatjetshirtfootball.png',
                'compositeMediaId' => 34563,
                'productionMediaId' => 12345,
                'width_mm' => 2000.1,
                'height_mm' => 2200.2]);

        CustomVariant::create([
                'variantName' => 't-shirt jantje',
                'ean' => '6812810182312',
                'size' => '4XL',
                'filename' => 'plaatjetshirtjantje.png',
                'compositeMediaId' => 34564,
                'productionMediaId' => 12346,
                'width_mm' => 2000.1,
                'height_mm' => 2200.2]);

        CustomVariant::create([
                'variantName' => 't-shirt pluche beertje bjorn',
                'ean' => '7012810182312',
                'size' => '3XL', 'filename' =>
                'plaatjetshirtbjorn.png',
                'compositeMediaId' => 34566,
                'productionMediaId' => 12348,
                'width_mm' => 1800.1,
                'height_mm' => 2100.2]);

        CustomVariant::create([
                'variantName' => 't-shirt oh yeah',
                'ean' => '7112810182312',
                'size' => '2XL',
                'filename' => 'plaatjetshirtoyeah.png',
                'compositeMediaId' => 34567,
                'productionMediaId' => 12349,
                'width_mm' => 1800.1,
                'height_mm' => 2100.2]);

        // $xml_obj = \simplexml_load_string($xmlstr);   //zelfde als onder
        // $xml_obj_2 = new \SimpleXMLElement($xmlstr);    //zelfde als boven

        // $xml_obj_toString = (new \SimpleXMLElement($xmlstr))->asXML();  // korte manier

        // $xml_obj_string = $xml_obj->asXML();        // krijg je de totaal-gevormde symmetrische xml string
        // $xml_obj_2_string = $xml_obj_2->asXML();    // krijg je de totaal-gevormde symmetrische xml string

        $bolOrdersRestEndpoint = '/services/rest/orders/v2';

        // GET /services/rest/orders/v2?page=1&fulfilment-method=FBR  // voorbeeld met query

        $accept_header = 'application/vnd.orders-v2.1+xml';

        // $bolOrdersResponse = $this->makePlazaApiRequest($bolOrdersRestEndpoint, 'get', $accept_header); // je krijgt alleen de 'open' orders

        $bolOrdersResponse = ['bolstatuscode' => 200, 'bolbody' => $deXml, 'bolreasonphrase' => 'OK', 'bolheaders' => ['Content-Type' => 'application/xml']];   // test response

        if($bolOrdersResponse['bolstatuscode'] != 200){     // checken op http-errors/codes
            $this->checkAndLogBolErrorResponse($bolOrdersResponse);
        }

        if($bolOrdersResponse['bolstatuscode'] == 200){
            $order_resp_code = $bolOrdersResponse['bolstatuscode'];
            $order_resp_phrase = $bolOrdersResponse['bolreasonphrase'];
            $order_resp_body = $bolOrdersResponse['bolbody'];
            $bolBodyXMLObject = new \SimpleXMLElement($order_resp_body);

            if( !isset($bolBodyXMLObject->Order->OrderId[0]) || (isset($bolBodyXMLObject->Order->OrderId[0]) && (string)$bolBodyXMLObject->Order->OrderId[0] == '') ){     // geen OrderId, geen order in $bolOrdersResponse, dus niet in $bolBodyXMLObject
                dump( 'Geen openstaande orders vanuit BOL!', $order_resp_code, $bolBodyXMLObject);
            }

            // checken of er orders aanwezig zijn, en of elke order in de order response een orderid heeft
            if( isset($bolBodyXMLObject->Order->OrderId[0])  ){   // OrderId isset = true bij String.Empty
                foreach($bolBodyXMLObject->Order as $order){
                    dump($order->OrderId);
                    if( !isset($order->OrderId) || ( isset( $order->OrderId ) && (string)$order->OrderId == '' ) ) {
                        \array_push( $this->nullOrEmptyOrderIDsArePresent, 'ispresent');
                    }
                }
                if(\in_array('ispresent', $this->nullOrEmptyOrderIDsArePresent)){
                    $this->nietBestaandeOrderIDSinOrderResponse = true;
                    dump($this->nullOrEmptyOrderIDsArePresent);
                }
                dump($this->nullOrEmptyOrderIDsArePresent);
            }

            if($this->nietBestaandeOrderIDSinOrderResponse){
                $this->checkAndLogBolErrorResponse($bolOrdersResponse);
                dd('Ongeldige of ontbrekende OrderIDs in Order response!');
            }

            // is er een order aanwezig in de resp-body en zijn alle orderid's niet null of String.Empty?
            if( isset($bolBodyXMLObject->Order->OrderId[0]) && (string)$bolBodyXMLObject->Order->OrderId[0] != '' &&  $this->nietBestaandeOrderIDSinOrderResponse == false){      // er is tenmiste 1 order, en alle orderid's zijn niet null of ''.
                dump("Er is tenminste 1 (bij bol openstaande) order aanwezig en alle orderid's zijn niet null of empty strings.");

                foreach($bolBodyXMLObject->Order as $order){
                    $this->erIsTenminsteEenOrderItemZonderCancelRequest = false; // is er tenminste 1 orderitem met 'CancelRequest' = false   aanwezig?
                    $this->nullOrEmptyOrderIDsArePresent = [];
                    foreach ($order->OrderItems->OrderItem as $item) {
                        if( strtoupper((string)$item->CancelRequest) == 'FALSE' ){
                            $this->erIsTenminsteEenOrderItemZonderCancelRequest = true;
                            dump('Er is tenminste 1 order item zonder cancel request: item: ' . $item->OrderItemId);
                        }
                    }
                    Order::where('bolOrderNr', (string)$order->OrderId)->doesntExist() ? $this->newOrder() : $this->existingOrder();  // ipv switch statement
                }
            }
        }
    }

    public function newOrder()
    {
        // nieuwe order aanmaken, zoeken naar of customer bekend is, zo niet nieuwe customer aanmaken, ook orderitems
        dump('Order bestaat nog niet in lokale DB.');
        dump('OrderId is hier: ' . (string)$order->OrderId);

        if(!$this->erIsTenminsteEenOrderItemZonderCancelRequest){
            return; // whatever there is to return !!
        }

        $customerAdressDataFromBolOrderResp =   ['firstName' => (string)$order->CustomerDetails->BillingDetails->Firstname,
                                                'lastName' => (string)$order->CustomerDetails->BillingDetails->Surname,
                                                'postalCode' => (string)$order->CustomerDetails->BillingDetails->ZipCode,
                                                'houseNr' => (string)$order->CustomerDetails->BillingDetails->Housenumber,
                                                'houseNrPostfix' => (string)$order->CustomerDetails->BillingDetails->HousenumberExtended];

        $shipmentAdressDataFromBolOrderResp =   ['firstName' => (string)$order->CustomerDetails->ShipmentDetails->Firstname,
                                                'lastName' => (string)$order->CustomerDetails->ShipmentDetails->Surname,
                                                'postalCode' => (string)$order->CustomerDetails->ShipmentDetails->ZipCode,
                                                'houseNr' => (string)$order->CustomerDetails->ShipmentDetails->Housenumber,
                                                'houseNrPostfix' => (string)$order->CustomerDetails->ShipmentDetails->HousenumberExtended,];

        $allCustomerDataFromBolOrderResp =      ['firstName' => (string)$order->CustomerDetails->BillingDetails->Firstname,
                                                'lastName' => (string)$order->CustomerDetails->BillingDetails->Surname,
                                                'street' => (string)$order->CustomerDetails->BillingDetails->Streetname,
                                                'postalCode' => (string)$order->CustomerDetails->BillingDetails->ZipCode,
                                                'houseNr' => (string)$order->CustomerDetails->BillingDetails->Housenumber,
                                                'houseNrPostfix' => (string)$order->CustomerDetails->BillingDetails->HousenumberExtended,
                                                'city' => (string)$order->CustomerDetails->BillingDetails->City,
                                                'countryCode' => (string)$order->CustomerDetails->BillingDetails->CountryCode,
                                                'email' => (string)$order->CustomerDetails->BillingDetails->Email  ];

        $allShipmentDataFromBolOrderResp =      ['firstName' => (string)$order->CustomerDetails->ShipmentDetails->Firstname,
                                                'lastName' => (string)$order->CustomerDetails->ShipmentDetails->Surname,
                                                'street' => (string)$order->CustomerDetails->ShipmentDetails->Streetname,
                                                'postalCode' => (string)$order->CustomerDetails->ShipmentDetails->ZipCode,
                                                'houseNr' => (string)$order->CustomerDetails->ShipmentDetails->Housenumber,
                                                'houseNrPostfix' => (string)$order->CustomerDetails->ShipmentDetails->HousenumberExtended,
                                                'city' => (string)$order->CustomerDetails->ShipmentDetails->City,
                                                'countryCode' => (string)$order->CustomerDetails->ShipmentDetails->CountryCode,
                                                'email' => (string)$order->CustomerDetails->ShipmentDetails->Email ];

        $nogNietBestaandeCustomer = Customer::where($customerAdressDataFromBolOrderResp)->doesntExist();

        if(!$nogNietBestaandeCustomer){ // bestaande customer, dwz slechts bestaand in alleen de customer table
            $bestaandeCust = Customer::where($customerAdressDataFromBolOrderResp)->first();
            $geenShipmentAdresNu = $customerAdressDataFromBolOrderResp == $shipmentAdressDataFromBolOrderResp; //is er een shipment adres? true als geen
            if($geenShipmentAdresNu){
                dump('Bestaande customer. Geen apart shipmentadres.');

                // 1st checken of er een (oud) shipment adres in de lokale DB bekend is bij deze customer, zo ja verwijderen
                // en met $bestaandeCust->update([ 'hasDeliveryAddress' => 0] ); het bestaande customer record updaten.
                if($bestaandeCust->hasDeliveryAddress == 1){    // $bestaande customer heeft bestaand (oud) afwijkend deliveryadress
                    $postAdrr = PostAddress::where('customerId', $bestaandeCust->id)->first();
                    if($postAdrr->exists()){
                        $postAdrr->delete();
                        $bestaandeCust->update([ 'hasDeliveryAddress' => 0] );
                    }
                }

                $this->storeNewBOLOrderInDB($bestaandeCust->id, (string)$order->OrderId);   // nu order aanmaken voor bekende customer zonder apart shipmentadr.

                if(isset($order->OrderItems->OrderItem[0])){    // maak orderitems aan:
                    foreach($order->OrderItems->OrderItem as $item){
                        if(strtoupper($item->CancelRequest) == 'FALSE'){
                            $this->storeNewOrderItemInDB($item,  Order::where('bolOrderNr', (string)$order->OrderId)->value('id') );    // $item is een instance of SimpleXMLElement
                        }
                    }
                }
            } else {   //  wel apart shipment adres nu
                dump('Bestaande customer. Wel apart shipmentadres nu.');
                // als er een bekend shipmentadr in lokale DB aanwezig is, deze deleten.
                // Dan er weer een aanmaken met recente data uit deze orderresponse
                if( PostAddress::where('customerId', $bestaandeCust->id)->exists() ){
                    PostAddress::where('customerId', $bestaandeCust->id)->delete();
                }

                $this->storeNewPostAddressInDB($allShipmentDataFromBolOrderResp, $bestaandeCust->id);
                $this->storeNewBOLOrderInDB($bestaandeCust->id, (string)$order->OrderId);   // nu order aanmaken voor bekende customer met net geupdate shipment adres.

                if(isset($order->OrderItems->OrderItem[0])){        // maak orderitems aan:
                    foreach($order->OrderItems->OrderItem as $item){
                        if(strtoupper($item->CancelRequest) == 'FALSE'){
                            $this->storeNewOrderItemInDB($item,  Order::where('bolOrderNr', (string)$order->OrderId)->value('id') );
                        }
                    }
                }
            }
        } else {
            $heeftGeenShipmentAdr = $customerAdressDataFromBolOrderResp == $shipmentAdressDataFromBolOrderResp;  // shipmentadr = billingadr
            dump('Shipment adres is het Billing adres: ' . ($heeftGeenShipmentAdr ? 'true' : 'false') );

            if(!$heeftGeenShipmentAdr){  // heeft wel ander shipment adres
                $this->storeNewCustomerInDB($allCustomerDataFromBolOrderResp, 1, 1);
                $this->storeNewPostAddressInDB( $allShipmentDataFromBolOrderResp, Customer::where($customerAdressDataFromBolOrderResp)->value('id') );
                $this->storeNewBOLOrderInDB(Customer::where($customerAdressDataFromBolOrderResp, ['hasDeliveryAddress' => 1])->value('id'), (string)$order->OrderId);

                if(isset($order->OrderItems->OrderItem[0])){
                    foreach($order->OrderItems->OrderItem as $item){
                        if(strtoupper($item->CancelRequest) == 'FALSE'){
                            $this->storeNewOrderItemInDB($item, Order::where('bolOrderNr', (string)$order->OrderId)->value('id'));    // $item is een instance of SimpleXMLElement
                        }
                    }
                }
            } else {
                $this->storeNewCustomerInDB($allCustomerDataFromBolOrderResp, 1, 0);
                $this->storeNewBOLOrderInDB(Customer::where($customerAdressDataFromBolOrderResp, ['hasDeliveryAddress' => 0])->value('id'), (string)$order->OrderId);       // nu order aanmaken in DB:

                if(isset($order->OrderItems->OrderItem[0])){        // nu orderitems aanmaken in DB:
                    foreach($order->OrderItems->OrderItem as $item){
                        if(strtoupper($item->CancelRequest) == 'FALSE'){
                            $this->storeNewOrderItemInDB($item, Order::where('bolOrderNr', (string)$order->OrderId)->value('id'));    // $item is een instance of SimpleXMLElement
                        }
                    }
                }
            }
        }
        dump($this->erIsTenminsteEenOrderItemZonderCancelRequest);
    }

    // scenario: order bestaat reeds in lokale DB. Nu kan er een OrderItem->CancelRequest op true staan, hier op checken
    // dit natuurlijk alleen bij orderItems van een order waarvan de status nog 'new' is.
    // in orderitems een kolom status: pending, failure success. in de orders response daarop checken en deze bolOrder(Item)State updaten aan de shipment/resource status van de bol status van het item
    // pas order naar smake als we van bol de shipment status vh orderitem als 'success' hebben geconfirmed
    // order komt binnen -> 5 min wachten, weer checken op cancellations -> dan shipment bevestigen aan bol -> shipment status confirmed/success van bol -> dan pas naar smake de order sturen
    public function existingOrder()
    {
        dump('Order bestaat reeds in DB');
        $de_order = Order::where('bolOrderNr', (string)$order->OrderId )->first();

        if(strtoupper($de_order->orderStatus) != 'NEW'){
            dump('Volgens BOL is deze order nog open. Order bestaat reeds in DB, maar order status is bij ons niet meer NEW');
            return; // whatever there is to return !!
        }

        foreach($order->OrderItems->OrderItem as $item){
            if(strtoupper((string)$item->CancelRequest) == 'TRUE'){
                // $OrderItemFromDBBestaat = OrderItem::where(['orderId' => Order::where('bolOrderNr', (string)$order->OrderId )->value('id'),
                //                                         'bolOrderItemId' => (string)$item->OrderItemId])->exists();
                $OrderItemFromDBBestaat = OrderItem::where(['orderId' => $de_order->id, 'bolOrderItemId' => (string)$item->OrderItemId])->exists();
                if($OrderItemFromDBBestaat){
                    // $OrderItemFromDB = OrderItem::where(['orderId' => Order::where('bolOrderNr', (string)$order->OrderId )->value('id'),
                    // 'bolOrderItemId' => (string)$item->OrderItemId])->first();
                    $OrderItemFromDB = OrderItem::where(['orderId' => $de_order->id, 'bolOrderItemId' => (string)$item->OrderItemId])->first();
                    dump('Deleting order item: ' . $OrderItemFromDB->bolOrderItemId  );
                    $OrderItemFromDB->delete();
                }
            } else {
                $OrderItemFromDBBestaatNiet = OrderItem::where(['orderId' => $de_order->id, 'bolOrderItemId' => (string)$item->OrderItemId])->doesntExist();
                if($OrderItemFromDBBestaatNiet){
                    $this->storeNewOrderItemInDB($item, Order::where('bolOrderNr', (string)$order->OrderId)->value('id'));
                }
            }
        }
        // checken of er nu uberhaupt nog wel orderitems aanwezig zijn voor deze order in de lokale DB
        // $lokaleOrderItems = Order::where('bolOrderNr', (string)$order->OrderId )->first()->orderItems;
        $lokaleOrderItems = $de_order->orderItems;
        $orderItemsCount = $de_order->orderItems()->count();

        dump('lokaleOrderItems zijn: ',  $lokaleOrderItems, 'aantal orderitems is nu: ' . $orderItemsCount );

        if($orderItemsCount != 0){
            return; // whatever there is to return !!
        }

        dump('Geen orderitems meer voor order: ' . $de_order->bolOrderNr . ' Deze order wordt verwijderd.');
        // eerst nog customerId ophalen van deze te deleten order, na delete is deze id niet meer beschikbaar..
        $het_customer_id = $de_order->customerId;
        $deOpOudeOrdersTeCheckenCustomer = Customer::find($het_customer_id);
        $de_order->delete();
        // zijn er nu nog (oude) orders bekend voor deze customer?
        $aantalOudeOrdersVanDezeCustomer = Order::where('customerId', $het_customer_id)->count();

        if($aantalOudeOrdersVanDezeCustomer == 0){
            dump('Klant ' . $deOpOudeOrdersTeCheckenCustomer->firstName . ' ' . $deOpOudeOrdersTeCheckenCustomer->lastName
                    . ' heeft geen eerdere bekende orders in de lokale DB. Deze klant wordt verwijderd.');
            $deOpOudeOrdersTeCheckenCustomer->delete();
        }

        return; // whatever there is to return !!
    }

    public function storeNewCustomerInDB(array $custData, bool $billingAddr, bool $shipmentAddr){
        $custData['hasBillingAddress'] =  $billingAddr; //  deze key toevoegen
        $custData['hasDeliveryAddress'] = $shipmentAddr;
        Customer::create($custData);
        dump('new customer created');
    }

    public function storeNewPostAddressInDB(array $postAdressData, $customerId){
        $postAdressData['customerId'] = $customerId;
        dump($postAdressData);
        PostAddress::create($postAdressData);
        dump('new post adress created');
    }

    public function storeNewBOLOrderInDB($custId, $bolOrderId){
        $newOrder = new Order();
        $newOrder->customerId = $custId;
        $newOrder->bolOrderNr = $bolOrderId;
        $newOrder->orderStatus = 'new';
        $newOrder->save();
        dump('Order created in DB: ' . $bolOrderId );
    }

    public function storeNewOrderItemInDB(\SimpleXMLElement $hetItem, $orderID){
        $newOrderItem = new OrderItem();
        $newOrderItem->orderId = $orderID;
        $newOrderItem->bolOrderItemId = (string)$hetItem->OrderItemId;
        $newOrderItem->qty = (int)$hetItem->Quantity;
        // customvariantid van de customvariant met het EAN uit het $bolBodyXMLObject
        $newOrderItem->customVariantId = CustomVariant::where('ean', (string)$hetItem->EAN)->value('id');
        $newOrderItem->latestDeliveryDate = (string)$hetItem->LatestDeliveryDate;
        $newOrderItem->save();
        dump('er is een order item aangemaakt');
    }

    public function checkAndLogBolErrorResponse($bol_response){
        $code = (string)$bol_response['bolstatuscode']; $firstNumber = \substr($code, 0, 1);
        if($bol_response['bolheaders']['Content-Type'] != 'application/xml'){
            return; // whatever there is to return !!
        }

        if( isset($bol_response['bolbody']) ){
            $bolBodyResp_AsObj = new \SimpleXMLElement($bol_response['bolbody']);
            $this->bolErrorBody = $bolBodyResp_AsObj->asXML();
        }

        switch($firstNumber){
            case '4':
                putContent('/client_errors.txt');
            break;

            case '5':
                putContent('/server_errors.txt');
            break;

            default:
                putContent('/other_errors.txt');
        }

        return; // whatever there is to return !!
    }

    public function putContent($fileName)
    {
        file_put_contents( storage_path( 'app/public') . $fileName, ($code . " " . $bol_response['bolreasonphrase']  . "\r\n\r\n"), FILE_APPEND );
        if($this->bolErrorBody != null){
            file_put_contents( storage_path( 'app/public') . $fileName, $this->bolErrorBody . "\r\n\r\n", FILE_APPEND );
            $this->bolErrorBody = null;
        }
        return; // whatever there is to return !!
    }

    // end code bart



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
    /****************/
    public $logFile = 'public/logs/message.txt';
    use SmakeApi;
    use DebugLog;

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

        $file = fopen( env('CONTENTFEED_PATH', '') . '/' .  $feed_file_name  . '.txt', 'w');
        fwrite($file, $feed_header . PHP_EOL);

        foreach($customVariantIds as $id) {
            $product_data = '';
            $shirt = CustomVariant::find($id);

            foreach($products as $product) {
                if(strpos($product->attrValues->attr_value, '->') != false) {
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

    public function getBolOrders() // array
    {
        $orders = [];
        // $orders = ......

        return $orders;
    }

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

