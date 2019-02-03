<?php

namespace App\Http\Controllers;

use App\Product;
use App\Variant;
use App\Attribute;
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
        // $response = $this->GetProducts();
        // $products = json_decode($response->getBody())->data;
        // // dd($products);
        // foreach($products as $product) {

        //     if($product->id == 8186)  {
        //         dd($product);
        //         foreach($product->variants as $variant){
        //             $imageName = Tshirt::where('id', Variant::where('variantId', $variant->id)->pluck('mediaId')->first())->pluck('fileName')->first();
        //             $variant->fileName = $imageName;
        //         }
        //     }
        // }
        $variants = Variant::all();
        return view('products.index',compact('variants'));
    }

    public function media()
    {
        // $imageId='2838736/w_200,h_223/w_200,h_223/fit_fill/fm_png';
        // $media = $this->GetMedia($imageId);
        // return view('products.media',compact('media'));
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
        $response = $this->GetProducts();
        if ($response->getStatusCode() === 200) {
            $products = json_decode($response->getBody())->data;
    // dd($products);
            foreach($products as $product) {
                $newProduct = new Product();
                $localProduct = Product::where('smakeId', $product->id)->first();
                if($product->id != 8186)  {
                    continue;
                }
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
                        $fileName = time().md5($variant->media_id);
                        $shirtImageDownloaded = $this->getBaseTshirtImage($variant->media_id, $fileName);
                        $newVariant->localMediaFileName = $shirtImageDownloaded;
                        $smallImageDownloaded = $this->getBaseTshirtImage($variant->media_id . "/w_470,h_524/w_470,h_524/fit_fill/fm_png", "sm_" . $fileName);
                        $newVariant->smallFileName = $smallImageDownloaded;
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

        $fileName = time().md5(2984016);
        $shirtImageDownloaded = $this->getBaseTshirtImage(2984016, $fileName);
        // dd($request);
        $this->validate($request, [
            'imageName' => 'required|image|mimes:jpeg,jpg,png|max:9216',
            'tShirtColor' => 'required|unique:tshirts,color'
        ]);
dd($shirtImageDownloaded);
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
