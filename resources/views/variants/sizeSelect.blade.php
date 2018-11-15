@extends('layouts.app')
@section('pageTitle', 'Maten Selecteren')
@section('content')
    <div class="card column is-9">
        <div class="card-content">
            <table class="table">
                <tbody>
                    <tr>
                        <td><p style="max-width: 300px;" class="imageHead is-size-5 has-text-centered has-text-weight-bold m-t-10 m-b-10">{{$customVariant->designName}}</p>
                            <img src="{{url('/customVariants')}}/{{$customVariant->fileName}}" width="250">
                        </td>
                        <td width='700'>
                            <form action="{{ route('customVariants.createVariant') }}" method="POST">
                                {{csrf_field()}}
                                <div class="column is-offset-2">
                                    <label for="roles" class="label is-size-5 has-text-weight-bold">Beschikbare maten:</label>
                                    @foreach ($AvailableSizesWithVariantIds as $key => $value)
                                        <input name="numberOfSizes"
                                               value="{{$loop->iteration}}"
                                               type="hidden">
                                        <input name="parentVariantId{{$loop->iteration}}"
                                               value="{{ $key }}"
                                               type="hidden">
                                        <input name="Size{{$loop->iteration}}"
                                               value="{{ $value }}"
                                               type="hidden">
                                        <div class="field">
                                            <div class="field has-addons inline">
                                                <div class="sizeSelect orderSize">
                                                    <input class='checkbox'
                                                           type="checkbox"
                                                           id="checkBox{{$loop->iteration}}"
                                                           onclick="ToggleDisabled('{{ $loop->iteration }}')">
                                                           <span class="is-size-6 has-text-weight-bold">&nbsp;&nbsp;{{ $value }}</span>
                                                </div>
                                                <div class="control">
                                                    <input class="input ean is-size-6 has-text-centered has-text-weight-bold p-r-15" disabled
                                                            id="ean{{$loop->iteration}}"
                                                            name="ean{{ $loop->iteration }}"
                                                            type="text"
                                                            placeholder="Ean nummer">
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    <input name="compositeMediaId"
                                           value="{{ $customVariant->id}}"
                                           type="hidden">
                                </div>
                                <button disabled id="save" class="button is-danger">Opslaan</button>
                            </form>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
@section('scripts')
<script src="{{ asset('js/sizeSelect.js') }}"></script>
{{-- <script>
    var app = new Vue({
        el: '#app',
        data: {
            // checkboxCustom: ''
        }
    });

</script> --}}
@endsection
