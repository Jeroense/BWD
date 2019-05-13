@include('layouts._main')
<body>
    <div id="app" class="contentArea">
        @include('_includes.nav.main')
        @include('_includes.nav.sideMenu')
            <div class="column is-12 m-l-275">
            @yield('content')
        </div>
        <script src="{{ asset('js/app.js') }}"></script>
        {{-- @yield('scripts') --}}
    </div>
</body>

@yield('scripts')

<script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
</html>

