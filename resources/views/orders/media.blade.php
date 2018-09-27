@extends('layouts.orders')

@section('content')
<div class="container">
    <div class="columns m-t-5">
        <div class="column">
            <h1 class="title">Overzicht Media</h1>
        </div> <!-- end of column -->

        <div class="column">
            {{-- <a href="{{ route('system.edit', $user->id) }}" class="button is-success is-pulled-right"><i class="fa fa-user m-r-10"></i> View System info</a> --}}
        </div>
    </div>
    <hr class="m-t-0">
</div>
@endsection
