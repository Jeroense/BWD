<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Traits\SmakeApi;
use App\CompositeMediaDesign;
use App\FrontCustomization;
use App\Order;
use App\Customer;
use App\Variant;
use App\customVariant;
use App\Attribute;
use App\Front;
use App\View;
use App\Design;
use App\TshirtSizes;

class CustomVariantController extends Controller
{
    use SmakeApi;

    public function index() {
        $customVariants = CustomVariant::All();
        // dd($customVariants);
        return view('customVariants.index', compact('customVariants'));
    }

    public function createVariant(Request $request) {
        $compositeMediaDesign = CompositeMediaDesign::find($request->compositeMediaId);
        if($compositeMediaDesign->smakeId === null){
            $uploadResult = $this->uploadCompositeMediaDesignToSmake($compositeMediaDesign);
            if($uploadResult = 'error'){
                \Session::flash("flash_message", "Er is iets fout gegaan met het opslaan van de 'custom variant', neem contact op met de systeembeheerder");
                return redirect()->route('variants.index');
            }
        }
        for ($i = 1; $i <= $request->numberOfSizes; $i++) {
            $currentEan = 'ean'.$i;
            $currentSize = 'Size'.$i;
            $currentParentVariantId = 'parentVariantId'.$i;
            if($request->has($currentEan)){
                $shirtLength = TshirtSizes::select('length_mm')->where('size', $request->$currentSize)->get()[0]->length_mm;
                $pixelSize = $shirtLength / 1250;
                $newCustomVariant = new CustomVariant();
                $newCustomVariant->parentVariantId = $request->$currentParentVariantId;
                $newCustomVariant->ean = $request->$currentEan;
                $newCustomVariant->size = $request->$currentSize;
                $newCustomVariant->width_mm = $compositeMediaDesign->width_px * $pixelSize;
                $newCustomVariant->height_mm = $compositeMediaDesign->height_px * $pixelSize;
                $newCustomVariant->fileName = $compositeMediaDesign->fileName;
                $newCustomVariant->compositeMediaId = $request->compositeMediaId;
                $newCustomVariant->productionMediaId = $compositeMediaDesign->designId;
                $newCustomVariant->smakeProductionMediaId = Design::select('smakeId')->find($newCustomVariant->productionMediaId)->get()[0]->smakeId;
                $newCustomVariant->smakeCompositeMediaId = CompositeMediaDesign::select('smakeId')->find($request->compositeMediaId)->get()[0]->smakeId;
                $uploadCustomVariantBody = $this->buildVariantObject($newCustomVariant);
                $newSmakeCustomVariant = $this->uploadCustomVariantToSmake($newCustomVariant, $uploadCustomVariantBody);
                $newCustomVariant->smakeVariantId  = $newSmakeCustomVariant->id;
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
            \Session::flash('flash_message', 'Er is iets fout gegaan met het opslaan van de custom variant, neem contact op met de systeembeheerder');
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

    public function buildOrderObject($variantId) {
        $app = app();
        $shippingAddress = $app->make('stdClass');
        $shippingAddress->first_name = 'Barry';
        $shippingAddress->last_name = 'Bles';
        $shippingAddress->street1 = 'Ulenpasweg 2F4';
        $shippingAddress->zip = '7041 GB';
        $shippingAddress->city = "'s-Heerenberg";
        $shippingAddress->country_code = 'NL';
        $shippingAddress->province_code = 'GLD';
        $shippingAddress->phone = '0314653130';
        $shippingAddress->email = 'info@internetsport.nl';
        $items = $app->make('stdClass');
        $items->variant_id = $variantId;
        $items->quantity = 1;
        $checkout = $app->make('stdClass');
        $checkout->email = 'info@internetsport.nl';
        $checkout->items = [$items];
        $checkout->shipping_address = $shippingAddress;
        return json_encode((array)$checkout);
    }

    public function orderVariant($id) {
        $path = env('CHECKOUT_PATH','');
        $variant = CustomVariant::find($id);
        $checkoutBody = $this->buildOrderObject($variant->smakeVariantId);
        $checkoutResponse = $this->CheckoutOrder($checkoutBody, $path);
        // start update shipping_line here
        return redirect()->route('customvariants.index');
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
