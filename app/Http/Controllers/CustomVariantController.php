<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Traits\SmakeApi;
use App\Http\Traits\DebugLog;
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
    use DebugLog;




    public function index() {
        if (env('APP_DEBUG') == true) {
            $this->log_item('*** key', 'debug = false');
        }
        $customVariants = CustomVariant::All();
        return view('customVariants.index', compact('customVariants'));
    }

    public function createVariant(Request $request) {

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
                $pixelSize = $shirtLength / 1125;  //was 1325 at first test order
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
                $newCustomVariant->smakeProductionMediaId = Design::select('smakeId')->where('id', $newCustomVariant->productionMediaId)->first()->smakeId;
                $newCustomVariant->smakeCompositeMediaId = CompositeMediaDesign::select('smakeId')->where('id' ,$request->compositeMediaId)->first()->smakeId;
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
        $parentVariant = $newCustomVariant->parentVariantId;
        $smakeVariantId = Variant::find($parentVariant);
        $url = 'variants/'.$smakeVariantId->variantId.'/design';
        $response = $this->PostSmakeData($uploadCustomVariantBody, $url);
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
        $shippingAddress->country_code = 'NL';
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
        $checkoutBody = $this->buildOrderObject($orderedItems);  // build order JSON Object
        $checkoutResponse = $this->CheckoutOrder($checkoutBody, $path);  // Send order to Smake
        //*** Debug only
                $command = $checkoutBody.' '.$path;
                $this->log_response($command, 'checkoutOrder', $checkoutResponse);  // End debug

        if ($checkoutResponse->getStatusCode() != 201) {  // check if order is accepted and get the data
            \Session::flash('flash_message', 'Er is iets fout gegaan met het versturen van de order naar Smake, neem contact op met de systeembeheerder');
                return redirect()->route('variants.index');
        }
        $thisOrder = json_decode($checkoutResponse->getBody());
        $order = Order::find($variantId);  // update database
        $order->smakeOrderId = $thisOrder->id;
        $order->orderStatus = $thisOrder->state;
        $order->orderAmount = $thisOrder->subtotal;  // *** needs to be updated after 'Complete Checkout'
        $order->totalTax = $thisOrder->total_tax;  // *** needs to be updated after 'Complete Checkout'
        $order->save();

        // Get shipping options poll url
        $url = 'checkouts/'.$thisOrder->id.'/shipping-rates';
        $response = $this->getCheckout($url);
        $this->log_response($url, 'getCheckout', $response);
        if ($response->getStatusCode() != 202 || $response->getStatusCode() != 200) {    // reasonPhrase = "Accepted"
            \Session::flash('flash_message', 'Er is iets fout gegaan met het versturen van de order naar Smake, neem contact op met de systeembeheerder');
            return redirect()->route('variants.index');
        }
        $pollUrl = $response->getHeaders()['Location'][0];  // retrieve poll url
        for($i = 0; $i < 100; $i++) {
            usleep(100000);
            $pollResult = $this->Poll($pollUrl);
            if($pollResult->getStatusCode() === 200){  // test for order to be completed before continuing to shipping details
                //*** Debug only
                        $this->log_response($pollUrl,'poll checkout', $pollResult);  // Log checkout response -- End of debug
                break;
            }
        }

        // Get shipping options
        $url = 'checkouts/'.$thisOrder->id.'shipping-rates';
        $response = $this->getSmakeData($url);
        //*** Debug only
                $command = 'GET checkouts/'.$thisOrder->id.'shipping-rates';
                $this->log_response($command, 'Get shipping options', $response); // End debug

        $shippingOptions = json_decode($response->getBody())->data;
        $shippingHandles = [];
        foreach($options as $option) {  // build array of available shipping 'handle' options
            array_push($shippingHandles, $option->handle);
            //*** Debug only
                    $this->log_item('handle: ', $option->handle); // End debug
        }
        $defaultHandle = env('SHIPPING_HANDLE', '');
        if (!in_array($defaultHandle, $shippingHandles)) {
            \Session::flash('flash_message', 'Er is iets fout gegaan met het versturen van de order naar Smake, neem contact op met de systeembeheerder');
            return redirect()->route('variants.index');
        }

        // build and send update shipping_line object
        $app = app();
        $shippingHandle = $app->make('stdClass'); // build JSON shipping handle Object
        $shippingHandle->handle = $defaultHandle;
        $shippingLine = $app->make('stdClass');
        $shippingLine->shipping = $shippingHandle;
        //*** Debug only
                $command = 'POST Update Shipping handle';
                $this->log_response($command, 'Created Object', $shippingHandle); // End debug
        $shippingResponse = $this->PostSmakeData(json_encode($shippingLine), 'checkouts/'.$thisOrder);
        //*** Debug only
                $command = 'response update shipping_line';
                $this->log_response($command, 'response from update ShippingLine', $shippingHandle); // End debug
        // check the result
        if ($shippingResponse->getStatusCode() != 200) {
            \Session::flash('flash_message', 'Er is iets fout gegaan met het versturen van de order naar Smake, neem contact op met de systeembeheerder');
            return redirect()->route('variants.index');
        }
        $shippingLine = json_decode($shippingResponse->getBody());
        $order->shippingRate =  $shippingLine->shipping_line->price;
        $order->orderAmount = $shippingLine->subtotal;
        $order->totalTax = $shippingLine->total_tax;
        $order->save();

        // complete Checkout
        $payment = $app->make('stdClass');
        $payment->handle = "invoice";
        $payment->amount = $shippingLine->total;
        $completeCheckout = $app->make('stdClass');
        $completeCheckout->payment = $payment;
        //*** Debug only
                $command = 'POST Complete Checkout';
                $this->log_response($command, 'Created Object', $completeCheckout); // End debug
        $checkoutResponse = $this->PostSmakeData(json_encode($completeCheckout), 'checkouts/'.$thisOrder.'/complete');
        if ($checkoutResponse->getStatusCode() != 200) {
            \Session::flash('flash_message', 'Er is iets fout gegaan met het versturen van de order naar Smake, neem contact op met de systeembeheerder');
            return redirect()->route('variants.index');
        }
        //*** Debug only
                $command = 'response Complete Checkout';
                $this->log_response($command, 'Returned Object', $completeCheckout); // End debug
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
