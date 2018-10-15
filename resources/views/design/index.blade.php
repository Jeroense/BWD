@extends('layouts.design')

@section('content')
<div class="container">
    <div class="columns m-t-5">
        <div class="column">
            <h1 class="title">Designs</h1>
            <hr>
        </div> <!-- end of column -->
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

