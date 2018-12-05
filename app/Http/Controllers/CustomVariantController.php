<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Traits\SmakeApi;
use App\CompositeMediaDesign;
use App\Order;
use App\OrderItem;
use App\Customer;
use App\Variant;
use App\CustomVariant;
use App\Attribute;
use App\Design;
use App\TshirtMetric;

class CustomVariantController extends Controller
{
    use SmakeApi;

    public function index() {
        $customVariants = CustomVariant::All();
        // dd($customVariants);
        return view('customVariants.index', compact('customVariants'));
    }

    public function createVariant(Request $request) {
        // ini_set("log_errors", 1);
        // ini_set("error_log", "logs/errors.log");
        $compositeMediaDesign = CompositeMediaDesign::find($request->compositeMediaId);
        if($compositeMediaDesign->smakeId === null){
            $uploadResult = $this->uploadCompositeMediaDesignToSmake($compositeMediaDesign);
            if($uploadResult == 'error'){
                \Session::flash("flash_message", "Er is iets fout gegaan met het uploaden van het 'Design', neem contact op met de systeembeheerder");
                return redirect()->route('variants.index');
            }
        }
        for ($i = 1; $i <= $request->numberOfSizes; $i++) {
            $currentEan = 'ean'.$i;
            $currentSize = 'Size'.$i;
            $currentParentVariantId = 'parentVariantId'.$i;
            if($request->has($currentEan)){
                $shirtLength = TshirtMetric::select('length_mm')->where('size', $request->$currentSize)->get()[0]->length_mm;
                $pixelSize = $shirtLength / 1325;
                $newCustomVariant = new CustomVariant();
                $newCustomVariant->parentVariantId = $request->$currentParentVariantId;
                $newCustomVariant->variantName = $compositeMediaDesign->designName;
                $newCustomVariant->ean = $request->$currentEan;
                $newCustomVariant->size = $request->$currentSize;
                $newCustomVariant->width_mm = round($compositeMediaDesign->width_px * $pixelSize, 2);
                $newCustomVariant->height_mm = round($compositeMediaDesign->height_px * $pixelSize, 2);
                $newCustomVariant->fileName = $compositeMediaDesign->fileName;
                $newCustomVariant->compositeMediaId = (int)$request->compositeMediaId;
                $newCustomVariant->productionMediaId = $compositeMediaDesign->designId;
                $newCustomVariant->smakeProductionMediaId = Design::select('smakeId')->find($newCustomVariant->productionMediaId)->get()[0]->smakeId;
                $newCustomVariant->smakeCompositeMediaId = CompositeMediaDesign::select('smakeId')->find($request->compositeMediaId)->get()[0]->smakeId;
                $uploadCustomVariantBody = $this->buildVariantObject($newCustomVariant);
                $newSmakeCustomVariant = $this->uploadCustomVariantToSmake($newCustomVariant, $uploadCustomVariantBody);
                $smakeId = $newSmakeCustomVariant->id;
                $newCustomVariant->smakeVariantId = $smakeId;
                $newCustomVariant->price = $newSmakeCustomVariant->price;
                $newCustomVariant->tax = $newSmakeCustomVariant->tax;
                $newCustomVariant->taxRate = $newSmakeCustomVariant->tax_rate;
                $newCustomVariant->total = $newSmakeCustomVariant->total;
                $newCustomVariant->save();
            }
        }
        return redirect()->route('customvariants.index');
    }

    public function buildVariantObject($newCustomVariant){
        $app = app();
        $dimensions = $app->make('stdClass');
        $dimensions->width = $newCustomVariant->width_mm;
        $dimensions->height = $newCustomVariant->height_mm;

        $customizations = $app->make('stdClass');
        $customizations->type = 'dtg';
        $customizations->production_media_id = $newCustomVariant->smakeProductionMediaId;
        $customizations->dimension = $dimensions;

        $front = $app->make('stdClass');
        $front->composite_media_id = $newCustomVariant->smakeCompositeMediaId;
        $front->customizations = [$customizations];

        $views = $app->make('stdClass');
        $views->front = $front;
        $newVariant = $app->make('stdClass');
        $newVariant->views = $views;

        return json_encode((array)$newVariant);
    }

    public function uploadCustomVariantToSmake($newCustomVariant, $uploadCustomVariantBody){
        // ini_set("log_errors", 1);
        // ini_set("error_log", "logs/errors.log");
        $parentVariant = $newCustomVariant->parentVariantId;
        $smakeVariantId = Variant::find($parentVariant);
        $url = 'variants/'.$smakeVariantId->variantId.'/design';
        $response = $this->UploadCustomVariant($uploadCustomVariantBody, $url);
        if ($response->getStatusCode() === 202) {    // reasonPhrase = "Accepted"
            $pollUrl = $response->getHeaders()['Location'][0];
            for($i = 0; $i < 100; $i++) {
                usleep(100000);
                $pollResult = $this->Poll($pollUrl);
                if($pollResult->getStatusCode() === 200){
                    $designedVariantId = json_decode($pollResult->getBody())->resource_url;
                    $smakeNewCustomVariant = json_decode($this->GetCustomVariant(substr(strrchr($designedVariantId, '/'), 1))->getBody());
                    break;
                }
            }
        } else {
            \Session::flash('flash_message', 'Er is iets fout gegaan met het versturen van de custom variant naar Smake, neem contact op met de systeembeheerder');
            return redirect()->route('variants.index');
        }
        return $smakeNewCustomVariant;
    }

    public function uploadCompositeMediaDesignToSmake($compositeMediaDesign) {
        // ini_set("log_errors", 1);
        // ini_set("error_log", "logs/errors.log");
        $status='';
        $path = env('COMPOSITE_MEDIA_PATH','');
        $fileSize = filesize($path.$compositeMediaDesign->fileName);
        $response = $this->UploadMedia($path, $compositeMediaDesign->fileName, $fileSize, 'media');

        if ($response->getStatusCode() === 201) {
            $compositeMediaResponse = json_decode($response->getBody());
            $compositeMediaDesign->smakeId = $compositeMediaResponse->id;
            $compositeMediaDesign->fileSize = $fileSize;
            $compositeMediaDesign->smakeFileName = $compositeMediaResponse->file_name;
            $compositeMediaDesign->smakeDownloadUrl = $compositeMediaResponse->download_url;
            $compositeMediaDesign->save();
        } else {
            $status = 'error';
        }
        return $status;
    }

    public function buildOrderObject($orderedItems) {
        $app = app();
        $shippingAddress = $app->make('stdClass');
        $shippingAddress->first_name = 'Barry';
        $shippingAddress->last_name = 'Bles';
        $shippingAddress->street1 = 'Ulenpasweg 2F4';
        $shippingAddress->zip = '7041 GB';
        $shippingAddress->city = "'s-Heerenberg";
        $shippingAddress->country_code = 'DE';
        $shippingAddress->province_code = 'GD';
        $shippingAddress->phone = '0314653130';
        $shippingAddress->email = 'info@internetsport.nl';
        $checkout = $app->make('stdClass');
        $checkout->email = 'info@internetsport.nl';
        $items = array();
        foreach($orderedItems as $item) {
            $itemObject = $app->make('stdClass');
            $smakeVariantId = CustomVariant::where('id', $item->variantId)->value('smakeVariantId');
            $itemObject->variant_id = $smakeVariantId;
            $itemObject->quantity = $item->qty;
            array_push($items, $itemObject);
        }
        $checkout->items = $items;
        $checkout->shipping_address = $shippingAddress;
        return json_encode((array)$checkout);
    }

    public function orderVariant($orderId) {
        $variantId = Order::where('id', $orderId)->value('id');
        $orderedItems = OrderItem::where('orderId', $variantId)->get();
        $path = env('CHECKOUT_PATH','');
        $checkoutBody = $this->buildOrderObject($orderedItems);
        $checkoutResponse = $this->CheckoutOrder($checkoutBody, $path);
        if ($checkoutResponse->getStatusCode() === 201) {
            $checkedoutOrder = json_decode($checkoutResponse->getBody());
            $order = Order::find($variantId);
            $order->smakeOrderId = $checkedoutOrder->id;
            $order->orderStatus = $checkedoutOrder->state;
            $order->orderAmount = $checkedoutOrder->subtotal;
            $order->totalTax = $checkedoutOrder->total_tax;
            $order->save();

            $url = 'checkouts/'.$checkedoutOrder->id.'/shipping-rates';
            $response = $this->getCheckout($url);
            if ($response->getStatusCode() === 202) {    // reasonPhrase = "Accepted"
                $pollUrl = $response->getHeaders()['Location'][0];
                for($i = 0; $i < 100; $i++) {
                    usleep(100000);
                    $pollResult = $this->Poll($pollUrl);
                    if($pollResult->getStatusCode() === 200){
                        // $designedVariantId = json_decode($pollResult->getBody())->resource_url;
                        // $smakeNewCustomVariant = json_decode($this->GetCustomVariant(substr(strrchr($designedVariantId, '/'), 1))->getBody());
                        dd($pollResult);
                        return json_decode($pollResult->getBody());
                    }
                }
            }
            dd($response);
            \Session::flash('flash_message', 'Er is iets fout gegaan met het versturen van de order naar Smake, neem contact op met de systeembeheerder');
            return redirect()->route('variants.index');

        }

/*      response ****
        {
            "id": 1,                                        -----> orders->smakeOrderId
            "is_test": true,
            "external_identifier": null,
            "project_name": null,
            "state": "incompleted",
            "customer_locale": "en",
            "currency": "EUR",
            "total": 123.75,
            "subtotal": 103.99,
            "total_tax": 19.76,
            "total_items_price": 123.75,
            "shipping_line": [],
            "created_at": "2017-09-29T09:14:11+00:00",
            "updated_at": "2017-09-29T09:14:11+00:00",
            "cancelled_at": null,
            "items": [
                {
                    "id": 75,
                    "quantity": 1,
                    "total": 123.75,
                    "price": 103.99,
                    "total_tax": 19.76,
                    "tax_rate": 19,
                    "variant": {
                        "id": 1,
                        "total": 123.75,
                        "price": 103.99,
                        "tax": 19.76,
                        "tax_rate": 19,
                        "attributes": [
                            {
                                "name": "color",
                                "value": "LightPink"
                            },
                            {
                                "name": "size",
                                "value": "M"
                            }
                        ],
                        "origin": {
                            "code": "2032095419441"
                        },
                        "media_id": 81,
                        "views": [],
                        "state": "finished",
                        "created_at": "2017-09-27T10:10:33+00:00",
                        "updated_at": "2017-09-27T10:10:34+00:00"
                    },
                    "created_at": "2017-09-29T09:14:11+00:00",
                    "updated_at": "2017-09-29T09:14:11+00:00",
                    "cancelled_at": null
                }
            ],
            "customer": {
                "id": 98,                                   -----> customers->smakeCustomerId
                "first_name": null,
                "last_name": null,
                "email": "customer@example.com",
                "phone": null,
                "addresses": [],
                "default_address": null,
                "created_at": "2017-09-29T09:14:11+00:00",
                "updated_at": "2017-09-29T09:14:11+00:00"
            },
            "shipping_address": {
                "id": 190,
                "default": true,
                "company": null,
                "first_name": "John",
                "last_name": "Doe",
                "city": "Anytown",
                "street1": "123 Main St",
                "street2": null,
                "street3": null,
                "zip": "12345",
                "phone": "12345 67890",
                "email": "shipping@example.com",
                "province_code": "NW",
                "country_code": "DE",
                "vat_in": null,
                "created_at": "2017-09-29T09:14:11+00:00",
                "updated_at": "2017-09-29T09:14:11+00:00"
            },
            "billing_address": {
                "id": 191,
                "default": true,
                "company": null,
                "first_name": "John",
                "last_name": "Doe",
                "city": "Anytown",
                "street1": "123 Main St",
                "street2": null,
                "street3": null,
                "zip": "12345",
                "phone": "12345 67890",
                "email": "shipping@example.com",
                "province_code": "NW",
                "country_code": "DE",
                "vat_in": null,
                "created_at": "2017-09-29T09:14:11+00:00",
                "updated_at": "2017-09-29T09:14:11+00:00"
            },
            "whitelabel_address": null,
            "transactions": [],
            "fulfillments": [],
            "id_tags": []
        }

        *************  GET /checkouts/1/shipping-rates

        Response ***
        HTTP/1.1 202 Accepted
        Location https://api.smake.io/jobs/1

        *** poll this location until 200 OK and following response

        HTTP/1.1 200 OK
        {
            "data": [
                {
                    "is_test": true,
                    "handle": "pickup",
                    "title": "Abholung",
                    "price": 22                             -----> orders->shippingRate  ?????????????????
                }
            ],
            "links": {
                "first": "/?page=1",
                "last": "/?page=1",
                "prev": null,
                "next": null
            },
            "meta": {
                "current_page": 1,
                "from": 1,
                "last_page": 1,
                "path": "/",
                "per_page": 15,
                "to": 1,
                "total": 1
            }
        }

        **** Update shipping_line

        PUT /checkouts/1
        {
        "shipping": {
            "handle": "versand"
        }

        ***** response 200 OK
        {
            ...
            "shipping_line": {
                "title": "Pickup",
                "price": 22.06,                             -----> orders->shippingRate   ?????????????????????
                "total": 26.25,
                "tax": 4.19
            },
            ...
        }

        **** Complete Checkout
        PUT /checkouts/1/complete

        {
            "payment": {
            "handle": "invoice",
            "amount": 150.0                                 -----> total price (without shipping costs???)
            }
        }
    }
*/











        return redirect()->route('customvariants.index');
    }

    public function addToCart()
    {
        //
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
