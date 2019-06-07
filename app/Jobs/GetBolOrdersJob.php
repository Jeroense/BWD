<?php

namespace App\Jobs;

use App\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Redis;
use App\Http\Traits\BolApiV3;
use App\Customer;
use App\PostAddress;
use App\OrderItem;
use App\CustomVariant;

class GetBolOrdersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, BolApiV3;

    protected $order;
    protected $servertype;
    private $bolErrorBody;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(\stdClass $order, string $servertype)
    {
        $this->order = $order;
        $this->servertype = $servertype;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {


        Redis::throttle('getbolorders')->allow(5)->every(1)->then(function () {   // 5 jobs/requests per 1 seconde
            // Job logic...

            $bol_single_order_resp = $this->make_V3_PlazaApiRequest($this->servertype,  "orders/{$this->order->orderId}", 'get');

            // valt op te maken dat bij de demo server 10 request per sec.(orders/*)


            if($bol_single_order_resp['bolstatuscode'] != 200)
            {

                dump('Status code op orders/{orderid} niet 200 !');
                $this->checkAndLogBolErrorResponse($bol_single_order_resp);
                $this->putResponseInFile("bol-get-orders-by-id-error-reponse-{$this->servertype}-voor-order-id-{$this->order->orderId}.txt",
                                        $bol_single_order_resp['bolstatuscode'],
                                        $bol_single_order_resp['bolreasonphrase'], $bol_single_order_resp['bolbody']
                                        );

                return 'http error-codes aanwezig';
            }

            if( strpos( $bol_single_order_resp['bolheaders']['Content-Type'][0], 'json') === false )
            {
                return 'Geen JSON in bol-single-order-response!';
            }

            // (tijdelijk) reponse naar file loggen
            $this->putResponseInFile("bolGetOrderResponseByID-{$this->servertype}.txt", $bol_single_order_resp['bolstatuscode'],
            $bol_single_order_resp['bolreasonphrase'], $bol_single_order_resp['bolbody']);

            // de response-body moet aanwezig zijn na een 200 OK. We doen deze request immers met de order(id)s, verkregen uit de request naar
            // GET retailer(-demo)/offers
            $reply_body_data = json_decode($bol_single_order_resp['bolbody'], true); // naar assoc_arr

            // empty()  ->  a variable is empty if it's undefined, null, false, 0 or an empty string.
            // empty() is the same as: !isset($var) || $var==false.

            // check of er de minimale billing & shipment gegevens in de order-by-order-id response aanwezig zijn
            if(
             // empty($reply_body_data["customerDetails"]["billingDetails"]["firstName"]) ||
                empty($reply_body_data["customerDetails"]["billingDetails"]["surName"] ) ||
                empty($reply_body_data["customerDetails"]["billingDetails"]["streetName"]) ||
                empty($reply_body_data["customerDetails"]["billingDetails"]["houseNumber"]) ||
                empty($reply_body_data["customerDetails"]["billingDetails"]["zipCode"]) ||
                empty($reply_body_data["customerDetails"]["billingDetails"]["city"]) ||
              // empty($reply_body_data["customerDetails"]["shipmentDetails"]["firstName"]) ||
                empty($reply_body_data["customerDetails"]["shipmentDetails"]["surName"] ) ||
                empty($reply_body_data["customerDetails"]["shipmentDetails"]["streetName"]) ||
                empty($reply_body_data["customerDetails"]["shipmentDetails"]["houseNumber"]) ||
                empty($reply_body_data["customerDetails"]["shipmentDetails"]["zipCode"]) ||
                empty($reply_body_data["customerDetails"]["shipmentDetails"]["city"])
            )
            {
                $this->putResponseInFile("bol-get-orders-by-id-error-reponse-MISSING_CUSTOMER-DETAILS-{$this->servertype}-voor-order-id-{$this->order->orderId}.txt",
                $bol_single_order_resp['bolstatuscode'],
                $bol_single_order_resp['bolreasonphrase'], $bol_single_order_resp['bolbody']);

                return 'Minimale customer details qua billing-gegevens en of shipment gegevens ontbreken!';
            }

            // een array is empty als er geen keys in bestaan


            if(!empty($reply_body_data['orderId']) && !empty( $reply_body_data["orderItems"]))
            {
                // dump('in GetBolOrdersJob class r. 111 !');


                Order::where('bolOrderNr', $reply_body_data['orderId'])->doesntExist() ? $this->newOrder($reply_body_data) : $this->existingOrder($reply_body_data);
            }

        }, function () {
            // Could not obtain lock...
            return $this->release(10);
        });


    }





    public function newOrder(array $order)
    {
        // nieuwe order aanmaken, zoeken naar of customer bekend is, zo niet nieuwe customer aanmaken, ook orderitems
        dump('Order bestaat nog niet in lokale DB.');
        dump('OrderId is hier: ' . $order['orderId']);



        dump('in GetBolOrdersJob@newOrder functie.');
        dump($order);


            $customerAdressDataFromBolOrderResp =   ['firstName' => isset($order["customerDetails"]["billingDetails"]["firstName"]) ? $order["customerDetails"]["billingDetails"]["firstName"] : '',
                                                    'lastName' => $order["customerDetails"]["billingDetails"]["surName"],
                                                    'postalCode' => $order["customerDetails"]["billingDetails"]["zipCode"],
                                                    'houseNr' => $order["customerDetails"]["billingDetails"]["houseNumber"],
                                                    // 'houseNrPostfix' => $order["customerDetails"]["billingDetails"]["houseNumberExtended"]
                                                    'city' => $order["customerDetails"]["billingDetails"]["city"],
                                                    'street' => $order["customerDetails"]["billingDetails"]["streetName"]
                                                ];


            $shipmentAdressDataFromBolOrderResp =   ['firstName' => isset($order["customerDetails"]["shipmentDetails"]["firstName"]) ? $order["customerDetails"]["shipmentDetails"]["firstName"] : '',
                                                    'lastName' => $order["customerDetails"]["shipmentDetails"]["surName"],
                                                    'postalCode' => $order["customerDetails"]["shipmentDetails"]["zipCode"],
                                                    'houseNr' => $order["customerDetails"]["shipmentDetails"]["houseNumber"],
                                                    // 'houseNrPostfix' => $order["customerDetails"]["shipmentDetails"]["houseNumberExtended"]
                                                    'city' => $order["customerDetails"]["shipmentDetails"]["city"],
                                                    'street' => $order["customerDetails"]["shipmentDetails"]["streetName"]
                                                ];

            $salutationStringCustomer = ''; $salutationStringShipment = '';

            switch($order["customerDetails"]["billingDetails"]["salutationCode"])
            {

                case '01':
                    $salutationStringCustomer = 'De heer';
                break;
                case '02':
                    $salutationStringCustomer = 'Mevrouw';
                break;
                case '03':
                    $salutationStringCustomer = 'De heer\Mevrouw';
                break;
                case null:
                    $salutationStringCustomer = 'De heer\Mevrouw';
                break;
                default: $salutationStringCustomer = 'De heer\Mevrouw';
            }

            switch($order["customerDetails"]["shipmentDetails"]["salutationCode"])
            {

                case '01':
                    $salutationStringShipment = 'De heer';
                break;
                case '02':
                    $salutationStringShipment = 'Mevrouw';
                break;
                case '03':
                    $salutationStringShipment = 'De heer\Mevrouw';
                break;
                case null:
                    $salutationStringShipment = 'De heer\Mevrouw';
                break;
                default: $salutationStringShipment = 'De heer\Mevrouw';
            }




            $allCustomerDataFromBolOrderResp =      ['salutation' => $salutationStringCustomer,
                                                    'firstName' => isset($order["customerDetails"]["billingDetails"]["firstName"]) ? $order["customerDetails"]["billingDetails"]["firstName"] : '',
                                                    'lastName' => $order["customerDetails"]["billingDetails"]["surName"],
                                                    'street' => $order["customerDetails"]["billingDetails"]["streetName"],
                                                    'postalCode' => $order["customerDetails"]["billingDetails"]["zipCode"],
                                                    'houseNr' => $order["customerDetails"]["billingDetails"]["houseNumber"],
                                                    // 'houseNrPostfix' => $order["customerDetails"]["billingDetails"]["houseNumberExtended"],
                                                    'city' => $order["customerDetails"]["billingDetails"]["city"],
                                                    'countryCode' => isset($order["customerDetails"]["billingDetails"]["countryCode"]) ? $order["customerDetails"]["billingDetails"]["countryCode"] : null,
                                                    'email' => isset($order["customerDetails"]["billingDetails"]["email"]) ? $order["customerDetails"]["billingDetails"]["email"] : null
                                                   ];

            $allShipmentDataFromBolOrderResp =      ['salutation' => $salutationStringShipment,
                                                    'firstName' => isset($order["customerDetails"]["shipmentDetails"]["firstName"]) ? $order["customerDetails"]["shipmentDetails"]["firstName"] : '',
                                                    'lastName' => $order["customerDetails"]["shipmentDetails"]["surName"],
                                                    'street' => $order["customerDetails"]["shipmentDetails"]["streetName"],
                                                    'postalCode' => $order["customerDetails"]["shipmentDetails"]["zipCode"],
                                                    'houseNr' => $order["customerDetails"]["shipmentDetails"]["houseNumber"],
                                                    // 'houseNrPostfix' => $order["customerDetails"]["shipmentDetails"]["houseNumberExtended"],
                                                    'city' => $order["customerDetails"]["shipmentDetails"]["city"],
                                                    'countryCode' => isset($order["customerDetails"]["shipmentDetails"]["countryCode"]) ? $order["customerDetails"]["shipmentDetails"]["countryCode"] : null,
                                                    'email' => isset($order["customerDetails"]["shipmentDetails"]["email"]) ? $order["customerDetails"]["shipmentDetails"]["email"] : null
                                                 ];

                // als er geen huisnummer-extensie is, geef bol deze property niet mee, dus op controleren:
                if( isset($order["customerDetails"]["billingDetails"]["houseNumberExtended"]) )
                {
                    $customerAdressDataFromBolOrderResp['houseNrPostfix'] = $order["customerDetails"]["billingDetails"]["houseNumberExtended"];
                    $allCustomerDataFromBolOrderResp['houseNrPostfix'] = $order["customerDetails"]["billingDetails"]["houseNumberExtended"];
                }

                if( isset($order["customerDetails"]["shipmentDetails"]["houseNumberExtended"]) )
                {
                    $shipmentAdressDataFromBolOrderResp['houseNrPostfix'] = $order["customerDetails"]["shipmentDetails"]["houseNumberExtended"];
                    $allShipmentDataFromBolOrderResp['houseNrPostfix'] = $order["customerDetails"]["shipmentDetails"]["houseNumberExtended"];
                }

            $nogNietBestaandeCustomer = Customer::where($customerAdressDataFromBolOrderResp)->doesntExist();

            if(!$nogNietBestaandeCustomer)      // bestaande customer, dwz slechts bestaand in alleen de customer table
            {

                $bestaandeCust = Customer::where($customerAdressDataFromBolOrderResp)->first();

                $geenShipmentAdresNu = $customerAdressDataFromBolOrderResp == $shipmentAdressDataFromBolOrderResp; //is er een shipment adres? true als geen
                if($geenShipmentAdresNu){
                    dump('Bestaande customer. Geen apart shipmentadres.');

                    // 1st checken of er een (oud) shipment adres in de lokale DB bekend is bij deze customer, zo ja verwijderen
                    // en met $bestaandeCust->update([ 'hasDeliveryAddress' => 0] ); het bestaande customer record updaten.
                    if($bestaandeCust->hasDeliveryAddress == 1)
                    {    // $bestaande customer heeft bestaand (oud) afwijkend deliveryadress

                        $postAdrr = PostAddress::where('customerId', $bestaandeCust->id)->first();

                        if($postAdrr->exists())
                        {
                            $postAdrr->delete();
                            $bestaandeCust->update([ 'hasDeliveryAddress' => false] );
                        }
                    }

                    // $this->storeNewBOLOrderInDB($bestaandeCust->id, (string)$order['orderId']);   // nu order aanmaken voor bekende customer zonder apart shipmentadr.
                    $this->storeNewBOLOrderInDB($bestaandeCust->id, $order);

                    if(!empty($order['orderItems'][0]['orderItemId']))
                    {
                        dump('in GetBolOrdersJob@newOrder if(!empty($order[orderItems][0][orderItemId]))');

                        foreach($order['orderItems'] as $item)
                        {
                            if( !empty($item['orderItemId']) && $item['cancelRequest']  == false)
                            {
                                 dump('rond regel 270 in code in GetBolOrdersJob');

                                 dump($item);

                                $this->storeNewOrderItemInDB($item,  Order::where('bolOrderNr', $order['orderId'])->value('id') );
                            }
                        }
                    }
                }

                if(!$geenShipmentAdresNu)       //  wel apart shipment adres nu
                {
                    dump('Bestaande customer. Wel apart shipmentadres nu.');
                    // als er een bekend shipmentadr in lokale DB aanwezig is, deze deleten.
                    // Dan er weer een aanmaken met recente data uit deze orderresponse
                    if( PostAddress::where('customerId', $bestaandeCust->id)->exists() )
                    {
                        PostAddress::where('customerId', $bestaandeCust->id)->delete();
                    }

                    $this->storeNewPostAddressInDB($allShipmentDataFromBolOrderResp, $bestaandeCust->id);
                    $this->storeNewBOLOrderInDB($bestaandeCust->id, $order);   // nu order aanmaken voor bekende customer met net geupdate shipment adres.

                    if( isset($order['orderItems'][0]['orderItemId']) )     // maak orderitems aan:
                    {
                        foreach($order['orderItems'] as $item)
                        {
                            if( !empty($item['orderItemId']) && $item['cancelRequest'] == false)
                            {
                                $this->storeNewOrderItemInDB($item,  Order::where('bolOrderNr', $order['orderId'])->value('id') );
                            }
                        }
                    }
                }
            }

            if($nogNietBestaandeCustomer)
            {
                $heeftGeenShipmentAdr = $customerAdressDataFromBolOrderResp == $shipmentAdressDataFromBolOrderResp;  // shipmentadr = billingadr
                dump('Shipment adres is het Billing adres: ' . ($heeftGeenShipmentAdr ? 'true' : 'false') );

                if(!$heeftGeenShipmentAdr)  // heeft wel ander shipment adres
                {
                    $this->storeNewCustomerInDB($allCustomerDataFromBolOrderResp, true, true);
                    $this->storeNewPostAddressInDB( $allShipmentDataFromBolOrderResp, Customer::where($customerAdressDataFromBolOrderResp)->value('id') );
                    $this->storeNewBOLOrderInDB(Customer::where($customerAdressDataFromBolOrderResp, ['hasDeliveryAddress' => true])->value('id'), $order);

                    if( isset($order['orderItems'][0]['orderItemId']) )
                    {
                        dump('in isset[orderitems]  regel 322'); //komt hier wel
                        foreach($order['orderItems'] as $item)
                        {
                            dump('in foreach regel 330. $item is:'); // komt hier wel

                            dump($item);

                            if(!empty($item['orderItemId']) && $item['cancelRequest'] == false)
                            {
                                $this->storeNewOrderItemInDB($item, Order::where('bolOrderNr', $order['orderId'])->value('id'));
                            }
                        }
                    }
                }

                if($heeftGeenShipmentAdr)
                {
                    $this->storeNewCustomerInDB($allCustomerDataFromBolOrderResp, true, false);

                    $this->storeNewBOLOrderInDB(Customer::where($customerAdressDataFromBolOrderResp, ['hasDeliveryAddress' => false])->value('id'), $order);   // aanpassen    // nu order aanmaken in DB:

                    if( isset($order['orderItems'][0]['orderItemId']) )  // nu orderitems aanmaken in DB:
                    {
                        foreach($order['orderItems'] as $item)
                        {
                            if( !empty($item['orderItemId']) && $item['cancelRequest'] == false)
                            {
                                $this->storeNewOrderItemInDB($item, Order::where('bolOrderNr', $order['orderId'])->value('id'));
                            }
                        }
                    }
                }
            }


        return;
    }


    // scenario: order bestaat reeds in lokale DB. Nu kan er een orderItem['cancelRequest'] op true staan, hier op checken
    // dit natuurlijk alleen bij orderItems van een order waarvan de status nog 'new' is.

    // in orderitems een kolom status: pending, failure success. in de orders response daarop checken ??
    // en deze bolOrder(Item)State updaten aan de shipment/resource status van de bol status van het item ??
    // pas order naar smake als we van bol de shipment status vh orderitem als 'success' hebben geconfirmed
    // order komt binnen -> 5 min wachten, weer checken op cancellations -> dan shipment bevestigen aan bol -> shipment status confirmed/success van bol -> dan pas naar smake de order sturen
    public function existingOrder(array $order)
    {
        dump('Order bestaat reeds in DB');
        $de_order = Order::where('bolOrderNr', $order['orderId'] )->first();

        if(strtoupper($de_order->orderStatus) != 'NEW')
        {
            dump('Volgens BOL is deze order nog open. Order bestaat reeds in DB, maar order status is bij ons niet meer NEW');
            return;
        }

        if(strtoupper($de_order->orderStatus) == 'NEW')  // is dubbel op ja ik weet 't
        {

            // een check voor het geval, dat er wel een bestaande order in lokale db staat, maar dat de klant om eoa reden er niet meer in staat?
            // dit is wellicht overbodig, maar voor de zekerheid toch maar
            $customer = Customer::find($de_order->customerId);

            if($customer == null)
            {
                $customerAdressDataFromBolOrderResp =   ['firstName' => isset($order["customerDetails"]["billingDetails"]["firstName"]) ? $order["customerDetails"]["billingDetails"]["firstName"] : '',
                                                        'lastName' => $order["customerDetails"]["billingDetails"]["surName"],
                                                        'postalCode' => $order["customerDetails"]["billingDetails"]["zipCode"],
                                                        'houseNr' => $order["customerDetails"]["billingDetails"]["houseNumber"],
                                                        // 'houseNrPostfix' => $order["customerDetails"]["billingDetails"]["houseNumberExtended"]
                                                        'city' => $order["customerDetails"]["billingDetails"]["city"],
                                                        'street' => $order["customerDetails"]["billingDetails"]["streetName"]
                                                        ];


                $shipmentAdressDataFromBolOrderResp =   ['firstName' => isset($order["customerDetails"]["shipmentDetails"]["firstName"]) ? $order["customerDetails"]["shipmentDetails"]["firstName"] : '',
                                                        'lastName' => $order["customerDetails"]["shipmentDetails"]["surName"],
                                                        'postalCode' => $order["customerDetails"]["shipmentDetails"]["zipCode"],
                                                        'houseNr' => $order["customerDetails"]["shipmentDetails"]["houseNumber"],
                                                     // 'houseNrPostfix' => $order["customerDetails"]["shipmentDetails"]["houseNumberExtended"]
                                                        'city' => $order["customerDetails"]["shipmentDetails"]["city"],
                                                        'street' => $order["customerDetails"]["shipmentDetails"]["streetName"]
                                                        ];

                $salutationStringCustomer = ''; $salutationStringShipment = '';

                    switch($order["customerDetails"]["billingDetails"]["salutationCode"])
                    {

                        case '01':
                        $salutationStringCustomer = 'De heer';
                        break;
                        case '02':
                        $salutationStringCustomer = 'Mevrouw';
                        break;
                        case '03':
                        $salutationStringCustomer = 'De heer\Mevrouw';
                        break;
                        case null:
                        $salutationStringCustomer = 'De heer\Mevrouw';
                        break;
                        default: $salutationStringCustomer = 'De heer\Mevrouw';
                    }

                    switch($order["customerDetails"]["shipmentDetails"]["salutationCode"])
                    {

                        case '01':
                        $salutationStringShipment = 'De heer';
                        break;
                        case '02':
                        $salutationStringShipment = 'Mevrouw';
                        break;
                        case '03':
                        $salutationStringShipment = 'De heer\Mevrouw';
                        break;
                        case null:
                        $salutationStringShipment = 'De heer\Mevrouw';
                        break;
                        default: $salutationStringShipment = 'De heer\Mevrouw';
                    }




                    $allCustomerDataFromBolOrderResp =      ['salutation' => $salutationStringCustomer,
                                                            'firstName' => isset($order["customerDetails"]["billingDetails"]["firstName"]) ? $order["customerDetails"]["billingDetails"]["firstName"] : '',
                                                            'lastName' => $order["customerDetails"]["billingDetails"]["surName"],
                                                            'street' => $order["customerDetails"]["billingDetails"]["streetName"],
                                                            'postalCode' => $order["customerDetails"]["billingDetails"]["zipCode"],
                                                            'houseNr' => $order["customerDetails"]["billingDetails"]["houseNumber"],
                                                            // 'houseNrPostfix' => $order["customerDetails"]["billingDetails"]["houseNumberExtended"],
                                                            'city' => $order["customerDetails"]["billingDetails"]["city"],
                                                            'countryCode' => isset($order["customerDetails"]["billingDetails"]["countryCode"]) ? $order["customerDetails"]["billingDetails"]["countryCode"] : null,
                                                            'email' => isset($order["customerDetails"]["billingDetails"]["email"]) ? $order["customerDetails"]["billingDetails"]["email"] : null
                                                            ];

                    $allShipmentDataFromBolOrderResp =      ['salutation' => $salutationStringShipment,
                                                        'firstName' => isset($order["customerDetails"]["shipmentDetails"]["firstName"]) ? $order["customerDetails"]["shipmentDetails"]["firstName"] : '',
                                                        'lastName' => $order["customerDetails"]["shipmentDetails"]["surName"],
                                                        'street' => $order["customerDetails"]["shipmentDetails"]["streetName"],
                                                        'postalCode' => $order["customerDetails"]["shipmentDetails"]["zipCode"],
                                                        'houseNr' => $order["customerDetails"]["shipmentDetails"]["houseNumber"],
                                                        // 'houseNrPostfix' => $order["customerDetails"]["shipmentDetails"]["houseNumberExtended"],
                                                        'city' => $order["customerDetails"]["shipmentDetails"]["city"],
                                                        'countryCode' => isset($order["customerDetails"]["shipmentDetails"]["countryCode"]) ? $order["customerDetails"]["shipmentDetails"]["countryCode"] : null,
                                                        'email' => isset($order["customerDetails"]["shipmentDetails"]["email"]) ? $order["customerDetails"]["shipmentDetails"]["email"] : null
                                                            ];

                    // als er geen huisnummer-extensie is, geef bol deze property niet mee, dus op controleren:
                    if( isset($order["customerDetails"]["billingDetails"]["houseNumberExtended"]) )
                    {
                        $customerAdressDataFromBolOrderResp['houseNrPostfix'] = $order["customerDetails"]["billingDetails"]["houseNumberExtended"];
                        $allCustomerDataFromBolOrderResp['houseNrPostfix'] = $order["customerDetails"]["billingDetails"]["houseNumberExtended"];
                    }

                    if( isset($order["customerDetails"]["shipmentDetails"]["houseNumberExtended"]) )
                    {
                        $shipmentAdressDataFromBolOrderResp['houseNrPostfix'] = $order["customerDetails"]["shipmentDetails"]["houseNumberExtended"];
                        $allShipmentDataFromBolOrderResp['houseNrPostfix'] = $order["customerDetails"]["shipmentDetails"]["houseNumberExtended"];
                    }

                    $geenShipmentAdresNu = $customerAdressDataFromBolOrderResp == $shipmentAdressDataFromBolOrderResp; //is er een shipment adres? true als geen

                    if($geenShipmentAdresNu){

                        $this->storeNewCustomerInDB( $allCustomerDataFromBolOrderResp, true, false);

                    }
                    if(!$geenShipmentAdresNu)
                    {
                        $this->storeNewCustomerInDB( $allCustomerDataFromBolOrderResp, true, true);
                        $this->storeNewPostAddressInDB( $allShipmentDataFromBolOrderResp, Customer::where($customerAdressDataFromBolOrderResp)->value('id') );
                    }

                    $de_order->update(['customerId' => Customer::where($customerAdressDataFromBolOrderResp)->value('id') ]);

                    dump('Order bestond wel in lokale DB, maar customer niet meer. Nieuwe customer aangemaakt, en bestaande order ge-update met info uit bol-order-response.');
            }
            //---end check bij existing order in lokale db en om een of andere reden geen bestaande klant meer in lokale db(dit zou normaliter niet moeten voorkomen, maar goed)--------


            // checken op Cancelrequest == 'true'
            foreach($order['orderItems'] as $item)
            {
                if( $item['cancelRequest'] == true)
                {
                    // $OrderItemFromDBBestaat = OrderItem::where(['orderId' => Order::where('bolOrderNr', (string)$order->OrderId )->value('id'),
                    //                                         'bolOrderItemId' => (string)$item->OrderItemId])->exists();
                    $OrderItemFromDBBestaat = OrderItem::where(['orderId' => $de_order->id, 'bolOrderItemId' => (string)$item['orderItemId'] ])->exists();

                    if($OrderItemFromDBBestaat)
                    {

                        $OrderItemFromDB = OrderItem::where(['orderId' => $de_order->id, 'bolOrderItemId' => (string)$item['orderItemId'] ])->first();
                        dump('Deleting order item: ' . $OrderItemFromDB->bolOrderItemId  );
                        $OrderItemFromDB->delete();
                    }
                }

                // nu nog voor case scenario: order bestaat in DB, maar orderitem nog niet, van cancelrequest=true naar false gezet
                if( $item['cancelRequest'] == false)
                {
                    $OrderItemFromDBBestaatNiet = OrderItem::where(['orderId' => $de_order->id, 'bolOrderItemId' => (string)$item['orderItemId'] ])->doesntExist();

                    if($OrderItemFromDBBestaatNiet)
                    {

                        $this->storeNewOrderItemInDB($item, $de_order->id);
                    }
                }
            }
            // checken of er nu uberhaupt nog wel orderitems aanwezig zijn voor deze order in de lokale DB
            // $lokaleOrderItems = Order::where('bolOrderNr', (string)$order->OrderId )->first()->orderItems;
            $lokaleOrderItems = $de_order->orderItems;
            $orderItemsCount = $de_order->orderItems()->count();

            dump('lokaleOrderItems zijn: ',  $lokaleOrderItems, 'aantal orderitems is nu: ' . $orderItemsCount );

            if($orderItemsCount == 0)
            {
                dump('Geen orderitems meer voor order: ' . $de_order->bolOrderNr . ' Deze order wordt verwijderd.');
                // eerst nog customerId ophalen van deze te deleten order, na delete is deze id niet meer beschikbaar..
                $het_customer_id = $de_order->customerId;
                $deOpOudeOrdersTeCheckenCustomer = Customer::find($het_customer_id);
                $de_order->delete();
                // zijn er nu nog (oude) orders bekend voor deze customer?
                $aantalOudeOrdersVanDezeCustomer = Order::where('customerId', $het_customer_id)->count();

                if($aantalOudeOrdersVanDezeCustomer == 0)
                {
                    dump('Klant ' . $deOpOudeOrdersTeCheckenCustomer->firstName . ' ' . $deOpOudeOrdersTeCheckenCustomer->lastName
                            . ' heeft geen eerdere bekende orders in de lokale DB. Deze klant wordt verwijderd.');
                    $deOpOudeOrdersTeCheckenCustomer->delete();
                }
            }
        }
        return;
    }

    public function storeNewCustomerInDB(array $custData, bool $billingAddr, bool $shipmentAddr)
    {
        $custData['hasBillingAddress'] =  $billingAddr; //  deze key toevoegen
        $custData['hasDeliveryAddress'] = $shipmentAddr;
        Customer::create($custData);
        dump('new customer created');
    }

    public function storeNewPostAddressInDB(array $postAdressData, $customerId)
    {
        $postAdressData['customerId'] = $customerId;
        // dump($postAdressData);
        PostAddress::create($postAdressData);
        dump('new post adress created');
    }

    // public function storeNewBOLOrderInDB($custId, $bolOrderId)
    public function storeNewBOLOrderInDB($custId, $bolOrder)
    {
        $newOrder = new Order();
        $newOrder->customerId = $custId;
        $newOrder->bolOrderNr = $bolOrder['orderId'];
        $newOrder->orderStatus = 'new';

        // ivm totale bolsaleprice en transactionfee van alle geldige orderitems van deze order
        $offeritems_total_transaction_fee_for_order = 0.00;
        $offeritems_total_price_for_order = 0.00;
        foreach($bolOrder['orderItems'] as $item)
        {
            if(!empty($item['offerPrice']) && $item['cancelRequest'] == false)
            {
                $offeritems_total_price_for_order += $item['offerPrice'];
            }
            if(!empty($item['transactionFee']) && $item['cancelRequest'] == false)
            {
                $offeritems_total_transaction_fee_for_order += $item['transactionFee'];
            }
        }

        $newOrder->orderAmount = $offeritems_total_price_for_order;
        $newOrder->boltransactionFee = $offeritems_total_transaction_fee_for_order;

        $newOrder->save();

        dump('Order created in DB: ' . $bolOrder['orderId'] );
    }

    public function storeNewOrderItemInDB(array $hetItem, $orderID)
    {
        dump('in GetBolOrdersJob@storeNewOrderItemInDB'); dump($hetItem);
        $newOrderItem = new OrderItem();
        $newOrderItem->orderId = $orderID;
        $newOrderItem->bolOrderItemId = $hetItem['orderItemId'];
        $newOrderItem->qty = (int)$hetItem['quantity'];
        $newOrderItem->bolTotalOfferPrice = !empty( $hetItem['offerPrice']) ? $hetItem['offerPrice'] : null;
        $newOrderItem->bolTransactionFee = !empty( $hetItem['transactionFee']) ? $hetItem['transactionFee'] : null;
        $newOrderItem->customVariantId = CustomVariant::where('ean', (string)$hetItem['ean'])->value('id');
        $newOrderItem->ean = $hetItem['ean'];
        $newOrderItem->latestDeliveryDate = (string)$hetItem['latestDeliveryDate'];

        $newOrderItem->save();
        dump('er is een order item aangemaakt');
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
                    $this->putContent('/client_errors_fromGetBolOrdersJob.txt', $code, $bol_response['bolreasonphrase']);
                break;

                case '5':
                    $this->putContent('/server_errors_fromGetBolOrdersJob.txt', $code, $bol_response['bolreasonphrase']);
                break;

                default:
                    $this->putContent('/other_errors_fromGetBolOrdersJob.txt', $code, $bol_response['bolreasonphrase']);
            }
        }
        return;
    }


    public function putContent($fileName, $code, $phrase)
    {
        file_put_contents( storage_path( 'app/public') . $fileName, ( (string)date('D, d M Y H:i:s') . "\r\n" .  $code . " " . $phrase  . "\r\n\r\n"), FILE_APPEND );

        if($this->bolErrorBody != null)
        {
            file_put_contents( storage_path( 'app/public') . $fileName, $this->bolErrorBody . "\r\n\r\n", FILE_APPEND );
            $this->bolErrorBody = null;
        }
        return;
    }

}
