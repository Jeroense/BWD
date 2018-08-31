@extends('layouts.app')

@section('content')

@if (session('status'))
    <div class="notification is-success">
        {{ session('status') }}
    </div>
@endif

<div class="columns">
    <div class="column is-one-third is-offset-one-third m-t-100">
        <div class="card">
            <div class="card-content">
                <h1 class="title">Wachtwoord Reset</h1>
                <form action="{{ route('password.request') }}" method="POST" role="form">
                    {{ csrf_field() }}
                    <input type="token" name="token" value="{{ $token }}">

                    <div class="field">
                        <label 
                            for="email" 
                            class="label">Emailadres</label>
                        <p class="control">
                            <input 
                                type="text" 
                                class="input {{ $errors->has('email') ? 'is-danger' : '' }}" 
                                name="email" 
                                id="email" 
                                placeholder="name@example.com" 
                                value="{{ old('email') }}"
                                required>
                        </p>
                        @if ($errors->has('email'))
                            <p class="help is-danger">Emailadres is vereist</p>
                        @endif
                    </div>

                    <div class="field">
                        <label 
                            for="password" 
                            class="label">wachtwoord
                        </label>
                        <p class="control">
                            <input 
                                type="password" 
                                class="input {{ $errors->has('password') ? 'is-danger' : '' }}"
                                name="password" 
                                id="password"
                                required>
                        </p>
                        @if ($errors->has('password'))
                            <p class="help is-danger">Wachtwoord is vereist</p>
                        @endif
                    </div>

                    <div class="field">
                        <label 
                            for="passwordConfirm" 
                            class="label">wachtwoord bevestigen
                        </label>
                        <p class="control">
                            <input 
                                type="password" 
                                class="input {{ $errors->has('passwordConfirm') ? 'is-danger' : '' }}"
                                name="passwordConfirm" 
                                id="passwordConfirm"
                                required>
                        </p>
                        @if ($errors->has('password'))
                            <p class="help is-danger">Wachtwoord is vereist</p>
                        @endif
                    </div>

                    <button class="button is-primary is-outlined is-fullwidth m-t-30">Reset Wachtwoord</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
