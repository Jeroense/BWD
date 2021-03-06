@extends('layouts.app')
@section('pageTitle', 'Overzicht Rechten')
@section('content')
    <div class="container column is-6 pull-left">
        <div class="columns m-t-5">
            <div class="column">
                <a href="{{route('permissions.create')}}" class="button is-danger is-pulled-right"><i class="fa fa-user-plus m-r-10"></i> Create New Permission</a>
            </div>
        </div>
        <hr class="m-t-0">
        <div class="card">
            <div class="card-content">
                <table class="table is-narrow">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Description</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($permissions as $permission)
                        <tr>
                            <th>{{$permission->display_name}}</th>
                            <td>{{$permission->name}}</td>
                            <td>{{$permission->description}}</td>
                            <td class="has-text-right">
                                <a class="button is-hovered is-small m-r-5" href="{{route('permissions.show', $permission->id)}}">View</a>
                                <a class="button is-hovered is-small" href="{{route('permissions.edit', $permission->id)}}">Edit</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
