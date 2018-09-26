
{{-- @include('layouts._main')
<body>
    <div id="app" class="mainContent">
        @include('_includes.nav.main')
        @include('_includes.nav.designSide')
        <div class="column is-10 is-offset-3">
            @yield('content')
        </div>
    </div>
    {{-- <script src="{{ asset('js/upload.js') }}"></script> --}}
    {{-- <script src="{{ asset('js/app.js') }}"></script> --}}

    {{-- @yield('scripts') --}}
{{-- </body>
</html> --}}

{{-- @include('layouts._main')
<body>
    <div id="app" class="mainContent">
        @include('_includes.nav.main')
        @include('_includes.nav.designSide')
        @yield('content')
        <script src="{{ asset('js/app.js') }}"></script>
        @yield('scripts')
    </div>
</body>
</html> --}}


@include('layouts._main')
<body>
    <div id="app">
        @include('_includes.nav.main')
        @include('_includes.nav.designSide')
        @yield('content')
        <script src="{{ asset('js/app.js') }}"></script>
        @yield('scripts')
    </div>
</body>
</html>
