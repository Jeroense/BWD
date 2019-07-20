<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use App\ProductAttribute;
use App\AttributeValue;

class ProductAttributeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::with('attributes.attrValues')->get();
        return view('productAttributes.index', compact('products'));

        // foreach($products as $product) {
        //     dd($product);
        //     dd($product->attributes->product_attribute_key); // levert key
        //     dd($product->attributes->attrValues->attr_value); // levert attribute value
        // }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id)
    {
        // dd($id);
        return view('productAttributes.create', compact('id'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $newProductAttribute = new ProductAttribute();
        $newProductAttribute->product_id = $request->productId;
        $newProductAttribute->product_attribute_key = $request->attribute_key;
        $newProductAttribute->save();
        $newAttributeValue = new AttributeValue();
        $newAttributeValue->attribute_id = $newProductAttribute->id;
        $newAttributeValue->attr_value = $request->attribute_value;
        $newAttributeValue->save();
        return redirect()->route('productAttributes.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $attribute = ProductAttribute::with('attrValues')->find($id);
        return view('productAttributes.edit', compact('attribute'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $update = ProductAttribute::find($id);
        $update->update(['product_attribute_key' => $request->attribute_key]);
        $update->attrValues()->update(['attr_value' => $request->attribute_value]);
        return redirect()->route('productAttributes.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
