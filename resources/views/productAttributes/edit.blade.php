@extends('layouts.app')
@section('pageTitle', 'Product Attribuut Wijzigen')
@section('content')
<div class="container column is-6 pull-left">
    <div class="columns m-t-5">
        <div class="column">
            <form action="{{route('productAttributes.update', $attribute->id) }}" method="POST">
                {{method_field('PUT')}}
                {{csrf_field()}}
                <div class="field">
                    <label for="attribute_key" class="label">Naam Attribuut:</label>
                    <p class="control">
                        <input type="text" class="input productAttribute" name="attribute_key" id="attribute_key" value="{{ $attribute->product_attribute_key }}">
                    </p>
                </div>
                <div class="field">
                    <label for="attribute_value" class="label">Waarde Attribuut:</label>
                    <p class="control">
                        <textarea class="textarea productAttribute" name="attribute_value" id="attribute_value" rows="5" cols="33">{{ $attribute->attrValues->attr_value }}</textarea>
                    </p>
                </div>
                <button class="button is-danger" id="saveButton">Opslaan</button>
            </form>
        </div>
    </div>
</div>
@endsection

