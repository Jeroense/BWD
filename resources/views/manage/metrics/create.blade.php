@extends('layouts.app')
@section('pageTitle', 'Afmeting Toevoegen')
@section('content')
<div class="container column is-6 pull-left">
    <div class="columns m-t-5">
        <div class="column">
            <form action="{{route('metrics.store')}}" method="POST">
                {{csrf_field()}}
                <div class="field">
                    <label for="size" class="label">Maat:</label>
                    <p class="control">
                        <input type="text" class="input" name="size" id="size">
                    </p>
                </div>
                <div class="field">
                    <label for="length" class="label">Lengte (mm):</label>
                    <p class="control">
                        <input type="text" class="input" name="length" id="length">
                    </p>
                </div>
                <button class="button is-danger">Opslaan</button>
            </form>
        </div>
    </div>
</div>
@endsection

