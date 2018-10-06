@include('layouts._main')
<body>
    <div id="app" class="contentArea">
        @include('_includes.nav.main')
        @include('_includes.nav.designSide')
        <div class="column is-10 is-offset-3">
            @yield('content')
        </div>
        <script src="{{ asset('js/app.js') }}"></script>
        @yield('scripts')
    </div>
</body>
</html>
