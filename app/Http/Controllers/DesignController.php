<?php

namespace App\Http\Controllers;

use App\Design;
use Illuminate\Http\Request;
use App\Http\Traits\SmakeApi;

class DesignController extends Controller
{
    use SmakeApi;

    public function dashboard() {
        return view('designs.dashboard');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $images = Design::All();
        return view('designs.index', compact('images'));
    }

    public function upload() {
        return view('designs.upload');
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
        // ini_set("log_errors", 1);
        // ini_set("error_log", "logs/errors.log");
        // error_log('made it up to here');
        // $this->validate($request, [
        //     'designImage' => 'required|image|mimes:jpeg,jpg,png|max:9216'
        // ]);
        $image = $request->file('file');
        $design = new Design();
        $design->originalName = $image->getClientOriginalName();
        $design->mimeType = $image->getClientMimeType();
        $design->fileSize = $image->getClientSize();
        $design->fileName = time().md5($design->originalName).'.'.$image->getClientOriginalExtension();
        $design->path = public_path('designImages');
        $image->move($design->path, $design->fileName);

        $designPath = env('DESIGN_PATH', '');
        $response = $this->UploadMedia($designPath, $design->fileName, $design->fileSize, 'media');
        if ($response->getStatusCode() === 201) {
            $designResponse = json_decode($response->getBody());
            $design->smakeId = $designResponse->id;
            $design->smakeFileName = $designResponse->file_name;
            $design->downloadUrl = $designResponse->download_url;
            $design->save();
        } else {
            unset($response);
            gc_collect_cycles();
            if (file_exists(public_path('designImages').'\\'.$design->fileName)) {
                unlink(public_path('designImages').'\\'.$design->fileName);
            }
            // \Session::flash('flash_message', 'Er is iets fout gegaan met het opslaan van het design, neem contact op met de systeembeheerder');
            return response()->json('Er is iets fout gegaan met het uploaden van designs', 400);
        }
        return response()->json('Custom Variant succesvol opgeslagen', 201);
        // return redirect()->route('designs.index');
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
    public function destroy($id)
    {
        $image = Design::find($id);
        if (file_exists(public_path('designImages').'\\'.$image->fileName)) {
            unlink(public_path('designImages').'\\'.$image->fileName);
        }
        $image->delete();
        return redirect()->route('designs.index');
    }
}
