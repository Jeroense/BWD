@extends('layouts.app')
@section('pageTitle', 'Product Attribuut Toevoegen')
@section('content')
<div class="container column is-6 pull-left">
    <div class="columns m-t-5">
        <div class="column">
            <form action="{{route('productAttributes.store') }}" method="POST">
                {{csrf_field()}}
                <input type="hidden" value="{{ $id }}" name="productId">
                <div class="field">
                    <label for="attribute_key" class="label">Naam Attribuut:</label>
                    <p class="control">
                        <input type="text" class="input productAttribute" name="attribute_key" id="attribute_key">
                    </p>
                </div>
                <div class="field">
                    <label for="attribute_value" class="label">Waarde Attribuut:</label>
                    <p class="control">
                        <textarea class="textarea productAttribute" name="attribute_value" id="attribute_value" rows="5" cols="33"></textarea>
                    </p>
                </div>
                <button disabled class="button is-danger" id="saveButton">Opslaan</button>
            </form>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script src="{{ asset('js/validateProductAttributes.js') }}"></script>
@endsection

