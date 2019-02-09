<?php

namespace App\Http\Controllers;

use App\Product;
use App\Variant;
use App\Attribute;
use Illuminate\Http\Request;
use App\Http\Traits\SmakeApi;
use App\Http\Traits\DebugLog;

class ProductController extends Controller
{
    use SmakeApi;
    use DebugLog;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $variants = Variant::all();
        return view('products.index',compact('variants'));
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

    public function productDownload() {
        return view('products.download');
    }

    public function download() {
        ini_set('max_execution_time', 600000);
        $response = $this->GetSmakeData('products?filter[id]=8186');
        if ($response->getStatusCode() === 200) {
            $products = json_decode($response->getBody())->data;
    // dd($products);
            foreach($products as $product) {
                if($product->id != 8186)  {
                    continue;
                }
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
                    // $this->checkIfAvailable($variant->id);
                    $newVariant = new Variant();
                    $localVariant = Variant::where('variantId', $variant->id)->first();
                    if($localVariant == null){
                        $newVariant->variantId = $variant->id;
                        $newVariant->productId = $newProduct->id;
                        $newVariant->price = $variant->price;
                        $newVariant->tax = $variant->tax;
                        $newVariant->taxRate = $variant->tax_rate;
                        $newVariant->mediaId = $variant->media_id;
                        $fileName = 'lg_' . $variant->media_id;
                        file_exists('tshirtImages/' . $fileName . '.png') ? $newVariant->localMediaFileName = $fileName .'.png' : $newVariant->localMediaFileName = $this->downloadMedia($variant->media_id, $fileName, 'tshirtImages');
                        $fileName = 'sm_' . $variant->media_id;
                        file_exists('tshirtImages/' . $fileName . '.png') ? $newVariant->smallFileName = $fileName . '.png' : $newVariant->smallFileName = $this->downloadMedia($variant->media_id, $fileName, 'tshirtImages');
                        $newVariant->save();
                        foreach($variant->attributes as $attribute){
                            $newAttribute = new Attribute();
                            $localAttribute = Attribute::where('variantId', $variant->id)->first();
                            $newAttribute->variantId = $newVariant->id;
                            $newAttribute->key = $attribute->name;
                            $newAttribute->value = $attribute->value;
                            $newAttribute->save();
                        }
                    }
                }
            }
            $variants = Variant::with('attributes')->get();
            foreach($variants as $variant) {
                $color = $variant->attributes[0]->value;
                $size = $variant->attributes[1]->value;
                $variant->color = $variant->attributes[0]->value;
                $variant->size = $variant->attributes[1]->value;
                $variant->save();
            }
            $message = 'Alle Smake producten zijn succesvol gedownload.';
            return view('products.dashboard', compact('message'));
        } else {
            $message = 'Er is een probleem opgetreden bij het downloaden van alle producten. Neem contact op met de systeembeheerder.';
            return view('products.dashboard', compact('message'));
        }
    }

    public function checkIfAvailable($id) {
        $response = $this->getSmakeData('variants?filter[id]='.$id);
        if(json_decode($response->getBody())->data == null) {
            $this->log_var($id, 'logs/message.txt');
        }
        return;
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
