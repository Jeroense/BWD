@extends('layouts.app')
@section('pageTitle', 'Publiceer offers op BOL')
@section('content')



<div class="container column is-10 pull-left">
    <div class="columns m-t-5">

    </div>
    <hr class="m-t-0">
    <div class="card">
            {{-- class="card"    --}}
        <div class="card-content">
                {{-- class="card-content" --}}

            @if( count($cvars) == 0 )
                <p>Nog geen custom varianten aangemaakt voor publicatie op BOL. </p>
            @endif
            @if( count($cvars) > 0 )

            <form method="POST" action="{{ route('boloffers.publish.dump') }}">
                @csrf

            <table class="table is-narrow">
                <thead>
                <tr>
                    <th>Image</th>
                    {{-- <th>offerId</th> --}}
                    <th>EAN</th>
                    <th>Name</th>
                    <th>Size</th>
                    {{-- <th>OnHold</th> --}}
                    <th>Price</th>
                    <th>Fulfil</th>
                    <th>Del.Code</th>
                    <th>Stock</th>
                    <th>Hold?</th>
                    <th>Publish</th>
                    {{-- <th>Corr.Stock</th> --}}

                    {{-- <th>Condition</th> --}}
                    {{-- <th>Updated</th> --}}
                    {{-- <th>Err-Code</th> --}}
                    {{-- <th>Err-Mssg</th> --}}
                    {{-- <th>Actions</th> --}}
                </tr>
                </thead>
                <tbody>
                    @foreach ($cvars as $cvar)


                        <tr class="message">
                            @if($cvar->fileName)
                            {{-- onderstaande werkt! --}}
                            {{-- <td><img class="customVariants" src="/customVariants/{{ $offer->fileName }}" alt="Geen image!" width="100"> </td> --}}

                            {{-- hieronder werkt ook! Maar er mogen  geen spaties in  scr="" staan, in: }}/{{ --}}
                            {{-- dus src=" {{ url('customVariants') }} / {{ $offer->fileName }}" werkt niet,  door de spaties in }} / {{ !! --}}
                            <td><img  src="{{ url('customVariants') }}/{{ $cvar->fileName }}" alt="Geen image!" width="140"> </td>

                            @else
                            <td> geen image! </td>
                            @endif


                            <td>{{ $cvar->ean }} </td>
                            @if($cvar->variantName)
                            <td>{{ $cvar->variantName }} </td>
                            @else
                            <td> onbekend! </td>
                            @endif

                            <td>{{ $cvar->size }} </td>

                            {{-- <td>{{ $cvar->onHoldByRetailer }}</td> --}}
                            <td>{{ $cvar->salePrice }}</td>

                            <td>FBR</td>


                            @isset($cvar->boldeliverycode)
                            <td>{{ $cvar->boldeliverycode }}</td>
                            @endisset

                            <input type="hidden" name="ean_{{$cvar->ean}}" value="{{$cvar->ean}}">
                            <input type="hidden" name="size_{{$cvar->ean}}" value="{{$cvar->size}}">

                            <td>
                                <div class="field">
                                    <div class="control">
                                    <input name="stockfor_{{$cvar->ean}}" class="input is-primary" type="number" placeholder="Stock" style="width: 100;" required>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <label class="checkbox">
                                    <input name="onhold_{{$cvar->ean}}" type="checkbox">
                                            Hold?
                                </label>
                            </td>

                            <td>
                                <label class="checkbox">
                                        <input name="publish_{{$cvar->ean}}" type="checkbox">
                                        Publish on BOL.
                                </label>
                            </td>


                        <input type="hidden" name="variantName_{{$cvar->ean}}" value="{{$cvar->variantName}}">
                        <input type="hidden" name="baseColor_{{$cvar->ean}}" value="{{$cvar->baseColor}}">
                        <input type="hidden" name="salePrice_{{$cvar->ean}}" value="{{$cvar->salePrice}}">
                        <input type="hidden" name="deliveryCode_{{$cvar->ean}}" value="{{$cvar->boldeliverycode}}">


                            {{-- <td class="has-text-right"> --}}
                                {{-- <a class="button is-hovered is-small m-r-5" href="{{ route('customers.show', $customer->id) }}">View</a> --}}
                                {{-- <a class="button is-hovered is-small" href="{{ route('customers.edit', $customer->id) }}">Edit</a> --}}

                                {{-- <a class="button is-fullwidth m-t-6" style="color: white; background-color:#003040;" onclick="return confirm('Weet je zeker dat je {{ $offer->ean }} wilt updaten?')"
                                href="{{ route('boloffers.twee') }}">Update</a>

                                <a class="button is-fullwidth is-danger  m-t-6 m-b-6"  onclick="return confirm('Weet je zeker dat je {{ $offer->ean }} wilt verwijderen?')"
                                href="{{ route('boloffers.twee') }}">Delete</a> --}}
                            {{-- </td> --}}
                        </tr>

                    @endforeach
                </tbody>
            </table>
            <button class="button" type="submit">Publish</button>
            </form>

            @endif
        </div>
    </div>
</div>


@endsection

@section('scripts')
<script>


</script>
@endsection





