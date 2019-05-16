@extends('layouts.app')
@section('pageTitle', 'Offers op BOL')
@section('content')



<div class="container pull-left">
        {{-- columns is-10 pull-left --}}
    <div class="columns m-t-5">

    {{-- </div> --}}
    <hr class="m-t-0">
    <div class="column is-four-fifth">
            {{-- class="card"    --}}
        {{-- <div > --}}
                {{-- class="card-content" --}}

            @if( count($bol_produktie_offers) == 0 )
                <p>Geen Bol produktie offers in Lokale Database. </p>
            @endif
            @if( count($bol_produktie_offers) > 0 )
            <table class="table is-narrow">
                <thead>
                <tr>
                    <th>Image</th>
                    <th>offerId</th>
                    <th>EAN</th>
                    <th>Name</th>
                    <th>OnHold</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Corr.Stock</th>
                    <th>Fulfil</th>
                    <th>Del.Code</th>
                    <th>Condition</th>
                    <th>Updated</th>
                    <th>Err-Code</th>
                    <th>Err-Mssg</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                    @foreach ($bol_produktie_offers as $offer)


                        <tr class="message">
                            @if($offer->fileName)
                            {{-- onderstaande werkt! --}}
                            {{-- <td><img class="customVariants" src="/customVariants/{{ $offer->fileName }}" alt="Geen image!" width="100"> </td> --}}

                            {{-- hieronder werkt ook! Maar er mogen  geen spaties in  scr="" staan, in: }}/{{ --}}
                            {{-- dus src=" {{ url('customVariants') }} / {{ $offer->fileName }}" werkt niet,  door de spaties in }} / {{ !! --}}
                            <td><img  src="{{ url('customVariants') }}/{{ $offer->fileName }}" alt="Geen image!" width="100"> </td>

                            @else
                            <td> geen image! </td>
                            @endif
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
                            <td class="publisherrorcode">{{$offer->notPublishableReasonsCode}}</td>
                            @endisset

                            @isset($offer->notPublishableReasonsDescription)
                            <td class="publisherrormessage">{{$offer->notPublishableReasonsDescription}}</td>
                            @endisset
                            <td class="has-text-right">
                                {{-- <a class="button is-hovered is-small m-r-5" href="{{ route('customers.show', $customer->id) }}">View</a> --}}
                                {{-- <a class="button is-hovered is-small" href="{{ route('customers.edit', $customer->id) }}">Edit</a> --}}

                                {{-- <a class="button is-fullwidth m-t-6" style="color: white; background-color:#003040;" onclick="return confirm('Weet je zeker dat je {{ $offer->ean }} wilt updaten?')"
                                href="{{ route('boloffers.twee') }}">Update</a>

                                <a class="button is-fullwidth is-danger  m-t-6 m-b-6"  onclick="return confirm('Weet je zeker dat je {{ $offer->ean }} wilt verwijderen?')"
                                href="{{ route('boloffers.twee') }}">Delete</a> --}}
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

@section('scripts')
<script>
    var tdElement = document.querySelectorAll(".publisherrorcode");
    tdElement.forEach(function(element){

        if(element.innerHTML != "0"){
            element.classList.add("is-dark")
        }
    });


    var tdElement2 = document.querySelectorAll(".publisherrormessage");
    tdElement2.forEach(function(element){

        if(element.innerHTML != "Is published. No errors!"){
            element.classList.add("is-dark");
        }
    });

</script>
@endsection
