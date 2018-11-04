<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CompositeMediaDesign;
use App\Attribute;
use App\Front;
use App\View;

class OrderController extends Controller
{
    public function dashboard() {
        return view('orders.dashboard');
    }

    public function create($id)
    {
        $customVariant = CompositeMediaDesign::find($id);
        $shirtsOfColor = Attribute::where('value', $customVariant->baseColor)->pluck('variantId');
        $AvailableSizesWithVariantIds = Attribute::whereIn('variantId', $shirtsOfColor)->where('key', 'size')->get()->pluck('value','variantId')->Unique();
        return view('orders.create', compact('AvailableSizesWithVariantIds', 'customVariant'));
    }

    public function store(Request $request)
    {
        // dd($request);
        return ('storing Order');
    }

    public function checkOrder(Request $request)
    {
        // dd($request);
        for ($i = 1; $i <= $request->numberOfSizes; $i++) {
            $listSelection = "parentVariantIdAndSize".$i;
            list($variantId, $Size) = explode(",", $request->$listSelection);
            // dd($variantId);
            // $variant = View::where('id', Front::where('compositeMediaId', $request->designId)->pluck('id'))->pluck('variantId');
            // dd(list);
            // $attr = Attribute::where('variantId', $variant)->pluck('value')->where('value', $Size);
            // dd($attr);


            

        }
        return ('checking Order');
    }
}
