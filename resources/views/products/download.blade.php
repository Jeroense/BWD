@extends('layouts.app')
@section('pageTitle', 'Basis Producten Downloaden')
@section('content')
<div class="container column is-6 pull-left">
    <div class="columns">
        <div class="column">
            <article class="message is-warning">
                <div class="message-header">
                    <p>Producten Downloaden</p>
                    {{-- <button class="is-danger"><span class="icon has-text-warning"><i class="fas fa-exclamation-triangle"></i></span></button> --}}
                    <span class="tag is-danger title is-5">!</span>
                </div>
                <div class="message-body">
                    <p>Deze functie kopieert alle standaard producten van Smake naar de locale database en is alleen noodzakelijk wanneer de locale database nog niet gevuld is of wanneer er nieuwe 'standaard' producten bij zijn gekomen.</p>
                    <p>In alle andere gevallen is het af te raden om deze functie te gebruiken.</p>
                </div>
            </article>
            <div class="field">
                <a class="button is-danger downloadProducts"
                    onclick="return confirm('Weet je zeker dat je alle producten wil downloaden?')"
                    href="{{ route('products.download') }}">Download alle Smake producten</a>
            </div>
        </div>
    </div>
</div>
@endsection
