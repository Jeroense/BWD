<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\CompositeMediaDesign;

class CompositeMediaDesignController extends Controller
{
    public function index()
    {
        //
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        ini_set("log_errors", 1);
        ini_set("error_log", "logs/errors.log");
        error_log($request);
        try {
            $compositeMediaDesign = new CompositeMediaDesign();

            $image = $request->get('imgBase64');
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = time().md5($request->get('imgName')).'.'.'png';
            $imagePath = public_path(). '\\customVariants\\';
            File::put($imagePath . $imageName, base64_decode($image));

            $compositeMediaDesign->designName = $request->get('imgName');
            $compositeMediaDesign->designId = $request->get('designId');
            $compositeMediaDesign->fileName = $imageName;
            $compositeMediaDesign->fileFolder = 'customVariants';
            $compositeMediaDesign->baseColor = $request->get('baseColor');
            $compositeMediaDesign->width_px = $request->get('width');
            $compositeMediaDesign->height_px = $request->get('height');
            $compositeMediaDesign->save();
            return response()->json('Custom Variant succesvol opgeslagen', 201);
        }
        catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
