@extends('layouts.app')
@section('content')
<div class="container">
    <div class="columns m-t-5">
        <div class="column">
            <h1 class="title">Afbeelding koppelen aan standaard varianten</h1>
            <hr/>
            <form action="{{ route('products.attachImage') }}" method="POST" enctype="multipart/form-data">
                {{csrf_field()}}
                <div class="field">
                    <label for="tShirtColor">Kies kleur tshirt: </label>
                    <div class="control">
                        <select name="tShirtColor">
                            @foreach($colors as $color)
                            {{ $color }}
                                <option value='{{ $color }}'>{{ $color }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="field">
                    <label for="imageName">Kies Afbeelding: </label>
                    <div class="control">
                        <input type='file' name="imageName" onchange='getImage(this);' accept=".jpg, .jpeg, .png" />
                    </div>
                </div>
                <div class="field">
                    <img class="hideDetail" id="uploadedImage" src="#" alt="tshirt" />
                </div>
                <div class="control">
                    <input type="submit" id="uploadFile" class="button is-danger is-small" value="save">
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
