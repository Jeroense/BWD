@extends('layouts.app')
@section('pageTitle', 'Nieuwe Variant Maken')
@section('content')

<div class="imageSelections">
    <div class="container m-t-20 is-pulled-left">
        <h3 class="is-size-4 has-text-weight-semibold">Selecteer Kleur</h3>
    </div>
    <div class="container m-t-20 is-pulled-left">
        <div class="imageSelection">
            @foreach($shirts as $shirt)
            <img src="{{ url('/tshirtImages') }}/{{ $shirt->fileName }}"
                 width="100"
                 id="a{{$loop->iteration}}"
                 class="imageSelector"
                 onclick="setActiveImage({{ $loop->iteration }}, src, '{{$shirt->color}}');">
            @endforeach
        </div>
    </div>
</div>
<div class="designSelection is-hidden">
    <div class="container m-t-20 is-pulled-left">
        <h3 class="is-size-4 has-text-weight-semibold">Selecteer Design</h3>
    </div>
    <div class="container m-t-20 is-pulled-left">
        <div class="designSelection">
            @foreach($designs as $design)
            <img src="{{ url('/designImages') }}/{{ $design->fileName }}"
                 width="100"
                 id="b{{ $loop->iteration }}"
                 class="designSelector"
                 onclick="setActiveDesign({{ $loop->iteration }}, src, {{ $design->id }});">
            @endforeach
        </div>
    </div>
    <div class="container m-t-20 is-pulled-left">
        <button id="customizationStart"
                class="button is-danger"
                disabled value="{{ route('variants.store') }}">Start Customization</button>
    </div>
</div>

<div class="designArea is-hidden">
    <div id="customizationContainer"></div>
    <div class="container .is-fullhd">
        <label for='customName'>Naam CustomDesign:</label>
        <input type="text" name="customName" id='customName'>
        <div><a href="{{ route('store') }}"
                id="saveImage"
                returnUrl="{{ route('variants.index') }}"
                class="button is-danger m-b-100">Custom Variant Opslaan</a></div>
    </div>
</div>

@endsection
@section('scripts')
    <script src="{{ asset('js/customization.js') }}"></script>
@endsection

