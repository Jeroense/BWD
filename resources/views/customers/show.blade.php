@extends('layouts.app')
@section('pageTitle', 'Klant Details')
@section('content')
<div class="container column is-6 pull-left">
    <div class="columns">
        <div class="column">
            <div class="field">
                <label for="name" class="label">Voornaam</label>
                <p class="m-l-15">{{ $customer->firstName }}</p>
            </div>

            <div class="field">
                <label for="name" class="label">Achternaam</label>
                <p class="m-l-15">{{ $customer->lastName }}</p>
            </div>
        </div>
    </div>
    <hr class="m-t-0">
    <div class="columns m-t-5">
        <div class="column">
            <a href="{{ route('customers.edit', $customer->id) }}" class="button is-danger is-pulled-right"><i class="fa fa-user m-r-10"></i> Edit User</a>
        </div>
    </div>

</div>
@endsection
