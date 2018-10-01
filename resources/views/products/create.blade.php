@extends('layouts.products')

@section('content')
    <div class="container column is-offset-3">
        <div class="columns m-t-5">
            <div class="column">
                <h1 class="title">Nieuwe product</h1>
            </div>
        </div>
        <hr class="m-t-0">

        <div class="columns">
            <div class="column">
                <form action="{{route('products.store')}}" method="POST">
                    {{csrf_field()}}



                    <button class="button is-success">product Opslaan</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    {{-- <script>
        var app = new Vue({
            el: '#app',
            data: {
                permissionType: 'crud',
                resource: '',
                crudSelected: ['create', 'read', 'update', 'delete']
            },
            methods: {
                crudName: function(item) {
                    return item.substr(0,1).toUpperCase() + item.substr(1) + " " + app.resource.substr(0,1).toUpperCase() + app.resource.substr(1);
                },
                crudSlug: function(item) {
                    return item.toLowerCase() + "-" + app.resource.toLowerCase();
                },
                crudDescription: function(item) {
                    return "Allow a User to " + item.toUpperCase() + " a " + app.resource.substr(0,1).toUpperCase() + app.resource.substr(1);
                }
            }
        });
    </script> --}}
@endsection
