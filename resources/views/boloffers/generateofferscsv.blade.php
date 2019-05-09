@extends('layouts.app')
@section('pageTitle', 'Opdracht aanmaak Offers-export CSV-file aan BOL gegeven!')
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
                <p>Met onderstaande button kun je checken of de offer-export klaar is.</p>
                <p>Dit kan enkele minuten (meestal 5) duren, maar hangt af van de drukte op de BOL-server.</p>

               <a  href="{{ route('boloffer.is.csv.ready') }}" class="button is-danger "><i class="fa fa-user-plus m-r-10"></i>Check of export-offer file klaar is.</a>

            <table class="table is-narrow">
                <thead>
                <tr>

                    <th>Process status id</th>
                    <th>EntityId</th>
                    <th>EventType</th>
                    <th>Description </th>
                    <th>Status</th>
                    <th>createTimestamp</th>
                    <th>Error Message</th>

                </tr>
                </thead>
                <tbody>

                        <tr>
                            @if($process_status->process_status_id)
                            <td>{{ $process_status->process_status_id }}</td>
                            @else
                            <td>onbekend proces status id</td>
                            @endif
                            @if($process_status->entityId)
                            <td>{{ $process_status->entityId }} </td>
                            @else
                            <td>onbekend entityId </td>
                            @endif
                            <td>{{ $process_status->eventType }}</td>
                            @if($process_status->description)
                            <td>{{ $process_status->description }}</td>
                            @else
                            <td>no description</td>
                            @endif

                            <td>{{ $process_status->status }}</td>
                            <td>{{ $process_status->createTimestamp }}</td>
                            @isset($process_status->errorMessage)
                            <td>{{$process_status->errorMessage}}</td>
                            @endisset
                            {{-- <td>{{ $process_status->link_to_self }}</td> --}}
                            {{-- <td>{{ $process_status->method_to_self }}</td> --}}


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