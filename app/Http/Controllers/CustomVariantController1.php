<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Traits\SmakeApi;
use App\CompositeMediaDesign;
use App\FrontCustomization;
use App\Order;
use App\Customer;
use App\customVariant;
use App\Variant;
use App\Attribute;
use App\Front;
use App\View;
use App\Design;
use App\TshirtSizes;

class CustomVariantController extends Controller
{
    use SmakeApi;

    public function index()
    {
        return('index @ customvariant controller');
        // $customVariants = CustomVariant::All();
        // return view('customVariants.index', compact('customVariants'));
    }

     /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createVariant(Request $request)
    {
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
        return ('Storing Custom Variants');
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

        return (json_encode((array)$newVariant));
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
}
