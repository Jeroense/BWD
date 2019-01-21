@extends('layouts.app')
@section('pageTitle', 'Overzicht Klanten')
@section('content')
    <div class="container column is-6 pull-left">
        <div class="columns m-t-5">
            <div class="column">
                <a href="{{ route('customers.create') }}" class="button is-danger is-pulled-right"><i class="fa fa-user-plus m-r-10"></i>Nieuwe klant aanmaken</a>
            </div>
        </div>
        <hr class="m-t-0">
        <div class="card">
            <div class="card-content">
                <table class="table is-narrow">
                    <thead>
                    <tr>
                        <th>Voornaam</th>
                        <th>Achternaam</th>
                    </tr>
                    </thead>
                    <tbody>
                        @foreach ($customers as $customer)
                            <tr>
                                <td>{{ $customer->firstName }}</td>
                                <td>{{ $customer->lastName }}, {{ $customer->lnPrefix }}</td>
                                <td class="has-text-right">
                                    <a class="button is-hovered is-small m-r-5" href="{{ route('customers.show', $customer->id) }}">View</a>
                                    <a class="button is-hovered is-small" href="#">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
