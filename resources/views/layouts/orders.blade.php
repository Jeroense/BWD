@include('layouts._main')
<body>
    <div id="app" class="contentArea">
        @include('_includes.nav.main')
        @include('_includes.nav.orderSide')
        @yield('content')
        <script src="{{ asset('js/app.js') }}"></script>
        {{-- @yield('scripts') --}}
    </div>
</body>
</html>
