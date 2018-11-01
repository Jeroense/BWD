<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CompositeMediaDesign;
use App\Attribute;

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
        // $a = Attribute::whereIn('variantId', $result)->where('key', 'size')->get();
        // $sizes = $a->pluck('value')->Unique();
        return view('orders.create', compact('AvailableSizesWithVariantIds', 'customVariant'));
    }

    public function store()
    {
        return ('storing Order');
    }
}
