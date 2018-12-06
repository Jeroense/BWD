@extends('layouts.app')
@section('pageTitle', 'Overzicht Afmetingen')
@section('content')
    <div class="container column is-6 pull-left">
        <div class="columns m-t-5">
            <div class="column">
                <a href="{{route('metrics.create')}}" class="button is-danger is-pulled-right"><i class="fa fa-user-plus m-r-10"></i>Nieuwe Maat Toevoegen</a>
            </div>
        </div>
        <hr class="m-t-0">

        <div class="card">
            <div class="card-content">
                <table class="table is-narrow">
                    <thead>
                    <tr>
                        <th>Maat</th>
                        <th>Lengte</th>
                        <th>Acties</th>
                    </tr>
                    </thead>
                    <tbody>
                        @foreach ($tshirtMetrics as $tshirtMetric)
                            <tr>
                                <th>{{$tshirtMetric->size}}</th>
                                <td>{{$tshirtMetric->length_mm}}</td>
                                <td class="has-text-right">
                                    <form action="{{route('metrics.destroy', $tshirtMetric->id)}}" method="POST">
                                        {{csrf_field()}}
                                        {{method_field('DELETE')}}
                                        <a class="button is-hovered is-small" href="{{route('metrics.edit', $tshirtMetric->id)}}">Wijzigen</a>
                                        <button type="submit" class="button is-hovered is-small m-r-5">Verwijderen</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
      {{-- {{$users->links()}} --}}
    </div>
@endsection
