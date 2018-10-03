@extends('layouts.products')

@section('content')
    <div class="container column is-offset-3">
        <div class="columns m-t-5">
            <div class="column">
                <h1 class="title">Nieuwe product</h1>
            </div>
        </div>
        <hr class="m-t-0">
        <form action="" method="POST" enctype="multipart/form-data">
            {{csrf_field()}}
            <div class="field">
                <label class="inputLabel" for="baseImage">Kies een T-shirt</label>
                <input type="file" id="baseImage" name="baseImage" accept=".jpg, .jpeg, .png">
            </div>
            <input type="submit" id="uploadFile" class="button is-success is-small" value="save">
        </form>
        <form action="" method="POST" enctype="multipart/form-data">
            {{csrf_field()}}
            <div class="field">
                <label for="designImage"></label>
                <input type="file" name="designImage" accept=".jpg, .jpeg, .png">
            </div>
            <input type="submit" id="uploadFile" class="button is-success is-small" value="save">
        </form>

        <div id="container"></div>
        <script src="customization.js"></script>
    <input type="button" id="saveImage" value='Save Image'>



        <div class="columns">
            <div class="column">
                <form action="{{route('products.store')}}" method="POST">
                    {{csrf_field()}}



                    <button class="button is-success">product Opslaan</button>
                </form>
            </div>
        </div>
    </div>
    <script src="customization.js"></script>
@endsection

@section('scripts')

@endsection
