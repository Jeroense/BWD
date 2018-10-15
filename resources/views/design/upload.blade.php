@extends('layouts.design')
@section('content')
<div class="container">
    <div class="columns m-t-5">
        <div class="column">
            <h1 class="title">Design upload</h1>
            <hr/>
            <form action="{{ route('design.store') }}" method="POST" enctype="multipart/form-data">
                {{csrf_field()}}
                <div class="field">
                    <label for="designImage"></label>
                    <input type="file" name="designImage" accept=".jpg, .jpeg, .png">
                </div>
                <input type="submit" id="uploadFile" class="button is-danger is-small" value="save">
            </form>
        </div> <!-- end of column -->
    </div>
</div>
@endsection
