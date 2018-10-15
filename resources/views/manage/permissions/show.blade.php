@extends('layouts.app')
@section('pageTitle', 'Rechten Detail')
@section('content')
    <div class="container column is-6 pull-left">
        <div class="columns m-t-5">
            <div class="column">
                <a href="{{route('permissions.edit', $permission->id)}}" class="button is-danger is-pulled-right"><i class="fa fa-edit m-r-10"></i> Edit Permission</a>
            </div>
        </div>
        <hr class="m-t-0">
        <div class="columns">
            <div class="column">
                <div class="box">
                    <article class="media">
                        <div class="media-content">
                            <div class="content">
                                <p>
                                    <strong>{{$permission->display_name}}</strong> <small>{{$permission->name}}</small>
                                    <br>
                                    {{$permission->description}}
                                </p>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </div>
@endsection
