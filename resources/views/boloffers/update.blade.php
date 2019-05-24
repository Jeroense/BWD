@extends('layouts.app')
@section('pageTitle', 'offer op BOL updaten.')
@section('content')



<div class="container column is-10 pull-left">
    <div class="columns m-t-5">
        <div class="column">

        </div>
    </div>
    <hr class="m-t-0">
    <div class="card">
        <div class="card-content">
        <p><strong>Updaten van Offer met offer-ID: {{ $offer->offerId }} , en EAN: {{ $offer->ean }}.</strong></p>

        <div class="columns m-t-5">
                <div class="column">

                </div>
        </div>

        <form method="POST" action="{{ route('boloffers.offer.updated') }}">
            @csrf

        <table class="table is-narrow">
            <thead>
            <tr>
                <th>Image</th>

                <th>EAN</th>
                <th>Name</th>

                <th>Price</th>
                <th>Fulfil</th>
                <th>Del.Code</th>
                <th>Stock</th>
                <th>Hold?</th>

            </tr>
            </thead>
            <tbody>



                    <tr class="message">
                        @if($offer->fileName)

                        <td><img  src="{{ url('customVariants') }}/{{ $offer->fileName }}" alt="Geen image!" width="140"> </td>

                        @else
                        <td> geen image! </td>
                        @endif


                        <td>{{ $offer->ean }} </td>

                        @if($offer->unknownProductTitle)
                            <td>{{ $offer->unknownProductTitle }} </td>
                        @else
                            <td> onbekend! </td>
                        @endif




                        <td>
                            <div class="field">
                                <div class="control">
                                    <input type="number" name="salePrice_{{$offer->ean}}" value="{{$offer->bundlePricesPrice}}"
                                     class="input is-primary" style="width: 100px" step="0.01" required>
                                </div>
                            </div>
                        </td>

                        <td>FBR</td>


                        @isset($offer->fulfilmentDeliveryCode)

                            <td>
                            <div class="field">
                                <div class="control">
                                <div class="select is-primary">
                                    <select name="deliveryCode_{{$offer->ean}}">
                                    <option>3-5d</option>
                                    <option>1-2d</option>
                                    <option>2-3d</option>
                                    <option>4-8d</option>
                                    <option>1-8d</option>
                                    </select>
                                </div>
                                </div>
                            </div>
                            </td>
                        @endisset

                        <input type="hidden" name="ean_{{$offer->ean}}" value="{{$offer->ean}}">


                        <td>
                            <div class="field">
                                <div class="control">
                                    <input name="stockfor_{{$offer->ean}}" class="input is-primary" type="number" placeholder="Stock"
                                     style="width: 100px;" value="{{ old('stockfor_' . $offer->ean)}}">
                                </div>

                            </div>

                        </td>

                        <td>
                            <div class="field">
                                <label class="checkbox">
                                    <input name="onhold_{{$offer->ean}}" type="checkbox" class="is-checkradio is-info" id="onholdcheckbox"
                                                                        {{-- @if( (old('onhold_' . $offer->ean)) ) checked @endif> --}}
                                    @if( $offer->onHoldByRetailer ) checked @endif>
                                        is on hold
                                </label>

                            </div>
                        </td>



                    <input type="hidden" name="variantName_{{$offer->ean}}" value="{{$offer->unknownProductTitle}}">





                        {{-- <td class="has-text-right"> --}}
                            {{-- <a class="button is-hovered is-small m-r-5" href="{{ route('customers.show', $customer->id) }}">View</a> --}}
                            {{-- <a class="button is-hovered is-small" href="{{ route('customers.edit', $customer->id) }}">Edit</a> --}}


                    </tr>


            </tbody>
        </table>
        <button class="button" type="submit" onclick="return confirm('offer {{$offer->ean}} updaten?')">Update</button>
        </form>

        </div>
    </div>
</div>


@endsection
