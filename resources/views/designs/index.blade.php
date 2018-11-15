@extends('layouts.app')
@section('pageTitle', 'Overzicht Beschikbare Designs')
@section('content')
<div class="container column is-6 pull-left">
    <div class="columns m-t-5">
    </div>
    <div>
        <div class="container">
            @if ($images->count() === 0)
                <div class="notification is-danger">Er zijn nog geen designs ge-upload</div>
            @endif
            @if(Session::has('flash_message'))
                <div class="notification is-danger">{{ Session::get('flash_message') }}</div>
            @endif
        </div>
        <table class="table is-narrow">
            <tbody>
                @foreach ($images as $image)
                    <tr>
                        <td><img class="designImage" src="/designImages/{{ $image->fileName }}" alt="" width="100"></td>
                        <td>
                            <form method="post" action="{{ route('designs.destroy', $image->id) }}">
                                {!! csrf_field() !!}
                                {!! method_field('delete') !!}
                                <button class="button is-danger marginAuto">Delete</button>
                            </form>
                        </td>
                        <td><a href="" class="button is-danger marginAuto">Show</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection


