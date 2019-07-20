<?php

namespace App\Http\Controllers;
use App\Http\Traits\DebugLog;
use App\Http\Traits\SmakeApi;
use Illuminate\Http\Request;
use App\System;
use Session;
use App\Design;

class SystemController extends Controller
{
    use DebugLog;
    use SmakeApi;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sysinfo = System::first();
        // dd($sysinfo);
        if(!$sysinfo == null) {
            return view('manage.system.index', compact('sysinfo'));
        }
        $sysInfo = new System();
        return view('manage.system.create', compact('sysInfo'));
    }

    public function uploadLogo()
    {
        return view('manage.system.uploadLogo');
    }

    public function storeLogo(Request $request)
    {
        $image = $request->file('file');
        $logo = new Design();
        $logo->originalName = $image->getClientOriginalName();
        $logo->mimeType = $image->getClientMimeType();
        $logo->fileSize = $image->getClientSize();
        $logo->fileName = time().md5($logo->originalName).'.'.$image->getClientOriginalExtension();
        $logo->path = public_path('systemImages');
        $image->move($logo->path, $logo->fileName);
        $logoPath = env('IMAGE_PATH', '');
        $response = $this->UploadMedia($logoPath, $logo->fileName, $logo->fileSize, 'media');

        $this->log_responseBody('text', $response, $file = 'public/logs/message.txt');
        $this->log_var('response status = ' . $response->getStatusCode(), $file = 'logs/message.txt');

        if ($response->getStatusCode() === 201) {

            $logoResponse = json_decode($response->getBody());
            $this->log_var('logo_id = ' . $logoResponse->id, $file = 'logs/message.txt');
            $systemData = System::find(1);
            $systemData->update(['logo_id' => $logoResponse->id]);
            $this->log_var('name = ' . $systemData->organizationName, $file = 'logs/message.txt');
        } else {
            unset($response);
            gc_collect_cycles();
            if (file_exists(public_path('systemImages').'\\'.$logo->fileName)) {
                unlink(public_path('systemImages').'\\'.$logo->fileName);
            }
            return response()->json('Er is iets fout gegaan met het uploaden van designs', 400);
        }

        return response()->json('Pakbon logo succesvol opgeslagen', 201);
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'organizationName' => 'required|max:255',
            'street' => 'required|max:255',
            'houseNr' => 'required|max:255',
            'postalCode' => 'required|max:7',
            'city' => 'required|max:255',
            'email' => 'required|max:255',
            'phone' => 'required|max:20',
            'cocNr' => 'required|max:10',
            'vatNr' => 'required|max:15',
            'appSerNr' => 'required|max:255',
            // 'systemKey' => 'required|max:255',
            'apiKeyBol' => 'required|max:255',
            'apiKeySmake' => 'required|max:255'
        ]);

        $sysInfo = new System();
        $sysInfo->organizationName = $request->organizationName;
        $sysInfo->street = $request->street;
        $sysInfo->houseNr = $request->houseNr;
        $sysInfo->postalCode = $request->postalCode;
        $sysInfo->city = $request->city;
        $sysInfo->email = $request->email;
        $sysInfo->phone = $request->phone;
        $sysInfo->cocNr = $request->cocNr;
        $sysInfo->vatNr = $request->vatNr;
        $sysInfo->appSerNr = $request->appSerNr;
        $sysInfo->systemKey = 'default value';
        $sysInfo->apiKeyBol = $request->apiKeyBol;
        $sysInfo->apiKeySmake = $request->apiKeySmake;

        if ($sysInfo->save()) {
            return redirect()->route('system.index');
        } else {
            Session::flash('danger', 'Helaas is het opslaan van de systeeminformatie niet gelukt');
            return redirect()->route('system.create');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\System  $system
     * @return \Illuminate\Http\Response
     */
    public function show(System $system)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\System  $system
     * @return \Illuminate\Http\Response
     */
    public function edit(System $system)
    {
        $sysInfo = System::first();
        return view('manage.system.edit', compact('sysInfo'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\System  $system
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'organizationName' => 'required|max:255',
            'street' => 'required|max:255',
            'houseNr' => 'required|max:255',
            'postalCode' => 'required|max:7',
            'city' => 'required|max:255',
            'email' => 'required|max:255',
            'phone' => 'required|max:20',
            'cocNr' => 'required|max:10',
            'vatNr' => 'required|max:15',
            'appSerNr' => 'required|max:255',
            'apiKeyBol' => 'required|max:255',
            'apiKeySmake' => 'required|max:255'
        ]);

        $updatedInfo = System::findOrFail($id);
        $updatedInfo->organizationName = $request->organizationName;
        $updatedInfo->street = $request->street;
        $updatedInfo->houseNr = $request->houseNr;
        $updatedInfo->postalCode = $request->postalCode;
        $updatedInfo->city = $request->city;
        $updatedInfo->email = $request->email;
        $updatedInfo->phone = $request->phone;
        $updatedInfo->cocNr = $request->cocNr;
        $updatedInfo->vatNr = $request->vatNr;
        $updatedInfo->appSerNr = $request->appSerNr;
        $updatedInfo->systemKey = 'default value';
        $updatedInfo->apiKeyBol = $request->apiKeyBol;
        $updatedInfo->apiKeySmake = $request->apiKeySmake;

        if ($updatedInfo->save()) {
            return redirect()->route('system.index');
        } else {
            Session::flash('danger', 'Helaas is het opslaan van de systeeminformatie niet gelukt');
            return view('manage.system.edit', compact('updatedInfo'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\System  $system
     * @return \Illuminate\Http\Response
     */
    public function destroy(System $system)
    {
        //
    }

    public function restore(System $system)
    {
        //
    }

    public function backup(System $system)
    {
        //
    }
}
