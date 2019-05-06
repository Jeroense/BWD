@extends('layouts.app')
@section('pageTitle', 'Status Offers-export CSV-file.')
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
                <p>Is de Offer export CSV file aangemaakt door BOL?</p>
                <p>Als er SUCCESS staat bij onderstaande status is het aangemaakt.</p>

               {{-- <a  href="{{ route('boloffer.is.csv.ready') }}" class="button is-danger "><i class="fa fa-user-plus m-r-10"></i>Check of export-offer file klaar is.</a> --}}

            <table class="table is-narrow">
                <thead>
                <tr>
                    @isset($laatste_db_entry->process_status_id)
                    <th>Process status id</th>
                    @endisset
                    @isset($laatste_db_entry->entityId)
                    <th>Offer-export-Id</th>
                    @endisset

                    <th>EventType</th>
                    @isset($laatste_db_entry->description)
                    <th>Description </th>
                    @endisset

                    <th>Status</th>

                    @isset($laatste_db_entry->errorMessage)
                    <th>Error Message</th>
                    @endisset
                    <th>Link to self</th>
                    <th>Method to self</th>
                    <th>createTimestamp</th>
                    <th>updated At:</th>
                </tr>
                </thead>
                <tbody>

                        <tr>
                            @isset($laatste_db_entry->process_status_id)
                            <td>{{ $laatste_db_entry->process_status_id }}</td>
                            @endisset
                            @isset($laatste_db_entry->entityId)
                            <td>{{ $laatste_db_entry->entityId }} </td>
                            @endisset
                            <td>{{ $laatste_db_entry->eventType }}</td>
                            @isset($laatste_db_entry->description)
                            <td>{{ $laatste_db_entry->description }}</td>
                            @endisset
                            <td>{{ $laatste_db_entry->status }}</td>
                            @isset($laatste_db_entry->errorMessage)
                            <td>{{$laatste_db_entry->errorMessage}}</td>
                            @endisset
                            <td>{{ $laatste_db_entry->link_to_self }}</td>
                            <td>{{ $laatste_db_entry->method_to_self }}</td>
                            <td>{{ $laatste_db_entry->createTimestamp }}</td>
                            <td>{{ $laatste_db_entry->updated_at }}</td>

                            <td class="has-text-right">
                                {{-- <a class="button is-hovered is-small m-r-5" href="{{ route('customers.show', $customer->id) }}">View</a> --}}
                                {{-- <a class="button is-hovered is-small" href="{{ route('customers.edit', $customer->id) }}">Edit</a> --}}
                            </td>
                        </tr>

                </tbody>
            </table>

        </div>
    </div>
</div>


@endsection
