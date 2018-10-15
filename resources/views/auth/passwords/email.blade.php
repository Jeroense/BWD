@extends('layouts.app')

@section('content')

<div class="columns">
    <div class="column is-one-third is-offset-one-third m-t-100">
        <div class="card">
            <div class="card-content">
                <h1 class="title">Wachtwoord Resetten</h1>
                <form action="{{ route('password.email') }}" method="POST" role="form">
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
                    
                    <button class="button is-danger is-outlined is-fullwidth m-t-30">Reset Wachtwoord</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
