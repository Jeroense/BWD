<?php

namespace App\Http\Controllers;

use App\Product;
use App\Variant;
use App\Attribute;
use App\View;
use App\Front;
use App\FrontCustomization;
use App\Back;
use App\BackCustomization;
use App\Left;
use App\LeftCustomization;
use App\Right;
use App\RightCustomization;
use App\Tshirt;
use Illuminate\Http\Request;
use App\Http\Traits\SmakeApi;

class ProductController extends Controller
{
    use SmakeApi;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = $this->GetJson();
        foreach($products as $product) {
            foreach($product->variants as $variant){
                $imageName = Tshirt::where('id', Variant::where('variantId', $variant->id)->pluck('mediaId')->first())->pluck('fileName')->first();
                $variant->fileName = $imageName;
            }
        }
        return view('products.index',compact('products'));
    }

    public function media()
    {
        $imageId='2838736/w_200,h_223/w_200,h_223/fit_fill/fm_png';
        $media = $this->GetMedia($imageId);
        return view('products.media',compact('media'));
    }

    public function dashboard() {
        return view('products.dashboard');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    // public function design(Request $request) {
    //     $result = Attribute::where('value', 'Royal')->first()->id;  // result = variant id.
    //     dd($result);
    // }
    public function productDownload() {
        // dd('productDowload function');
        return view('products.download');
    }

    public function download() {
        $products = $this->GetJson();
        foreach($products as $product) {
            $newProduct = new Product();
            $localProduct = Product::where('smakeId', $product->id)->first();
            if($localProduct == null){
                $newProduct->productName = $product->title;
                $newProduct->productDescription = $product->description;
                $newProduct->smakeId = $product->id;
                $newProduct->save();
            } else {
                $newProduct->id = $localProduct->id;
            }
            foreach($product->variants as $variant){
                $newVariant = new Variant();
                $localVariant = Variant::where('variantId', $variant->id)->first();
                if($localVariant == null){
                    $newVariant->variantId = $variant->id;
                    $newVariant->productId = $newProduct->id;
                    $newVariant->price = $variant->price;
                    $newVariant->tax = $variant->tax;
                    $newVariant->taxRate = $variant->tax_rate;
                    $newVariant->mediaId = $variant->media_id;
                    $newVariant->save();
                    foreach($variant->attributes as $attribute){
                        $newAttribute = new Attribute();
                        $localAttribute = Attribute::where('variantId', $variant->id)->first();
                        $newAttribute->variantId = $newVariant->id;
                        $newAttribute->key = $attribute->name;
                        $newAttribute->value = $attribute->value;
                        $newAttribute->save();
                    }
                    $newView = new View();
                    $newView->variantId = $newVariant->id;
                    $newView->save();
                    $newFront = new Front();
                    $newFront->viewId = $newView->id;
                    $newFront->compositeMediaId = $variant->views->front->composite_media_id;
                    $newFront->save();
                    foreach($variant->views->front->customizations as $customization){
                        $newCustomization = new FrontCustomization();
                        $newCustomization->frontId = $newFront->id;
                        $newCustomization->type = $customization->type;
                        $newCustomization->productionMediaId = $customization->production_media_id;
                        $newCustomization->previewMediaId = $customization->preview_media_id;
                        $newCustomization->width = $customization->dimension->width;
                        $newCustomization->height = $customization->dimension->height;
                        $newCustomization->save();
                    }
                    foreach($variant->views->back->customizations as $customization){
                        $newCustomization = new BackCustomization();
                        $newCustomization->backId = $newBack->id;
                        $newCustomization->type = $customization->type;
                        $newCustomization->productionMediaId = $customization->production_media_id;
                        $newCustomization->previewMediaId = $customization->preview_media_id;
                        $newCustomization->width = $customization->dimension->width;
                        $newCustomization->height = $customization->dimension->height;
                        $newCustomization->save();
                    }
                    foreach($variant->views->left->customizations as $customization){
                        $newCustomization = new LeftCustomization();
                        $newCustomization->leftId = $newLeft->id;
                        $newCustomization->type = $customization->type;
                        $newCustomization->productionMediaId = $customization->production_media_id;
                        $newCustomization->previewMediaId = $customization->preview_media_id;
                        $newCustomization->width = $customization->dimension->width;
                        $newCustomization->height = $customization->dimension->height;
                        $newCustomization->save();
                    }
                    foreach($variant->views->right->customizations as $customization){
                        $newCustomization = new RightCustomization();
                        $newCustomization->rightId = $newRight->id;
                        $newCustomization->type = $customization->type;
                        $newCustomization->productionMediaId = $customization->production_media_id;
                        $newCustomization->previewMediaId = $customization->preview_media_id;
                        $newCustomization->width = $customization->dimension->width;
                        $newCustomization->height = $customization->dimension->height;
                        $newCustomization->save();
                    }
                }
            }
        }
        return view('products.dashboard');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Create array with colors to populate a selectbox from variant->attributes->value where the key is color
     * The purpose is to assign the imageID of a T-shirt with the correct color to base variants
     *
     * @return view uploadImage
     */
    public function uploadImage() {
        $colors = Attribute::where('key', 'color')->groupBy('value')->pluck('value')->toArray(); // find all used colors from existing base variants
        // $colors = Attribute::distinct('value')->where('key', 'color')->pluck('value')->toArray();
        // dd($colors);
        return view('products.uploadImage', compact('colors'));
    }

    /**
     * Attaches a T-shirt image to base variants
     * select all variants with the chosen color and updates the field mediaId in the variants table
     *
     * @param Request $request
     * @return 'redirect uploadImage'
     */
    public function attachImage(Request $request) {

        $this->validate($request, [
            'imageName' => 'required|image|mimes:jpeg,jpg,png|max:9216',
            'tShirtColor' => 'required|unique:tshirts,color'
        ]);

        $image = $request->imageName;
        $tshirt = new Tshirt();
        $tshirt->color = $request->tShirtColor;
        $tshirt->fileName = time().md5($image->getClientOriginalName()).'.'.$image->getClientOriginalExtension();
        $tshirt->filePath = public_path('tshirtImages');
        $image->move($tshirt->filePath, $tshirt->fileName);
        $tshirt->save();

        $selection = ['key' => 'color', 'value' => $tshirt->color];

        foreach(Attribute::where($selection)->get() as $id) {
            $updatedVariant = Variant::findOrFail($id->variantId);
            $updatedVariant->mediaId = $tshirt->id;
            $updatedVariant->save();
        }
        return redirect()->route('products.uploadImage');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }
}
