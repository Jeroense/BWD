<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Traits\SmakeApi;
use App\CompositeMediaDesign;
use App\FrontCustomization;
use App\Order;
use App\Customer;
use App\Variant;
use App\Attribute;
use App\Front;
use App\View;
use App\Design;

class OrderController extends Controller
{
    use SmakeApi;

    public function dashboard() {
        return view('orders.dashboard');
    }

    public function create($id)
    {
        // $customVariant = CompositeMediaDesign::find($id);
        // $shirtsOfColor = Attribute::where('value', $customVariant->baseColor)->pluck('variantId');
        // $AvailableSizesWithVariantIds = Attribute::whereIn('variantId', $shirtsOfColor)->where('key', 'size')->get()->pluck('value','variantId')->Unique();
        // return view('orders.create', compact('AvailableSizesWithVariantIds', 'customVariant'));
    }

    public function store(Request $request)
    {
        // dd($request);
        return ('storing Order');
    }

    public function checkOrder(Request $request)
    {
        
    }


}


