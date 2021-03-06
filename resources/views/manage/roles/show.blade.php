@extends('layouts.app')
@section('pageTitle', 'Overzicht Rollen')
@section('content')
<div class="container column is-9 pull-left">
    <div class="columns m-t-5">
        <div class="column">
            <h1 class="title">{{ $role->display_name }}<small class="m-l-25"><em>({{ $role->name }})</em></small></h1>
            <h5 class="subtitle">{{ $role->description }}</h5>
        </div>
        <div class="column">
            <a href="{{ route('roles.edit', $role->id) }}" class="button is-danger is-pulled-right"><i class="fa fa-user-plus m-r-10"></i> Edit this Role</a>
        </div>
    </div>
    <hr class="m-t-0">

    <div class="columns">
        <div class="column">
            <div class="box">
                <article class="media">
                    <div class="media-content">
                        <h2 class="title">Permissions</h2>
                        <ul>
                            @foreach($role->permissions as $r)
                                <li>{{ $r->display_name }} <em class="m-l-15">({{ $r->description }})</em></li>
                            @endforeach
                        </ul>
                    </div>
                </article>
            </div>
        </div>
    </div>
</div>
@endsection
