@extends('layouts.app')
@section('pageTitle', 'Overzicht Product Atributen')
@section('content')
<div class="container column is-6 pull-left">
    <div class="container">
        @if ($products->count() === 0)
            <div class="notification is-danger">Er zijn nog geen producten gedownload</div>
        @endif

        @if(Session::has('flash_message'))
            <div class="notification is-danger">{{ Session::get('flash_message') }}</div>
        @endif
    </div>
    @foreach($products as $product)
    <a href="{{route('productAttributes.create', $product->id) }}" class="button is-danger is-pulled-right"><i class="fa fa-plus m-r-10"></i>Nieuw Atribuut Aanmaken</a>
    <p class="is-size-4 has-text-weight-bold"> Product: {{ $product->productName }}</p><br>
        <table class="table table is-fullwidth">
            <thead>
                <tr>
                    <th>Atribuut</th>
                    <th>Waarde</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    @foreach($product->attributes as $attribute)
                        <tr>
                            <td>{{ $attribute->product_attribute_key }}</td>
                            <td>{{ $attribute->attrValues->attr_value }}</td>
                            <td><a class="button is-hovered is-small" href="{{ route('productAttributes.edit', $attribute->id) }}">Edit</a></td>
                        </tr>
                    @endforeach
                </tr>
            </tbody>
        </table>
    @endforeach
</div>
@endsection
