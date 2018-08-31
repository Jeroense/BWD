@include('layouts._main')
<body>
    <div id="app" class="mainContent">
        @include('_includes.nav.main')
        @include('_includes.nav.adminSide')
        @yield('content')
        <script src="{{ asset('js/app.js') }}"></script>
        @yield('scripts')
    </div>
</body>
</html>
