@extends('layouts.app')
@section('pageTitle', 'Nieuwe Gebruiker Aanmaken')
@section('content')
<div class="container column is-6 pull-left">
    <div class="columns m-t-5">
        <div class="column">
            <form action="{{route('users.store')}}" method="POST">
                {{csrf_field()}}
                <div class="field">
                    <label for="name" class="label">Name</label>
                    <p class="control">
                        <input type="text" class="input" name="name" id="name">
                    </p>
                </div>

                <div class="field">
                    <label for="email" class="label">Email:</label>
                    <p class="control">
                        <input type="text" class="input" name="email" id="email">
                    </p>
                </div>

                <div class="field">
                    <label for="password" class="label">Password</label>
                    <p class="control">
                        <input type="text" class="input" name="password" id="password" v-if="!auto_password" placeholder="Manually give a password to this user">
                        <b-checkbox type="is-danger" name="auto_generate" class="m-t-15" v-model="auto_password">Auto Generate Password</b-checkbox>
                    </p>
                </div>

                <button class="button is-danger">Create User</button>
            </form>
        </div>
    </div>

</div> <!-- end of .flex-container -->
@endsection

@section('scripts')
    <script>
            var app = new Vue({
            el: '#app',
            data: {
                auto_password: true
            }
            });
    </script>
@endsection
