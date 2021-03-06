@extends('layouts.app')
@section('pageTitle', 'Overzicht Rollen')
@section('content')
    <div class="container column is-9 pull-left">
        <div class="columns m-t-5">
            <div class="column">
                <a href="{{route('roles.create')}}" class="button is-danger is-pulled-right"><i class="fa fa-user-plus m-r-10"></i> Create New Role</a>
            </div>
        </div>
        <hr class="m-t-0">

        <div class="columns is-multiline">
            @foreach ($roles as $role)
                <div class="column is-one-third">
                    <div class="box">
                        <article class="media">
                            <div class="media-content">
                                <div class="content">
                                    <h3 class="title">{{ $role->display_name }}</h3>
                                    <h4 class="subtitle"><em>{{ $role->name }}</em></h4>
                                    <p>
                                        {{ $role->description }}
                                    </p>
                                </div>
                                <div class="columns is-mobile">
                                    <div class="column is-one-half">
                                        <a href="{{ route('roles.show', $role->id) }}" class="button is-danger is-fullwidth">Details</a>
                                    </div>
                                    <div class="column is-one-half">
                                        <a href="{{ route('roles.edit', $role->id) }}" class="button is-light is-danger is-outlined is-fullwidth">Edit</a>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
