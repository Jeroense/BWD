@extends('layouts.app')
@section('pageTitle', '***************')
@section('content')
    <div class="container m-t-20 is-pulled-left">
        <form action="" method="POST" enctype="multipart/form-data">
            {{csrf_field()}}
            <div class="field">
                <label for="tShirtColor">Kies basis T-shirt: </label>
                <div class="control">
                    <select name="tShirtColor">
                        @foreach($shirts as $shirt)
                        {{ $shirt->color }}
                            <option value='{{ $shirt->color  }}'>{{ $shirt->color  }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>

        <form action="" method="POST" enctype="multipart/form-data">
            {{csrf_field()}}
            <div class="field">
                <label for="designImage"></label>
                <input type="file" name="designImage" accept=".jpg, .jpeg, .png">
            </div>
            <input type="submit" id="uploadFile" class="button is-danger is-small" value="save">
        </form>

        <div id="container"></div>
        <script src="customization.js"></script>
        <input type="button" id="saveImage" value='Save Image'>
        <div class="columns">
            <div class="column">
                <form action="{{route('products.store')}}" method="POST">
                    {{csrf_field()}}
                    <button class="button is-danger">Product Opslaan</button>
                </form>
            </div>
        </div>
    </div>
    <script src="customization.js"></script>
@endsection

