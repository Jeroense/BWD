@extends('layouts.app')
@section('pageTitle', 'Verzoek tot publicatie op BOL-api gedaan voor:')
@section('content')



<div class="container column is-10 pull-left">
    <div class="columns m-t-5">

    </div>
    <hr class="m-t-0">
    <div class="card">

        <div class="card-content">

                @if ($errors->any())
                    <div class="is-danger">
                        @foreach ($errors->all() as $error)
                            <a class="button is-danger m-t-6">{{ $error }}</a>
                        @endforeach
                    </div>
                @endif

            @if( count($sent) == 0 )
                <p>Geen custom varianten selected om te verzenden. </p>
            @endif
            @if( count($sent) > 0 )



            <table class="table is-narrow">
                <thead>
                <tr>
                    <th>Image</th>

                    <th>EAN</th>
                    <th>Name</th>
                    {{-- <th>Size</th> --}}

                    <th>Price</th>
                    {{-- <th>In Catalog</th> --}}
                    <th>Del.Code</th>
                    <th>Stock</th>
                    <th>Hold?</th>
                    {{-- <th>Publish</th> --}}

                </tr>
                </thead>
                <tbody>
                    @foreach ($sent as $se)


                        <tr class="message">
                            @if($se->fileName)
                            {{-- onderstaande werkt! --}}
                            {{-- <td><img class="customVariants" src="/customVariants/{{ $offer->fileName }}" alt="Geen image!" width="100"> </td> --}}

                            {{-- hieronder werkt ook! Maar er mogen  geen spaties in  scr="" staan, in: }}/{{ --}}
                            {{-- dus src=" {{ url('customVariants') }} / {{ $offer->fileName }}" werkt niet,  door de spaties in }} / {{ !! --}}
                            <td><img  src="{{ url('customVariants') }}/{{ $se->fileName }}" alt="Geen image!" width="140"> </td>

                            @else
                            <td> geen image! </td>
                            @endif

                            <td>{{ $se->ean }} </td>

                            @if($se->unknownProductTitle)
                            <td>{{ $se->unknownProductTitle }} </td>
                            @else
                            <td> onbekend! </td>
                            @endif

                            <td>{{ $se->pricing->bundlePrices[0]->price }} </td>



                            <td>
                                    {{ $se->fulfilment->deliveryCode }}
                            </td>

                            <td>
                                    {{ $se->stock->amount }}
                            </td>



                            <td>
                                    {{ $se->onHoldByRetailer }}
                            </td>
                        </tr>

                    @endforeach
                </tbody>
            </table>
            <hr>
                <p>Het verwerken van deze opdracht door de BOL-api duurt enkele seconden.</p>
                <p>Met de 'Check status' button kun je controleren of de opdracht goed is aangekomen.</p>
            <hr>
            <a class="button is-danger is-outlined" href="{{ route('boloffers.offer.checkinitialstatus') }}" >Check status</a>


            @endif



        </div>
    </div>
</div>



@endsection

@section('scripts')
<script>


</script>
@endsection

