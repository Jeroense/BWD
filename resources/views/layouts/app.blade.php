@include('layouts._main')
<body>
    @include('_includes.nav.main')
    <div id="app" class="mainContent">
        @yield('content')
    </div>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    @yield('scripts')
</body>
</html>
