@include('layouts._main')
<body>
    <div id="app">
        @include('_includes.nav.main')
        @include('_includes.nav.orderSide')
        @yield('content')
        <script src="{{ asset('js/app.js') }}"></script>
        {{-- @yield('scripts') --}}
    </div>
</body>
</html>
