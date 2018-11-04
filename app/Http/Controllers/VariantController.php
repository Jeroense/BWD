<?php

namespace App\Http\Controllers;

use Intervention\Image\Facades\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Variant;
use App\Tshirt;
use App\Design;
use App\CompositeMediaDesign;

class VariantController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $customVariants = CompositeMediaDesign::All();
        // dd($customVariants);
        return view('variants.index', compact('customVariants'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $shirts = Tshirt::orderBy('color', 'asc')->get();
        $designs = Design::All();
        return view('variants.create', compact('shirts', 'designs'));
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
        // **** create Guzzle object to upload a custom Variant
            // $app = app();
            // $dimensions = $app->make('stdClass');
            // $dimensions->width = "myWidth";
            // $dimensions->height = "myHight";

            // $customizations = $app->make('stdClass');
            // $customizations->type = "dtg";
            // $customizations->production_media_id = "myProductionMediaId";
            // $customizations->dimensions = $dimensions;

            // $front = $app->make('stdClass');
            // $front->composite_media_id = "myCompositeMediaId";
            // $front->customizations = $customizations;

            // $views = $app->make('stdClass');
            // $views->front = $front;
            // $newVariant = $app->make('stdClass');
            // $newVariant->views = $views;

            // dd(json_encode((array)$newVariant));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return('test details');
    }



    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $target = CompositeMediaDesign::find($id);
        $targetUri = public_path().'\\'.$target->fileFolder.'\\'.$target->fileName;
        if (file_exists($targetUri)) {
            unlink($targetUri);
        }
        $target->delete();
        return redirect()->route('variants.index', $id);
    }
}
