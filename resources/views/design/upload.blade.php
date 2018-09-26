@extends('layouts.design')
{{-- @section('scripts')
<script src="{{ asset('js/upload.js') }}"></script>
@endsection --}}

@section('content')
<div class="container">
    <div class="columns m-t-5">
        <div class="column">
            <h1 class="title">Design upload</h1>
            <hr/>
            <form>
                <div class="field">
                    <label for="imageFile"></label>
                    <input type="file" name="imageFile">
                </div>

                <input type="button" id="uploadFile" class=" button is-success is-small" value="save">
            </form>
        </div> <!-- end of column -->
    </div>
</div>


@endsection
