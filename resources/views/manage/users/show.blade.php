@extends('layouts.app')
@section('pageTitle', 'Gebruiker Details')
@section('content')
<div class="container column is-6 pull-left">


    <div class="columns">
        <div class="column">
            <div class="field">
                <label for="name" class="label">Naam</label>
                <p class="m-l-15">{{ $user->name }}</p>
            </div>

            <div class="field">
                    <label for="name" class="label">Email</label>
                    <p class="m-l-15">{{ $user->email }}</p>
                </div>

            <div class="field">
                <div class="field">
                    <label for="roles" class="label">Rollen</label>
                    <ul>
                        <p class="m-l-15">{{ $user->roles->count() == 0 ? 'This user has not been assigned any roles yet' : ''}}</p>
                        @foreach($user->roles as $role)
                            <li class="m-l-15">{{ $role->display_name }} - <em>({{ $role->description }})</em></li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <hr class="m-t-0">
    <div class="columns m-t-5">
        <div class="column">
            <a href="{{ route('users.edit', $user->id) }}" class="button is-danger is-pulled-right"><i class="fa fa-user m-r-10"></i> Edit User</a>
        </div>
    </div>

</div>
@endsection
