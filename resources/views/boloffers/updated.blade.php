@extends('layouts.app')
@section('pageTitle', 'offer op BOL updaten.')
@section('content')



<div class="container column is-10 pull-left">
    <div class="columns m-t-5">
        <div class="column">

        </div>
    </div>
    <hr class="m-t-0">
    <div class="card">
        <div class="card-content">
        <p>Offer met offer-ID: {{ $offer->offerId }} , en EAN: {{ $offer->ean }} is aangepast op BOL.</p>





        </div>
    </div>
</div>


@endsection
