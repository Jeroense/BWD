@extends('layouts.app')
@section('pageTitle', 'Klant Wijzigen')
@section('content')
<div class="mainContent container column is-6 pull-left">
    <form action="{{ route('customers.update', $customer->id) }}" method="POST">
        {{method_field('PUT')}}
        {{csrf_field()}}
        <div class="columns">
            <div class="column">
                <div class="field">
                    <label for="firstName" class="label">Voornaam:</label>
                    <p class="control">
                        <input type="text" class="input" name="firstName" id="name" value="{{$customer->firstName}}">
                    </p>
                </div>
                <div class="field">
                    <label for="LastName" class="label">Achternaam:</label>
                    <p class="control">
                        <input type="text" class="input" name="email" id="LastName" value="{{$customer->lastName}}">
                    </p>
                </div>
            </div>
        </div>
        <div class="columns">
            <div class="column">
                <hr />
                <button class="button is-danger is-pulled-right" style="width: 250px;">Update Klant</button>
            </div>
        </div>
    </form>
</div>
@endsection

