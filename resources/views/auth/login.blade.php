@extends('layouts.app')

@section('content')

<div class="columns">
    <div class="column is-one-third is-offset-one-third m-t-100">
        <div class="card">
            <div class="card-content">
                <h1 class="title">Log In</h1>
                <form action="{{ route('login') }}" method="POST" role="form">
                    {{ csrf_field() }}
                    
                    <div class="field">
                        <label 
                            for="email" 
                            class="label">Emailadres
                        </label>
                        <p class="control">
                            <input 
                                type="text" 
                                class="input {{ $errors->has('email') ? 'is-danger' : '' }}" 
                                name="email" 
                                id="email" 
                                placeholder="name@example.com" 
                                value="{{ old('email') }}">
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
                                id="password">
                        </p>
                        @if ($errors->has('password'))
                            <p class="help is-danger">Wachtwoord is vereist</p>
                        @endif
                    </div>

                    <button class="button is-primary is-outlined is-fullwidth m-t-30">Log In</button>
                </form>
            </div>
        </div>
        <h5 class="has-text-centered m-t-15"><a href="{{ route('password.request') }}" class="is-muted">Wachtwoord vergeten?</a></h5>
    </div>
</div>

@endsection
