<?php

namespace App\Http\Controllers;

use App\Design;
use Illuminate\Http\Request;

class DesignController extends Controller
{
    public function dashboard() {
        return view('design.dashboard');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $images = Design::All();
        // dd($images);
        return view('design.index', compact('images'));
    }

    public function upload() {
        return view('design.upload');
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'designImage' => 'required|image|mimes:jpeg,jpg,png|max:9216'
        ]);

        $image = $request->designImage;
        $design = new Design();
        $design->originalName = $image->getClientOriginalName();
        $design->mimeType = $image->getClientMimeType();
        $design->fileSize = $image->getClientSize();
        $design->fileName = time().md5($design->originalName).'.'.$image->getClientOriginalExtension();
        $design->path = public_path('designImages');
        $image->move($design->path, $design->fileName);
        $design->save();

        return redirect()->route('design.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Design  $design
     * @return \Illuminate\Http\Response
     */
    public function show(Design $design)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Design  $design
     * @return \Illuminate\Http\Response
     */
    public function edit(Design $design)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Design  $design
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Design $design)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Design  $design
     * @return \Illuminate\Http\Response
     */
    public function destroy(Design $design)
    {
        //
    }
}
