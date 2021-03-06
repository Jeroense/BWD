@extends('layouts.app')
@section('pageTitle', 'Upload Nieuw Pakbon Logo')
@section('content')
<div class="container column is-6 pull-left">
    <div class="columns m-t-5">
        <div class="column">
            <form action="{{ route('system.storeLogo') }}" enctype="multipart/form-data" class="dropzone" id="my-dropzone">
                {{ csrf_field() }}
                <div class="dz-message">
                    <div class="col-xs-8">
                        <div class="message">
                            <p>Sleep Logo naar hier of klik om folder te openen</p>
                        </div>
                    </div>
                </div>
                <div class="fallback">
                    <input type="file" name="file" multiple>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/dropzone.js') }}"></script>
@endsection
