@extends('layouts.app')
@section('pageTitle', 'Afmeting Wijzigen')
@section('content')
<div class="mainContent container column is-6 pull-left">
    <form action="{{ route('metrics.update', $tshirtMetric->id) }}" method="POST">
        {{method_field('PUT')}}
        {{csrf_field()}}
        <div class="columns">
            <div class="column">
                <div class="field">
                    <label for="size" class="label">Maat:</label>
                    <p class="control">
                        <input type="text" class="input" name="size" id="size" value="{{$tshirtMetric->size}}">
                    </p>
                </div>
                <div class="field">
                    <label for="length" class="label">Lengte (mm):</label>
                    <p class="control">
                        <input type="text" class="input" name="length" id="length" value="{{$tshirtMetric->length_mm}}">
                    </p>
                </div>
            </div>
        </div>
        <div class="columns">
            <div class="column">
                <hr />
                <button class="button is-danger is-pulled-right" style="width: 250px;">Update Maat</button>
            </div>
        </div>
    </form>
</div>
@endsection

