<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\TshirtMetric;

class TshirtMetricController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tshirtMetrics = TshirtMetric::all();
        return view('manage.metrics.index', compact('tshirtMetrics'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('manage.metrics.create');
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
            'size' => 'required|max:5',
            'length' => 'required|numeric'
        ]);

        $tshirtMetric = new TshirtMetric();
        $tshirtMetric->size = strtoupper($request->size);
        $tshirtMetric->length_mm = $request->length;
        $tshirtMetric->save();
        return redirect()->route('metrics.index');
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
        $tshirtMetric = TshirtMetric::where('id', $id)->first();
        return view( 'manage.metrics.edit', compact('tshirtMetric'));
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
        $this->validate($request, [
            'size' => 'required|max:5',
            'length' => 'required|numeric'
        ]);

        $tshirtMetric = TshirtMetric::findOrFail($id);
        $tshirtMetric->size = strtoupper($request->size);
        $tshirtMetric->length_mm = $request->length;
        $tshirtMetric->save();
        return redirect()->route('metrics.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $tshirtMetric = TshirtMetric::findOrFail($id);
        $tshirtMetric->delete();
        return redirect()->route('metrics.index');
    }
}
