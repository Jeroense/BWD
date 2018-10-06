<?php

namespace App\Http\Controllers;

use App\Product;
use App\Variant;
use App\Attribute;
use App\View;
use App\Front;
use App\FrontCustomization;
use App\Back;
use App\Left;
use App\Right;
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
        // dd($products);
        return view('products.index',compact('products'));
    }

    public function media()
    {
        $imageId='2838736/w_200,h_223/w_200,h_223/fit_fill/fm_png';
        $media = $this->GetMedia($imageId);
        //  dd($media);
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
        return view('products.create');
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
        dd($product);
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
        // $this->validate($request, [
        //     'designImage' => 'required|image|mimes:jpeg,jpg,png|max:9216'
        // ]);

        // $image = $request->designImage;
        // $design = new baseProduct();
        // $design->originalName = $image->getClientOriginalName();
        // $design->mimeType = $image->getClientMimeType();
        // $design->fileSize = $image->getClientSize();
        // $design->fileName = time().md5($design->originalName).'.'.$image->getClientOriginalExtension();
        // $design->path = public_path('baseImages');
        // $image->move($design->path, $design->fileName);
        // $design->save();

        // return redirect()->route('products.index');
    }

    public function upload() {
        //
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
