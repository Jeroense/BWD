@extends('layouts.app')
@section('pageTitle', 'Afbeelding koppelen aan basis varianten')
@section('content')
<div class="container column is-9 pull-left">
    <div class="columns m-t-5">
        <div class="column">
            <form action="{{ route('products.attachImage') }}" method="POST" enctype="multipart/form-data">
                {{csrf_field()}}
                <div class="field field is-horizontal">
                    <div class="field-label">
                        <label class="button btnLabel">Kies:</label>
                    </div>
                    <div class="field-body">
                        <div class="field">
                            <p class="control">
                                <div class="select is-danger">
                                    <select name="tShirtColor">
                                        @foreach($colors as $color)
                                            {{ $color }}
                                            <option value='{{ $color  }}'>
                                                {{ $color  }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <div class="file is-info has-name">
                    <label class="file-label">
                        {{-- <input class="file-input" type="file" name="imageName"> --}}
                        <input class="file-input is-danger" type='file' name="imageName" onchange='getImage(this);' width="48" accept=".jpg, .jpeg, .png" />
                        <span class="file-cta">
                            <span class="file-icon">
                                <i class="fas fa-upload"></i>
                            </span>
                            <span class="file-label">
                                Info file…
                            </span>
                        </span>
                        <span class="file-name">
                            Screen Shot 2017-07-29 at 15.54.25.png
                        </span>
                    </label>
                    </div>
                </div>




                {{-- <div class="field is-horizontal">
                    <div class="field-label is-normal">
                        <label class="label">Kies Afbeelding:</label>
                    </div>
                    <div class="field-body">
                        <div class="field">
                            <p class="control">
                                <input class="input is-danger" type='file' name="imageName" onchange='getImage(this);' width="48" accept=".jpg, .jpeg, .png" />
                            </p>
                        </div>
                    </div>
                </div> --}}
                <div class="field">
                    <img class="hideDetail" id="uploadedImage" src="#" alt="tshirt" />
                </div>
                <div class="control">
                    <input type="submit" id="uploadFile" class="button is-success is-small" value="save">
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
