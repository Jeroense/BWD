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

    public function index()
    {
        $customVariants = CustomVariant::All();
        $customers = Customer::select('lastName', 'firstName', 'lnPrefix', 'id')->orderBy('lastName')->orderBy('firstName')->get();

        $persons = [];
        foreach($customers as $customer) {
           array_push($persons, $customer->lnPrefix == null
                ? ['fullName' => $customer->lastName . ', ' . $customer->firstName, 'id' => $customer->id]
                : ['fullName' => $customer->lastName . ', ' . $customer->firstName . ' ' . $customer->lnPrefix, 'id' => $customer->id]);
        }

        return view('customVariants.index', compact('customVariants', 'persons'));
    }

    public function createVariant(Request $request)
    {
        $compositeMediaDesign = CompositeMediaDesign::find($request->compositeMediaId);
        if($compositeMediaDesign->smakeId === null){
            $uploadResult = $this->uploadCompositeMediaDesign($compositeMediaDesign);
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
                $newSmakeCustomVariant = $this->UploadCustomVariant($newCustomVariant, $uploadCustomVariantBody);

                if($newSmakeCustomVariant == null) {
                    if(\Session::pull('error') == 404){
                        return redirect()->route('variants.index')->with('status', 'Deze variant is momenteel niet beschikbaar');
                    } else {
                        return redirect()->route('variants.index')->with('status', 'Er is iets fout gegaan met het versturen van de custom variant naar Smake, neem contact op met de systeembeheerder');
                    }
                }

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

    public function buildVariantObject($newCustomVariant)
    {
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

    public function UploadCustomVariant($newCustomVariant, $uploadCustomVariantBody)
    {
        $parentVariant = $newCustomVariant->parentVariantId;
        $smakeVariantId = Variant::find($parentVariant);
        $url = 'variants/'.$smakeVariantId->variantId.'/design';
        $response = $this->postSmakeData($uploadCustomVariantBody, $url);

        if ($response->getStatusCode() === 202) {    // reasonPhrase = "Accepted"
            $pollUrl = $response->getHeaders()['Location'][0];

            for($i = 0; $i < 100; $i++) {
                usleep(100000);
                $pollResult = $this->Poll($pollUrl);

                if($pollResult->getStatusCode() === 200){
                    $designedVariantId = json_decode($pollResult->getBody())->resource_url;
                    $smakeNewCustomVariant = json_decode($this->getSmakeData('designed-variants/'.substr(strrchr($designedVariantId, '/'), 1))->getBody());
                    break;
                }
            }

        } else {
            \Session::put('error', $response->getStatusCode());
            return null;
        }

        return $smakeNewCustomVariant;
    }

    public function uploadCompositeMediaDesign($compositeMediaDesign)
    {
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
