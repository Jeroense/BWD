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



        <table class="table is-narrow">
            <thead>
            <tr>
                <th>Image</th>

                <th>EAN</th>
                <th>Name</th>

                <th>Price</th>
                {{-- <th>Fulfil</th> --}}
                <th>Del.Code / OnHold</th>
                {{-- <th>Hold?</th> --}}
                <th>Stock</th>


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
                          <div class="box">
                            <form method="POST" action="{{ route('boloffers.offer.offerprice_updated', $offer) }}">
                                @csrf
                                    <div class="field">
                                        <div class="control">
                                            <input type="number" name="salePrice" value="{{$offer->bundlePricesPrice}}"
                                            class="input is-primary" style="width: 100px" step="0.01" required>
                                        </div>
                                    </div>
                                    <button class="button is-primary is-outlined" type="submit" onclick="return confirm('Prijs van offer {{$offer->ean}} updaten?')">Update</button>
                            </form>
                          </div>
                        </td>

                        {{-- <td>FBR</td> --}}


                        {{-- @isset($offer->fulfilmentDeliveryCode) --}}

                            <td>
                                <div class="box">
                                <form method="POST" action="{{ route('boloffers.offer.onhold_and_deliverycode_updated', $offer) }}">
                                    @csrf
                                    <div class="field">
                                        <div class="control">
                                            <div class="select is-primary">
                                                <select name="deliveryCode">
                                                <option>3-5d</option>
                                                <option>1-2d</option>
                                                <option>2-3d</option>
                                                <option>4-8d</option>
                                                <option>1-8d</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                            {{-- </td> --}}
                        {{-- @endisset --}}

                            {{-- <td> --}}
                                <div class="field">
                                    <label class="checkbox">
                                        <input name="onhold" type="checkbox" class=" " id="onholdcheckbox"

                                        @if( $offer->onHoldByRetailer ) checked @endif>
                                            is on hold
                                    </label>

                                </div>
                                    <button class="button is-primary is-outlined" type="submit" onclick="return confirm('Delivery code en Onhold van offer {{$offer->ean}} updaten?')">Update</button>
                                </form>
                            </div>
                            </td>



                        {{-- <input type="hidden" name="ean" value="{{$offer->ean}}"> --}}


                        <td>
                          <div class="box">
                            <form method="POST" action="{{ route('boloffers.offer.stock_updated', $offer) }}">
                                @csrf
                                    <div class="field">
                                        <div class="control">
                                            <input name="stock" class="input is-primary" type="number" placeholder="Stock"
                                            style="width: 100px;" value="{{ old('stockfor_' . $offer->ean)}}">
                                        </div>
                                    </div>
                                <button class="button is-primary is-outlined" type="submit" onclick="return confirm('Stock van offer {{$offer->ean}} updaten?')">Update</button>
                            </form>
                          </div>
                        </td>





                    {{-- <input type="hidden" name="variantName_{{$offer->ean}}" value="{{$offer->unknownProductTitle}}"> --}}

                    {{-- <input type="hidden" name="variantName" value="{{$offer->unknownProductTitle}}"> --}}




                        {{-- <td class="has-text-right"> --}}
                            {{-- <a class="button is-hovered is-small m-r-5" href="{{ route('customers.show', $customer->id) }}">View</a> --}}
                            {{-- <a class="button is-hovered is-small" href="{{ route('customers.edit', $customer->id) }}">Edit</a> --}}


                    </tr>


            </tbody>
        </table>


        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('code') && session('phrase'))
            <div class="alert alert-success">
                <p> HTTP of netwerk error:  </p>
               <p> {{ session('code') }}  {{ session('phrase') }} </p>
               <p> Er is iets mis met de internetverbinding of met de server van BOL. Probeer het later nog eens. </p>
            </div>
        @endif

    </div>
</div>


@endsection
