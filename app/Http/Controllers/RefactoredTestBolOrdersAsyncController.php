<?php

namespace App\Http\Controllers;

use App\Jobs\GetBolOrdersJob;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Http\Traits\BolApiV3;
use App\Http\Traits\SmakeApi;
use App\Http\Traits\DebugLog;
use App\CompositeMediaDesign;
use App\Design;
use App\Order;
use App\OrderItem;
use App\Customer;
use App\PostAddress;
use App\CustomVariant;
use function GuzzleHttp\json_decode;

// $this->log_json( ** $response Body **, 'checkoutBody', $checkoutBody);
// $this->log_var( ** $var **, $this->logFile);
// $this->log_DBrecord( ** DBrecord **, $this->logFile);

class RefactoredTestBolOrdersAsyncController extends Controller{

    public $logFile = 'public/logs/message.txt';

    use SmakeApi;
    use DebugLog;
    use BolAPiV3;

    private $bolErrorBody = null;
    private $erIsTenminsteEenOrderItemZonderCancelRequest = false;
    private $nullOrEmptyOrderIDsArePresent = [];
    private $nietBestaandeOrderIDSinOrderResponse = false;



    public function getOrdersFromBol(){



        $bolOrdersResponse = $this->make_V3_PlazaApiRequest('demo',  'orders?fulfilment-method=FBR', 'get');

        if($bolOrdersResponse['bolstatuscode'] != 200){     // checken op http-errors/codes
            $this->checkAndLogBolErrorResponse($bolOrdersResponse);
            return;
        }

        if($bolOrdersResponse['bolstatuscode'] == 200){
            // $order_resp_code = $bolOrdersResponse['bolstatuscode'];
            // $order_resp_phrase = $bolOrdersResponse['bolreasonphrase'];
            // $order_resp_body = $bolOrdersResponse['bolbody'];

            if( strpos( $bolOrdersResponse['bolheaders']['Content-Type'][0], 'json') === false ){

                return 'Geen JSON in bol-orders-response!';
            }

            $this->putResponseInFile("bolGetOrdersResponse-demo.txt", $bolOrdersResponse['bolstatuscode'],
                $bolOrdersResponse['bolreasonphrase'], $bolOrdersResponse['bolbody']);

            dump($bolOrdersResponse['bolbody']);

            $bolRespBodystdClassObject = json_decode($bolOrdersResponse['bolbody']);

            if(!isset($bolRespBodystdClassObject->orders) || !isset($bolRespBodystdClassObject->orders[0]->orderId)){

                dump( 'Geen openstaande orders vanuit BOL!', $bolRespBodystdClassObject);
                return 'Geen openstaande orders vanuit BOL!';
            }



            // checken of er orders aanwezig zijn, en of elke order in de order response een orderid heeft
            if( isset($bolRespBodystdClassObject->orders[0]->orderId)  ){   // OrderId isset = true bij String.Empty
                foreach($bolRespBodystdClassObject->orders as $order){
                    dump($order->orderId);
                    if( !isset($order->orderId) || ( isset( $order->orderId ) && (string)$order->orderId == '' ) ) {
                        \array_push( $this->nullOrEmptyOrderIDsArePresent, 'ispresent');
                    }
                }
                if(\in_array('ispresent', $this->nullOrEmptyOrderIDsArePresent)){
                    $this->nietBestaandeOrderIDSinOrderResponse = true;
                    dump($this->nullOrEmptyOrderIDsArePresent);
                }
                dump('Zijn er lege orderIds? : ' , \in_array('ispresent', $this->nullOrEmptyOrderIDsArePresent) ? 'Ja' : 'Nee');

            }

            if($this->nietBestaandeOrderIDSinOrderResponse){
                $this->checkAndLogBolErrorResponse($bolOrdersResponse);
                dump('Ongeldige of ontbrekende OrderIDs in Order response!');
                return;
            }

            // is er een order aanwezig in de resp-body en zijn alle orderid's niet null of String.Empty?
            if( isset($bolRespBodystdClassObject->orders[0]->orderId) && (string)$bolRespBodystdClassObject->orders[0]->orderId != '' &&  $this->nietBestaandeOrderIDSinOrderResponse == false){      // er is tenmiste 1 order, en alle orderid's zijn niet null of ''.
                dump("Er is tenminste 1 (bij bol openstaande) order aanwezig en alle orderid's zijn niet null of empty strings.");

                foreach($bolRespBodystdClassObject->orders as $order)
                {
                    $this->erIsTenminsteEenOrderItemZonderCancelRequest = false; // is er tenminste 1 orderitem met 'CancelRequest' = false   aanwezig?
                    $this->nullOrEmptyOrderIDsArePresent = [];
                    foreach ($order->orderItems as $item) {

                            if( $item->cancelRequest == false ){
                            $this->erIsTenminsteEenOrderItemZonderCancelRequest = true;
                            dump('Er is tenminste 1 order item zonder cancel request: item: '); dump($item->orderItemId);
                        }
                    }


                    GetBolOrdersJob::dispatch($order, 'demo');

                }
            }
        }
    }

    // public function newOrder(array $order)
    // {
    //     // nieuwe order aanmaken, zoeken naar of customer bekend is, zo niet nieuwe customer aanmaken, ook orderitems
    //     dump('Order bestaat nog niet in lokale DB.');
    //     dump('OrderId is hier: ' . $order['orderId']);



    //     dump('in newOrder functie. regel 130');
    //     dump($order);

    //     if($this->erIsTenminsteEenOrderItemZonderCancelRequest){
    //         $customerAdressDataFromBolOrderResp =   ['firstName' => $order["customerDetails"]["billingDetails"]["firstName"],
    //                                                 'lastName' => $order["customerDetails"]["billingDetails"]["surName"],
    //                                                 'postalCode' => $order["customerDetails"]["billingDetails"]["zipCode"],
    //                                                 'houseNr' => $order["customerDetails"]["billingDetails"]["houseNumber"],
    //                                                 // 'houseNrPostfix' => $order["customerDetails"]["billingDetails"]["houseNumberExtended"]
    //                                             ];


    //         $shipmentAdressDataFromBolOrderResp =   ['firstName' => $order["customerDetails"]["shipmentDetails"]["firstName"],
    //                                                 'lastName' => $order["customerDetails"]["shipmentDetails"]["surName"],
    //                                                 'postalCode' => $order["customerDetails"]["shipmentDetails"]["zipCode"],
    //                                                 'houseNr' => $order["customerDetails"]["shipmentDetails"]["houseNumber"],
    //                                                 // 'houseNrPostfix' => $order["customerDetails"]["shipmentDetails"]["houseNumberExtended"]
    //                                             ];


    //         $allCustomerDataFromBolOrderResp =      ['firstName' => $order["customerDetails"]["billingDetails"]["firstName"],
    //                                                 'lastName' => $order["customerDetails"]["billingDetails"]["surName"],
    //                                                 'street' => $order["customerDetails"]["billingDetails"]["streetName"],
    //                                                 'postalCode' => $order["customerDetails"]["billingDetails"]["zipCode"],
    //                                                 'houseNr' => $order["customerDetails"]["billingDetails"]["houseNumber"],
    //                                                 // 'houseNrPostfix' => $order["customerDetails"]["billingDetails"]["houseNumberExtended"],
    //                                                 'city' => $order["customerDetails"]["billingDetails"]["city"],
    //                                                 'countryCode' => $order["customerDetails"]["billingDetails"]["countryCode"],
    //                                                 'email' => $order["customerDetails"]["billingDetails"]["email"]  ];

    //         $allShipmentDataFromBolOrderResp =      ['firstName' => $order["customerDetails"]["shipmentDetails"]["firstName"],
    //                                                 'lastName' => $order["customerDetails"]["shipmentDetails"]["surName"],
    //                                                 'street' => $order["customerDetails"]["shipmentDetails"]["streetName"],
    //                                                 'postalCode' => $order["customerDetails"]["shipmentDetails"]["zipCode"],
    //                                                 'houseNr' => $order["customerDetails"]["shipmentDetails"]["houseNumber"],
    //                                                 // 'houseNrPostfix' => $order["customerDetails"]["shipmentDetails"]["houseNumberExtended"],
    //                                                 'city' => $order["customerDetails"]["shipmentDetails"]["city"],
    //                                                 'countryCode' => $order["customerDetails"]["shipmentDetails"]["countryCode"],
    //                                                 'email' => $order["customerDetails"]["shipmentDetails"]["email"] ];

    //             // als er geen huisnummer-extensie is, geef bol deze property niet mee, dus op controleren:
    //             if( isset($order["customerDetails"]["billingDetails"]["houseNumberExtended"]) ){
    //                 $customerAdressDataFromBolOrderResp['houseNrPostfix'] = $order["customerDetails"]["billingDetails"]["houseNumberExtended"];
    //                 $allCustomerDataFromBolOrderResp['houseNrPostfix'] = $order["customerDetails"]["billingDetails"]["houseNumberExtended"];
    //             }

    //             if( isset($order["customerDetails"]["shipmentDetails"]["houseNumberExtended"]) ){
    //                 $shipmentAdressDataFromBolOrderResp['houseNrPostfix'] = $order["customerDetails"]["shipmentDetails"]["houseNumberExtended"];
    //                 $allShipmentDataFromBolOrderResp['houseNrPostfix'] = $order["customerDetails"]["shipmentDetails"]["houseNumberExtended"];
    //             }

    //         $nogNietBestaandeCustomer = Customer::where($customerAdressDataFromBolOrderResp)->doesntExist();

    //         if(!$nogNietBestaandeCustomer){ // bestaande customer, dwz slechts bestaand in alleen de customer table

    //             $bestaandeCust = Customer::where($customerAdressDataFromBolOrderResp)->first();

    //             $geenShipmentAdresNu = $customerAdressDataFromBolOrderResp == $shipmentAdressDataFromBolOrderResp; //is er een shipment adres? true als geen
    //             if($geenShipmentAdresNu){
    //                 dump('Bestaande customer. Geen apart shipmentadres.');

    //                 // 1st checken of er een (oud) shipment adres in de lokale DB bekend is bij deze customer, zo ja verwijderen
    //                 // en met $bestaandeCust->update([ 'hasDeliveryAddress' => 0] ); het bestaande customer record updaten.
    //                 if($bestaandeCust->hasDeliveryAddress == 1){    // $bestaande customer heeft bestaand (oud) afwijkend deliveryadress
    //                     $postAdrr = PostAddress::where('customerId', $bestaandeCust->id)->first();
    //                     if($postAdrr->exists()){
    //                         $postAdrr->delete();
    //                         $bestaandeCust->update([ 'hasDeliveryAddress' => false] );
    //                     }
    //                 }

    //                 $this->storeNewBOLOrderInDB($bestaandeCust->id, (string)$order['orderId']);   // nu order aanmaken voor bekende customer zonder apart shipmentadr.

    //                 if(isset($order['orderItems'][0]['orderItemId'])){    // maak orderitems aan:   // hij komt hier niet! !!!!!!
    //                     dump('op regel 191 in code');
    //                     foreach($order['orderItems'] as $item){
    //                         if( $item['cancelRequest']  == false){
    //                              dump('op regel 194 in code') ;dump($item);
    //                             $this->storeNewOrderItemInDB($item,  Order::where('bolOrderNr', $order['orderId'])->value('id') );
    //                         }
    //                     }
    //                 }
    //             }

    //             if(!$geenShipmentAdresNu){   //  wel apart shipment adres nu
    //                 dump('Bestaande customer. Wel apart shipmentadres nu.');
    //                 // als er een bekend shipmentadr in lokale DB aanwezig is, deze deleten.
    //                 // Dan er weer een aanmaken met recente data uit deze orderresponse
    //                 if( PostAddress::where('customerId', $bestaandeCust->id)->exists() ){
    //                     PostAddress::where('customerId', $bestaandeCust->id)->delete();
    //                 }

    //                 $this->storeNewPostAddressInDB($allShipmentDataFromBolOrderResp, $bestaandeCust->id);
    //                 $this->storeNewBOLOrderInDB($bestaandeCust->id, (string)$order['orderId']);   // nu order aanmaken voor bekende customer met net geupdate shipment adres.

    //                 if( isset($order['orderItems'][0]['orderItemId']) ){        // maak orderitems aan:
    //                     foreach($order['orderItems'] as $item){
    //                         if( $item['cancelRequest'] == false){
    //                             $this->storeNewOrderItemInDB($item,  Order::where('bolOrderNr', $order['orderId'])->value('id') );
    //                         }
    //                     }
    //                 }
    //             }
    //         }

    //         if($nogNietBestaandeCustomer){
    //             $heeftGeenShipmentAdr = $customerAdressDataFromBolOrderResp == $shipmentAdressDataFromBolOrderResp;  // shipmentadr = billingadr
    //             dump('Shipment adres is het Billing adres: ' . ($heeftGeenShipmentAdr ? 'true' : 'false') );

    //             if(!$heeftGeenShipmentAdr){  // heeft wel ander shipment adres
    //                 $this->storeNewCustomerInDB($allCustomerDataFromBolOrderResp, true, true);
    //                 $this->storeNewPostAddressInDB( $allShipmentDataFromBolOrderResp, Customer::where($customerAdressDataFromBolOrderResp)->value('id') );
    //                 $this->storeNewBOLOrderInDB(Customer::where($customerAdressDataFromBolOrderResp, ['hasDeliveryAddress' => true])->value('id'), (string)$order['orderId']); // nog ff aanpassen!!

    //                 if( isset($order['orderItems'][0]['orderItemId']) ){
    //                     dump('in isset[orderitems]  regel 231'); //komt hier wel
    //                     foreach($order['orderItems'] as $item){
    //                         dump('in foreach regel 233. $item is:'); dump($item); // komt hier wel
    //                         if( $item['cancelRequest'] == false){      // hier zat de fout: string/bool!
    //                             $this->storeNewOrderItemInDB($item, Order::where('bolOrderNr', $order['orderId'])->value('id'));
    //                         }
    //                     }
    //                 }
    //             }

    //             if($heeftGeenShipmentAdr){
    //                 $this->storeNewCustomerInDB($allCustomerDataFromBolOrderResp, true, false);
    //                 $this->storeNewBOLOrderInDB(Customer::where($customerAdressDataFromBolOrderResp, ['hasDeliveryAddress' => false])->value('id'), (string)$order['orderId']);   // aanpassen    // nu order aanmaken in DB:

    //                 if( isset($order['orderItems'][0]['orderItemId']) ){        // nu orderitems aanmaken in DB:
    //                     foreach($order['orderItems'] as $item){
    //                         if( $item['cancelRequest'] == false){
    //                             $this->storeNewOrderItemInDB($item, Order::where('bolOrderNr', $order['orderId'])->value('id'));
    //                         }
    //                     }
    //                 }
    //             }
    //         }
    //         dump($this->erIsTenminsteEenOrderItemZonderCancelRequest);
    //     }
    //     return; // whatever there is to return !!
    // }


    // scenario: order bestaat reeds in lokale DB. Nu kan er een orderItem['cancelRequest'] op true staan, hier op checken
    // dit natuurlijk alleen bij orderItems van een order waarvan de status nog 'new' is.
    // in orderitems een kolom status: pending, failure success. in de orders response daarop checken en deze bolOrder(Item)State updaten aan de shipment/resource status van de bol status van het item
    // pas order naar smake als we van bol de shipment status vh orderitem als 'success' hebben geconfirmed
    // order komt binnen -> 5 min wachten, weer checken op cancellations -> dan shipment bevestigen aan bol -> shipment status confirmed/success van bol -> dan pas naar smake de order sturen



    // public function existingOrder(array $order)
    // {
    //     dump('Order bestaat reeds in DB');
    //     $de_order = Order::where('bolOrderNr', $order['orderId'] )->first();

    //     if(strtoupper($de_order->orderStatus) != 'NEW'){
    //         dump('Volgens BOL is deze order nog open. Order bestaat reeds in DB, maar order status is bij ons niet meer NEW');
    //         return; // whatever there is to return !!
    //     }

    //     if(strtoupper($de_order->orderStatus) == 'NEW'){   // dry nakijken
    //         // checken op Cancelrequest == 'true'
    //         foreach($order['orderItems'] as $item){
    //             if( $item['cancelRequest'] == true){
    //                 // $OrderItemFromDBBestaat = OrderItem::where(['orderId' => Order::where('bolOrderNr', (string)$order->OrderId )->value('id'),
    //                 //                                         'bolOrderItemId' => (string)$item->OrderItemId])->exists();
    //                 $OrderItemFromDBBestaat = OrderItem::where(['orderId' => $de_order->id, 'bolOrderItemId' => (string)$item['orderItemId'] ])->exists();
    //                 if($OrderItemFromDBBestaat){

    //                     $OrderItemFromDB = OrderItem::where(['orderId' => $de_order->id, 'bolOrderItemId' => (string)$item['orderItemId'] ])->first();
    //                     dump('Deleting order item: ' . $OrderItemFromDB->bolOrderItemId  );
    //                     $OrderItemFromDB->delete();
    //                 }
    //             }

    //             // nu nog voor case scenario: order bestaat in DB, maar orderitem nog niet, van cancelrequest=true naar false gezet
    //             if( $item['cancelRequest'] == false){
    //                 $OrderItemFromDBBestaatNiet = OrderItem::where(['orderId' => $de_order->id, 'bolOrderItemId' => (string)$item['orderItemId'] ])->doesntExist();
    //                 if($OrderItemFromDBBestaatNiet){

    //                     $this->storeNewOrderItemInDB($item, $de_order->id);
    //                 }
    //             }
    //         }
    //         // checken of er nu uberhaupt nog wel orderitems aanwezig zijn voor deze order in de lokale DB
    //         // $lokaleOrderItems = Order::where('bolOrderNr', (string)$order->OrderId )->first()->orderItems;
    //         $lokaleOrderItems = $de_order->orderItems;
    //         $orderItemsCount = $de_order->orderItems()->count();

    //         dump('lokaleOrderItems zijn: ',  $lokaleOrderItems, 'aantal orderitems is nu: ' . $orderItemsCount );

    //         if($orderItemsCount == 0){
    //             dump('Geen orderitems meer voor order: ' . $de_order->bolOrderNr . ' Deze order wordt verwijderd.');
    //             // eerst nog customerId ophalen van deze te deleten order, na delete is deze id niet meer beschikbaar..
    //             $het_customer_id = $de_order->customerId;
    //             $deOpOudeOrdersTeCheckenCustomer = Customer::find($het_customer_id);
    //             $de_order->delete();
    //             // zijn er nu nog (oude) orders bekend voor deze customer?
    //             $aantalOudeOrdersVanDezeCustomer = Order::where('customerId', $het_customer_id)->count();

    //             if($aantalOudeOrdersVanDezeCustomer == 0){
    //                 dump('Klant ' . $deOpOudeOrdersTeCheckenCustomer->firstName . ' ' . $deOpOudeOrdersTeCheckenCustomer->lastName
    //                         . ' heeft geen eerdere bekende orders in de lokale DB. Deze klant wordt verwijderd.');
    //                 $deOpOudeOrdersTeCheckenCustomer->delete();
    //             }
    //         }
    //     }
    //     return; // whatever there is to return !!
    // }

    // public function storeNewCustomerInDB(array $custData, bool $billingAddr, bool $shipmentAddr){
    //     $custData['hasBillingAddress'] =  $billingAddr; //  deze key toevoegen
    //     $custData['hasDeliveryAddress'] = $shipmentAddr;
    //     Customer::create($custData);
    //     dump('new customer created');
    // }

    // public function storeNewPostAddressInDB(array $postAdressData, $customerId){
    //     $postAdressData['customerId'] = $customerId;
    //     // dump($postAdressData);
    //     PostAddress::create($postAdressData);
    //     dump('new post adress created');
    // }

    // public function storeNewBOLOrderInDB($custId, $bolOrderId){
    //     $newOrder = new Order();
    //     $newOrder->customerId = $custId;
    //     $newOrder->bolOrderNr = $bolOrderId;
    //     $newOrder->orderStatus = 'new';
    //     $newOrder->save();
    //     dump('Order created in DB: ' . $bolOrderId );
    // }

    // public function storeNewOrderItemInDB(array $hetItem, $orderID){
    //     // dump('in storeNewOrderItemInDB, regel 349'); dump($hetItem);
    //     $newOrderItem = new OrderItem();
    //     $newOrderItem->orderId = $orderID;
    //     $newOrderItem->bolOrderItemId = $hetItem['orderItemId'];
    //     $newOrderItem->qty = (int)$hetItem['quantity'];
    //     // customvariantid van de customvariant met het EAN uit het $bolBodyXMLObject
    //     $newOrderItem->customVariantId = CustomVariant::where('ean', (string)$hetItem['ean'])->value('id');
    //     $newOrderItem->ean = $hetItem['ean'];
    //     $newOrderItem->latestDeliveryDate = (string)$hetItem['latestDeliveryDate'];
    //     $newOrderItem->save();
    //     dump('er is een order item aangemaakt');
    // }

    // public function checkAndLogBolErrorResponse($bol_response){
    //     $code = (string)$bol_response['bolstatuscode']; $firstNumber = \substr($code, 0, 1);

    //     // strpos($bol_outh_response->getHeaders()['Content-Type'][0], 'json') !== false
    //     if(strpos( $bol_response['bolheaders']['Content-Type'], 'json') ){
    //         if( isset($bol_response['bolbody']) ){

    //             $this->bolErrorBody = (string)$bol_response['bolbody'];
    //         }
    //         switch($firstNumber){
    //             case '4':
    //                 putContent('/client_errors.txt', $code, $bol_response['bolreasonphrase']);
    //             break;

    //             case '5':
    //                 putContent('/server_errors.txt', $code, $bol_response['bolreasonphrase']);
    //             break;

    //             default:
    //                 putContent('/other_errors.txt', $code, $bol_response['bolreasonphrase']);
    //         }
    //     }
    //     return; // whatever there is to return !!
    // }

    // public function putContent($fileName, $code, $phrase)
    // {
    //     file_put_contents( storage_path( 'app/public') . $fileName, ($code . " " . $phrase  . "\r\n\r\n"), FILE_APPEND );
    //     if($this->bolErrorBody != null){
    //         file_put_contents( storage_path( 'app/public') . $fileName, $this->bolErrorBody . "\r\n\r\n", FILE_APPEND );
    //         $this->bolErrorBody = null;
    //     }
    //     return; // whatever there is to return !!
    // }

    public function herstel_eerder_Aangemaakte_Smake_Customvarianten_Designs_en_Composite_media_Designs(){

        CustomVariant::truncate();
        CustomVariant::create([
                'variantId' => 8,
                'smakeVariantId' => 734415,
                'variantName' => 'meneer E.Hoorn',
                'ean' => '7435156898875',
                'size' => 'S',
                'price' => 6.30,
                'tax' => 1.20,
                'taxRate' => 19.00,
                'total' => 7.50,
                'fileName' => '15553260237e3cf0e56fccbef2117e7472b1571205.png',
                'baseColor' => 'White',
                'compositeMediaId' => 1,
                'smakeCompositeMediaId' => 5220525,
                'productionMediaId' => 1,
                'smakeProductionMediaId' => 5220523,
                'isInBolCatalog' => 0,
                'width_mm' => 293.2,
                'height_mm' => 256.6,
                'salePrice' => 40.00,
                // 'boltitle' => 'T-shirt met voetbal-logo',
                // 'referencecode' => '456.66',
                'boldeliverycode' => '3-5d',
                'boldescription' => 'Een leuk eekhoorn t-shirt']);

        CustomVariant::create([
                'variantId' => 100,
                'smakeVariantId' => 735436,
                'variantName' => 'pacman and friend',
                'ean' => '7435156898868',
                'size' => 'XL',
                'price' => 6.61,
                'tax' => 1.25,
                'taxRate' => 19.00,
                'total' => 7.86,
                'fileName' => '1555434716d481ae530b177e40acfe0fcc53923c4b.png',
                'baseColor' => 'Stone Blue',
                'compositeMediaId' => 2,
                'smakeCompositeMediaId' => 5226607,
                'smakeProductionMediaId' => 5226606,
                'isInBolCatalog' => 0,
                'productionMediaId' => 2,
                'width_mm' => 287.5,
                'height_mm' => 215.7,
                'salePrice' => 40.00,
                // 'boltitle' => 'T-shirt Jantje',
                // 'referencecode' => '456.71',
                'boldeliverycode' => '3-5d',
                'boldescription' => 'Een leuk pacman t-shirt']);

        CompositeMediaDesign::truncate();
        CompositeMediaDesign::create([
            'designName' => 'meneer E.Hoorn',
            'baseColor' => 'White',
            'designId' => 1,
            'smakeId' => 5220525,
            'fileName' => '15553260237e3cf0e56fccbef2117e7472b1571205.png',
            'fileFolder' => 'customVariants',
            'fileSize' => 914347,
            'smakeFileName' => 'cd452d734adc4d5d9840c1a92eb9c82e.png',
            'smakeDownloadUrl' => 'https://api.smake.io/v2/apps/10087/media/5220525/download',
            'width_px' => 464,
            'height_px' => 406
        ]);

        CompositeMediaDesign::create([
            'designName' => 'pacman and friend',
            'baseColor' => 'Stone Blue',
            'designId' => 2,
            'smakeId' => 5226607,
            'fileName' => '1555434716d481ae530b177e40acfe0fcc53923c4b.png',
            'fileFolder' => 'customVariants',
            'fileSize' => 1033921,
            'smakeFileName' => '41c9b045e02042c99a5f27a16880d5b2.png',
            'smakeDownloadUrl' => 'https://api.smake.io/v2/apps/10087/media/5226607/download',
            'width_px' => 411.0226163012337,
            'height_px' => 308.2669622259252
        ]);

        Design::truncate();
        Design::create([
            'smakeId' => 5220523,
            'smakeFileName' => '63ff29d6dbdb446b990a0d2071a85f67.jpeg',
            'fileName' => '1555325949f599f4d7def9aa0c1643f1f33b9b32ca.jpg',
            'originalName' => 'e.Hoorn.jpg',
            'mimeType' => 'image/jpeg',
            'fileSize' => '38904',
            'path' => 'C:\Users\skugga\BWD-master-25-03-2019\BWD\public\designImages',
            'downloadUrl' => 'https://api.smake.io/v2/apps/10087/media/5220523/download'
        ]);

        Design::create([
            'smakeId' => 5226606,
            'smakeFileName' => 'c61df32e143849e18f40c35995d67609.png',
            'fileName' => '1555434635bf842b5a9ac231c7f96d40c4f1130b9b.png',
            'originalName' => '1550062334114e2b0c9198812f84ca0348183e26a3.png',
            'mimeType' => 'image/png',
            'fileSize' => '205406',
            'path' => 'C:\Users\skugga\BWD-master-25-03-2019\BWD\public\designImages',
            'downloadUrl' => 'https://api.smake.io/v2/apps/10087/media/5226606/download'
        ]);
                dump('2 eerder aangemaakte customvariants, designs en composite_media_designs staan weer in DB!');
    }

    public function maak_Fake_Custom_Varianten_aan_voor_Test_met_Bol_Retailer_DEMO_SERVER(){

        CustomVariant::create([
            'variantId' => 100,
            'smakeVariantId' => 805436,
            'variantName' => 'star wars trilogy',
            'ean' => '8712626055150',
            'size' => 'XL',
            'price' => 6.61,
            'tax' => 1.25,
            'taxRate' => 19.00,
            'total' => 7.86,
            'fileName' => '1555434716d481ae530b177e40acfe0fcc53923c4b.png',
            'baseColor' => 'Stone Blue',
            'compositeMediaId' => 2,
            'smakeCompositeMediaId' => 5226607,
            'smakeProductionMediaId' => 5226606,
            'isInBolCatalog' => 0,
            'productionMediaId' => 2,
            'width_mm' => 287.5,
            'height_mm' => 215.7,
            'salePrice' => 40.00,
            // 'boltitle' => 'T-shirt Jantje',
            // 'referencecode' => '456.71',
            'boldeliverycode' => '3-5d',
            'boldescription' => 'trilogy van star wars']);

            CustomVariant::create([
                'variantId' => 100,
                'smakeVariantId' => 825436,
                'variantName' => 'bol demo dummy produktnaam 2',
                'ean' => '8712626055143',
                'size' => 'XL',
                'price' => 6.61,
                'tax' => 1.25,
                'taxRate' => 19.00,
                'total' => 7.89,
                'fileName' => '1555434716d481ae530b177e40acfe0fcc53923c4b.png',
                'baseColor' => 'Stone Blue',
                'compositeMediaId' => 2,
                'smakeCompositeMediaId' => 5226607,
                'smakeProductionMediaId' => 5226606,
                'isInBolCatalog' => 0,
                'productionMediaId' => 2,
                'width_mm' => 287.5,
                'height_mm' => 215.7,
                'salePrice' => 40.00,
                // 'boltitle' => 'T-shirt Jantje',
                // 'referencecode' => '456.71',
                'boldeliverycode' => '3-5d',
                'boldescription' => 'bol demo dummy produktnaam 2']);

                CustomVariant::create([
                    'variantId' => 100,
                    'smakeVariantId' => 845436,
                    'variantName' => 'bol demo dummy produktnaam 3',
                    'ean' => '8804269223123',
                    'size' => 'XL',
                    'price' => 6.61,
                    'tax' => 1.25,
                    'taxRate' => 19.00,
                    'total' => 7.89,
                    'fileName' => '1555434716d481ae530b177e40acfe0fcc53923c4b.png',
                    'baseColor' => 'Stone Blue',
                    'compositeMediaId' => 2,
                    'smakeCompositeMediaId' => 5226607,
                    'smakeProductionMediaId' => 5226606,
                    'isInBolCatalog' => 0,
                    'productionMediaId' => 2,
                    'width_mm' => 287.5,
                    'height_mm' => 215.7,
                    'salePrice' => 40.00,
                    // 'boltitle' => 'T-shirt Jantje',
                    // 'referencecode' => '456.71',
                    'boldeliverycode' => '3-5d',
                    'boldescription' => 'bol demo dummy produktnaam 3']);

                    CustomVariant::create([
                        'variantId' => 100,
                        'smakeVariantId' => 875436,
                        'variantName' => 'bol demo dummy produktnaam 3',
                        'ean' => '8718526069334',
                        'size' => 'XL',
                        'price' => 6.61,
                        'tax' => 1.25,
                        'taxRate' => 19.00,
                        'total' => 7.89,
                        'fileName' => '1555434716d481ae530b177e40acfe0fcc53923c4b.png',
                        'baseColor' => 'Stone Blue',
                        'compositeMediaId' => 2,
                        'smakeCompositeMediaId' => 5226607,
                        'smakeProductionMediaId' => 5226606,
                        'isInBolCatalog' => 0,
                        'productionMediaId' => 2,
                        'width_mm' => 287.5,
                        'height_mm' => 215.7,
                        'salePrice' => 40.00,
                        // 'boltitle' => 'T-shirt Jantje',
                        // 'referencecode' => '456.71',
                        'boldeliverycode' => '3-5d',
                        'boldescription' => 'bol demo dummy produktnaam 3']);

                        dump('Bol retailer-demo test produkten aangemaakt!');
    }

    // end code bart
}
