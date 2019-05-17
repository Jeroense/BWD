@extends('layouts.app')
@section('pageTitle', 'Opdracht tot verwijderen van offer aan BOL.')
@section('content')



<div class="container column is-10 pull-left">
    <div class="columns m-t-5">
        <div class="column">

        </div>
    </div>
    <hr class="m-t-0">
    <div class="card">
        <div class="card-content">
        <p>Opdracht tot verwijderen van Offer met offer-ID: {{ $offer->offerId }} , en EAN: {{ $offer->ean }} aan BOL doorgegeven.</p>






        </div>
    </div>
</div>


@endsection
