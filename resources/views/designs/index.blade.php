@extends('layouts.app')
@section('pageTitle', 'Overzicht Beschikbare Designs')
@section('content')
<div class="container column is-6 pull-left">
    <div class="columns m-t-5">
    </div>
    <div>
        <table class="table is-narrow">
            <tbody>
                @foreach ($images as $image)
                    <tr>
                        <td><img class="designImage" src="/designImages/{{ $image->fileName }}" alt="" width="100"></td>
                        <td><a href="#" class="button is-danger marginAuto">Delete</a></td>
                        <td><a href="#" class="button is-danger marginAuto">Show</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection


