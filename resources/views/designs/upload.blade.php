@extends('layouts.app')
@section('pageTitle', 'Upload Nieuw Design')
@section('content')
<div class="container column is-6 pull-left">
    <div class="columns m-t-5">
        <div class="column">
            {{-- <form action="{{ route('designs.store') }}" method="POST" enctype="multipart/form-data">
                {{csrf_field()}}
                <div class="field">
                    <label for="designImage"></label>
                    <input type="file" name="designImage" accept=".jpg, .jpeg, .png">
                </div>
                <button type="submit" id="uploadFile" class="button is-danger">Save</button>
            </form> --}}

            <form action="{{ route('designs.store') }}"
                  class="dropzone"
                  id="my-awesome-dropzone">
                {{-- <input type="file" name="file" /> --}}
            </form>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/dropzone.js') }}"></script>
@endsection
