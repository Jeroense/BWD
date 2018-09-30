@extends('layouts.variants')

@section('content')
<div class="container">
    <div class="columns m-t-5">
        <div class="column">
            <h1 class="title">Varianten</h1>
        </div> <!-- end of column -->

        <div class="column">
            {{-- <a href="{{ route('system.edit', $user->id) }}" class="button is-success is-pulled-right"><i class="fa fa-user m-r-10"></i> View System info</a> --}}
        </div>
    </div>
    <hr class="m-t-0">

    <div class="columns">
        <div class="column">
            <div class="field">
                <label for="name" class="label">Naam</label>
                @foreach($variants as $variant)
                    <p class="m-l-15">{{ $variant->title }}</p>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
