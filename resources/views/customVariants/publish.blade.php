@extends('layouts.app')
@section('pageTitle', 'Varianten Publiceren op Bol.com')
@section('content')
    <div class="card column is-7">
        <div class="card-content">
            @if(Session::has('flash_message'))
                <div class="notification is-danger">{{ Session::get('flash_message') }}</div>
            @endif
            <div>
                <a href="{{ route('customvariants.updateSalesPrice') }}" id="updatePrice" returnUrl="{{ route('customvariants.publish') }}"></a>
            </div>

            <div class="pull-right control">
                <div class="field">
                    <div class="control">
                        <div class="select is-danger">
                            <select id="mySelectBox" name="mySelectName" onchange="filter(value);">
                                <option value="all">Alle custom varianten</option>
                                <option value="published">Gepubliceerd op Bol.com</option>
                                <option value="unpublished">Niet gepubliceerd op Bol.com</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <table class="table">
                <br>
                <hr>
                <tbody>
                    @if ($customVariants->count() !== 0)
                        @foreach ($customVariants as $customVariant)
                            <tr class="orderForm" value="{{ $customVariant->isPublishedAtBol }}">
                                <td><p style="max-width: 350px;" class="imageHead is-size-6 has-text-centered has-text-weight-bold m-t-10 m-b-10">{{ $customVariant->designName }}
                                    <img src="{{url('/customVariants')}}/{{ $customVariant->filename }}" width="250"></p>
                                    <div class="has-text-centered has-text-weight-bold is-size-5 m-b-20">{{ $customVariant->variantName }}</div>
                                </td>
                                <td>
                                    <div class="has-text-centered m-b-20">
                                        @if ($customVariant->isPublishedAtBol == "published")
                                            <div class="tile">
                                                <div class="tile is-parent is-vertical m-l-10">
                                                    <article class="tile is-child notification is-danger is-outlined">
                                                        <p class="subtitle">Aanwezig op Bol.com</p>
                                                    </article>
                                                </div>
                                            </div>
                                        @endif
                                        <br>
                                        <div class="control m-l-20">Maat: <span class='has-text-weight-bold is-size-5'>{{ $customVariant->size }}</span></div>
                                    </div>
                                    <form class="form">
                                        {{-- <form class="form" action="{{ route('customvariants.initiatePublishing') }}" method="post" --}}
                                        {!! csrf_field() !!}
                                        <div>
                                            <div class="field is-horizontal m-l-20">
                                                <div class="field-label is-normal">
                                                    <label class="label">Verkoopprijs:</label>
                                                </div>
                                                <div class="field-body">
                                                    <div class="field">
                                                    <p class="control">
                                                        <input type="text"
                                                               class="input"
                                                               name="name"
                                                               id="name"
                                                               value="{{ $customVariant->salesprice }}"
                                                               onchange="persistPrice( {{ $customVariant->id }}, value, {{ $minimalSalesPrice }});">
                                                    </p>
                                                    </div>
                                                </div>
                                            </div>

                                            <button type="button"
                                                    id="orderButton"
                                                    class="buttonHidden button is-outlined is-danger is-pulled-left m-t-30 m-b-5 m-l-20"
                                                    onclick="publishItem({{ $customVariant->id }});">
                                                @if ($customVariant->isPublishedAtBol !== "published")
                                                    Publiceren op Bol.com
                                                @else
                                                    Verwijderen van Bol.com
                                                @endif

                                            </button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <p class="notification is-danger">Nog geen varianten aangemaakt!!</p>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
@endsection
@section('scripts')
<script src="{{ asset('js/filterVariants.js') }}"></script>
<script src="{{ asset('js/UpdateSalesPrices.js') }}"></script>
@endsection


