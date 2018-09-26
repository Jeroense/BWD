@extends('layouts.manage')

@section('content')
<div class="container">
    <div class="columns m-t-5">
        <div class="column">
            <h1 class="title">View System Info</h1>
        </div> <!-- end of column -->

        <div class="column">
            <a href="{{ route('system.edit', $user->id) }}" class="button is-success is-pulled-right"><i class="fa fa-user m-r-10"></i> View System info</a>
        </div>
    </div>
    <hr class="m-t-0">

    <div class="columns">
        <div class="column">
            <div class="field">
                <label for="name" class="label">Name</label>
                <p class="m-l-15">{{ $user->name }}</p>
            </div>

            <div class="field">
                    <label for="name" class="label">Email</label>
                    <p class="m-l-15">{{ $user->email }}</p>
                </div>

            <div class="field">
                <div class="field">
                    <label for="roles" class="label">Roles</label>
                    <ul>
                        <p class="m-l-15">{{ $user->roles->count() == 0 ? 'This user has not been assigned any roles yet' : ''}}</p>

                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
