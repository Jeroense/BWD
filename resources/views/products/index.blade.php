@extends('layouts.products')

@section('content')
<div class="container column is-offset-3">
    <div class="columns m-t-5">
        <div class="column">
            <h1 class="title">Producten</h1>
        </div> <!-- end of column -->

        <div class="column">
        </div>
    </div>
    <hr class="m-t-0">
    <div class="columns">
        <div class="column">
            <div class="field">
                @foreach($products as $product)
                    <table style="width:80%">
                        <tr>
                            <th><strong>{{ $product->title }}</strong></td>
                        </tr>
                        <tr>
                            <td colspan="2">{{ $product->description }}</td>
                            <td><a href="javascript:void(0)" class="button is-success productDetail">Details</a></td>
                        </tr>
                        <tr class='test1'>
                            <td class="hideDetail">{{ $product->id }}</td>
                        </tr>
                    </table>
                    <hr>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
