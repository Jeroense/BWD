@extends('layouts.app')
@section('pageTitle', 'Offers op BOL')
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
            @if( count($bol_produktie_offers) == 0 )
                <p>Geen Bol produktie offers in Lokale Database. </p>
            @endif
            @if( count($bol_produktie_offers) > 0 )
            <table class="table is-narrow">
                <thead>
                <tr>

                    <th>offerId</th>
                    <th>EAN</th>
                    <th>Name</th>
                    <th>OnHold</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>CorrectedStock</th>
                    <th>Fulfil</th>
                    <th>DeliveryCode</th>
                    <th>Condition</th>
                    <th>Updated</th>
                    <th>Err-Code</th>
                    <th>Err-Mssg</th>
                </tr>
                </thead>
                <tbody>
                    @foreach ($bol_produktie_offers as $offer)
                        <tr>

                            @if($offer->offerId)
                            <td>{{ $offer->offerId }} </td>
                            @else
                            <td> nog onbekend! </td>
                            @endif

                            <td>{{ $offer->ean }} </td>
                            @if($offer->unknownProductTitle)
                            <td>{{ $offer->unknownProductTitle }} </td>
                            @else
                            <td> onbekend! </td>
                            @endif
                            <td>{{ $offer->onHoldByRetailer }}</td>
                            <td>{{ $offer->bundlePricesPrice }}</td>
                            <td>{{ $offer->stockAmount }}</td>
                            @isset($offer->correctedStock)
                            <td>{{$offer->correctedStock}}</td>
                            @endisset
                            <td>{{ $offer->fulfilmentType }}</td>
                            <td>{{ $offer->fulfilmentDeliveryCode }}</td>
                            <td>{{ $offer->fulfilmentConditionName }}</td>
                            <td>{{ $offer->updated_at }}</td>
                            @isset($offer->notPublishableReasonsCode)
                            <td>{{$offer->notPublishableReasonsCode}}</td>
                            @endisset
                            @isset($offer->notPublishableReasonsDescription)
                            <td>{{$offer->notPublishableReasonsDescription}}</td>
                            @endisset
                            <td class="has-text-right">
                                {{-- <a class="button is-hovered is-small m-r-5" href="{{ route('customers.show', $customer->id) }}">View</a> --}}
                                {{-- <a class="button is-hovered is-small" href="{{ route('customers.edit', $customer->id) }}">Edit</a> --}}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>
</div>


@endsection
