@extends('layouts.app')
@section('pageTitle', 'Nieuwe Custom Varianten')
@section('content')
    <div class="card column is-5">
        <div class="card-content">
            @if(Session::has('flash_message'))
                <div class="notification is-danger">{{ Session::get('flash_message') }}</div>
            @endif
            <table class="table">
                <tbody>
                    @if ($customVariants->count() !== 0)
                        @foreach ($customVariants as $customVariant)
                            <tr>
                                <td><p style="max-width: 250px;" class="imageHead is-size-5 has-text-centered has-text-weight-bold m-t-10 m-b-10">{{$customVariant->designName}}</p>
                                    <img src="{{url('/customVariants')}}/{{$customVariant->fileName}}" width="250"></td>
                                <td>
                                    <p><a class="button is-danger is-outlined is-pulled-left m-b-5 m-l-50 is-fullwidth"
                                        href="#">Publiceren op Bol.com</a></p>
                                    <form action="{{ route('customvariants.orderVariant', $customVariant->id) }}" method="post">
                                        {!! csrf_field() !!}
                                        <p><button type="submit" class="button is-danger is-outlined is-pulled-left m-b-5 m-l-50 is-fullwidth">Direct bestellen</button></p>
                                    </form>

                                </td>
                            </tr>
                        @endforeach
                    @else
                        <p class="is-size-3 has-text-danger has-text-centered has-text-weight-semibold">Nog geen nieuwe varianten aangemaakt!!</p>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
@endsection

