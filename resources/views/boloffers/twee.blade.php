@extends('layouts.app')
@section('pageTitle', 'Test-page.')
@section('content')



<div class="container column is-10 pull-left">
    <div class="columns m-t-5">
        <div class="column">
            {{-- <a href="{{ route('customers.create') }}" class="button is-danger is-pulled-right"><i class="fa fa-user-plus m-r-10"></i>Nieuwe klant aanmaken</a> --}}
        </div>
    </div>
    <hr class="m-t-0">
    <div class="card">
        <div class="card-content">
                <p>Dit is boloffers twee</p>


               {{-- <a  href="{{ route('boloffer.is.csv.ready') }}" class="button is-danger "><i class="fa fa-user-plus m-r-10"></i>Check of export-offer file klaar is.</a> --}}



        </div>
    </div>
</div>


@endsection
