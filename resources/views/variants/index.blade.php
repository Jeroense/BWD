@extends('layouts.app')
@section('pageTitle', 'Alle Varianten')
@section('content')
    <div class="card column is-5">
        <div class="card-content">
            @if(Session::has('flash_message'))
                <div class="notification is-danger">{{ Session::get('flash_message') }}</div>
            @endif
            <table class="table">
                <tbody>
                    @if ($compositeMediaDesigns->count() !== 0)
                        @foreach ($compositeMediaDesigns as $customVariant)
                            <tr>
                                <td><p style="max-width: 250px;" class="imageHead is-size-5 has-text-centered has-text-weight-bold m-t-10 m-b-10">{{$customVariant->designName}}</p>
                                    <img src="{{url('/customVariants')}}/{{$customVariant->fileName}}" width="250"></td>
                                <td>
                                    <p><a class="button is-danger is-outlined is-pulled-left m-b-5 m-l-50 is-fullwidth"
                                        href="{{route('variants.show', $customVariant->id)}}">Details</a></p>
                                    <form action="{{route('variants.destroy', $customVariant->id)}}" method="post">
                                        {!! method_field('delete') !!}
                                        {!! csrf_field() !!}
                                        <p><button type="submit" class="button is-danger is-outlined is-pulled-left m-b-5 m-l-50 is-fullwidth">Verwijderen</button></p>
                                    </form>
                                    <p><a class="button is-danger is-outlined is-pulled-left m-b-5 m-l-50 is-fullwidth"
                                        href="{{route('variants.selectSizes', $customVariant->id)}}">Maten selecteren</a></p>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <p class="is-size-3 has-text-danger has-text-centered has-text-weight-semibold">Nog geen varianten aangemaakt!!</p>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
@endsection

