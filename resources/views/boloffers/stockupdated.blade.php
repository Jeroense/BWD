@extends('layouts.app')
@section('pageTitle', 'stock-update request naar BOL verzonden.')
@section('content')



<div class="container column is-10 pull-left">
    <div class="columns m-t-5">
        <div class="column">

        </div>
    </div>
    <hr class="m-t-0">
    <div class="card">
        <div class="card-content">
        <p>Voor Offer met offer-ID: {{ $offer->offerId }} , en EAN: {{ $offer->ean }} is een verzoek aan BOL gedaan om de voorraad aan te passen.</p>
        <p>Verzoek aan BOL-api gedaan om de voorraad aan te passen naar: {{ $stock }}</p>
        <p>Dit kost BOL enige tijd, varierend van een paar seconden tot enige minuten als de serverbelasting hoog is.</p>
        <p>Daarom is niet (altijd) direct deze voorraad wijziging te zien op de 'Offers op BOL' pagina.</p>


        </div>
    <a type="button" class="button is-primary is-outlined" href="{{ route('boloffers.index') }}">Terug naar Offers op BOL</a>
    </div>
</div>


@endsection
