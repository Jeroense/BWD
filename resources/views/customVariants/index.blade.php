@extends('layouts.app')
@section('pageTitle', 'Varianten Bestellen en Publiceren')
@section('content')
    <div class="card column is-7">
        <div class="card-content">
            @if(Session::has('flash_message'))
                <div class="notification is-danger">{{ Session::get('flash_message') }}</div>
            @endif
            <div><a href="{{ route('orders.store') }}" id="orderRoute" returnUrl="{{ route('customvariants.orderVariant', '') }}"></a>
                {{-- id="orderRoute" --}}
                {{-- returnUrl="{{ route('orders.create', '') }}" --}}




                {{-- customvariants.buildOrderObject --}}
                {{-- class="button is-danger m-b-100">Custom Variant Opslaan</a> --}}
        </div>
            <table class="table">
                <tbody>
                    @if ($customVariants->count() !== 0)
                        @foreach ($customVariants as $customVariant)
                            <tr>
                                <td><p style="max-width: 350px;" class="imageHead is-size-6 has-text-centered has-text-weight-bold m-t-10 m-b-10">{{$customVariant->designName}}
                                    <img src="{{url('/customVariants')}}/{{ $customVariant->fileName }}" width="250"></p>
                                    <div class="has-text-centered has-text-weight-bold is-size-5 m-b-20">{{ $customVariant->variantName }}</div>
                                </td>
                                <td>
                                    <div class="has-text-centered m-b-20">
                                        <p>Maat: <span class='has-text-weight-bold is-size-5'>{{ $customVariant->size }}</span></p>
                                    </div>
                                    <form class="form" action="{{ route('customvariants.orderVariant', $customVariant->id) }}" method="post">
                                        {!! csrf_field() !!}
                                        <div class="input-group plus-minus-input">
                                            <input id="variantId{{ $loop->iteration }}" type="hidden" value="{{ $customVariant->id }}">
                                            <div class="input-group-button is-danger is-outlined is-pulled-left m-b-15 m-l-50">
                                                <button type="button"
                                                        class="button hollow circle"
                                                        data-quantity="minus"
                                                        data-field="quantity"
                                                        onclick="AmountMin({{ $loop->iteration }});">
                                                    <i class="fa fa-minus" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                            <div class="input-group-field is-pulled-left">
                                                <input class="input-group-field is-size-6"
                                                    id="orderAmount{{ $loop->iteration }}"
                                                    name="orderAmount{{ $loop->iteration }}"
                                                    type="number"
                                                    size="2"
                                                    maxlength="4"
                                                    placeholder="Aantal"
                                                    {{-- value="0" --}}
                                                    onkeyup="checkAmount(value, {{ $loop->iteration }});">
                                            </div>
                                            <div class="input-group-button is-pulled-left">
                                                <button type="button"
                                                        class="button hollow circle"
                                                        data-quantity="plus"
                                                        data-field="quantity"
                                                        onclick="AmountPlus({{ $loop->iteration }});">
                                                    <i class="fa fa-plus" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div>
                                            <button type="button"
                                                     id="orderButton{{ $loop->iteration }}"
                                                     class="buttonHidden button is-normal is-outlined is-pulled-left m-t-25 m-b-5 m-l-50"
                                                     disabled
                                                     onclick="updateOrderItems({{ $loop->iteration }});">
                                                Bestellen
                                            </button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <p class="is-size-3 has-text-danger has-text-centered has-text-weight-semibold">Nog geen nieuwe varianten aangemaakt!!</p>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="{{ asset('js/createOrder.js') }}"></script>
@endsection


