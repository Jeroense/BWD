@extends('layouts.app')
@section('pageTitle', 'Overzicht Gebruikers')
@section('content')
    <div class="container column is-6 pull-left">
        <div class="columns m-t-5">
            <div class="column">
                <a href="{{route('users.create')}}" class="button is-danger is-pulled-right"><i class="fa fa-user-plus m-r-10"></i>Nieuwe gebruiker aanmaken</a>
            </div>
        </div>
        <hr class="m-t-0">
        <div class="card">
            <div class="card-content">
                <table class="table is-narrow">
                    <thead>
                    <tr>
                        <th>id</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Date Created</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <th>{{$user->id}}</th>
                                <td>{{$user->name}}</td>
                                <td>{{$user->email}}</td>
                                <td>{{$user->created_at->toFormattedDateString()}}</td>
                                <td class="has-text-right">
                                    <a class="button is-hovered is-small m-r-5" href="{{route('users.show', $user->id)}}">View</a>
                                    <a class="button is-hovered is-small" href="{{route('users.edit', $user->id)}}">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
      {{$users->links()}}
    </div>
@endsection
