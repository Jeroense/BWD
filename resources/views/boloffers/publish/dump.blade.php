@extends('layouts.app')
@section('pageTitle', 'Dump data offers')
@section('content')


<div class="columns">
  <div class="column is-four-fifths">
    @foreach($array_met_alle_offer_objecten as $obj)
    <hr>
    {{ var_dump($obj) }}
    @endforeach

  </div>
</div>
@endsection
