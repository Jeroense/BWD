@extends('layouts.app')
@section('pageTitle', 'Order Details')
@section('content')
    <div class="card column is-8">
        <div class="card-content">
            <table class="table">
                <tbody>
                    <tr>
                        <td><p style="max-width: 300px;" class="imageHead is-size-5 has-text-centered has-text-weight-bold m-t-10 m-b-10">{{$customVariant->designName}}</p>
                            <img src="{{url('/customVariants')}}/{{$customVariant->fileName}}" width="250">
                        </td>
                        <td width='700'>
                            <form action="{{ route('orders.checkOrder') }}" method="POST">
                                {{csrf_field()}}
                                <div class="column is-offset-2">
                                    <label for="roles" class="label is-size-5 has-text-weight-bold">Beschikbare maten:</label>
                                    @foreach ($AvailableSizesWithVariantIds as $key => $value)
                                        <input name="numberOfSizes"
                                               value="{{$loop->iteration}}"
                                               type="hidden">
                                        <input name="parentVariantIdAndSize{{$loop->iteration}}"
                                               value="{{ $key }},{{ $value }}"
                                               type="hidden">
                                        <div class="field">
                                            <div class="field has-addons inline">
                                                <div class="sizeSelect orderSize">
                                                    <input class='checkbox'
                                                           type="checkbox"
                                                           id="checkBox{{$loop->iteration}}">
                                                           <span class="is-size-6 has-text-weight-bold">&nbsp;&nbsp;{{ $value }}</span>
                                                </div>
                                                <div class="control">
                                                    <a class="button is-danger" onclick="AmountMin('{{ $loop->iteration }}')">
                                                        <p class="is-size-3 has-text-centered has-text-weight-bold p-b-9">-</p>
                                                    </a>
                                                </div>
                                                <div class="control">
                                                    <input class="input is-size-6 has-text-centered has-text-weight-bold"
                                                           id="orderAmount{{$loop->iteration}}"
                                                           name="orderAmount{{ $loop->iteration }}"
                                                           type="text"
                                                           placeholder="Aantal"
                                                           value="0">
                                                </div>
                                                <div class="control">
                                                    <a class="button is-danger" onclick="AmountPlus('{{ $loop->iteration }}')">
                                                        <p class="AmountControlPlus is-size-4 has-text-centered has-text-weight-bold p-b-5">+</p>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    <input name="designId"
                                           value="{{ $customVariant->id}}"
                                           type="hidden">
                                </div>
                                <button disabled id="bestelButton" class="button is-danger">Bestellen!!</button>
                            </form>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
@section('scripts')
<script src="{{ asset('js/orderDetail.js') }}"></script>
{{-- <script>
    var app = new Vue({
        el: '#app',
        data: {
            // checkboxCustom: ''
        }
    });

</script> --}}
@endsection

