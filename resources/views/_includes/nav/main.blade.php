<div class="is-paddingless">
    <nav class="navbar is-success is-fixed-top">
        <div class="navbar-brand">
            <a class="m-t-5 m-l-20 m-r-20" href="{{ route('home') }}" >
                <img src="{{ asset('images/logo-borduurwerkdeal.png') }}" width="204" height="45" alt="bwd logo"/>
            </a>

            @if((Request::segment(1) == 'manage')|(Request::segment(1) == 'products'))
                <a href="#" class="navbar-item is-hidden-desktop" id="slideout-button">
                    <span class="icon"><i class="fa fa-arrow-circle-o-right"></i></span>
                </a>
            @endif

            <div class="navbar-burger burger" data-target="navMenu">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>

        <div id="navMenu" class="navbar-menu">

            <div class="navbar-start">
                @role('superadministrator')
                    <a class="navbar-item is-hoverable" href="{{ route('manage.dashboard') }}">
                        Admin
                    </a>
                @endrole

                <a class="navbar-item is-hoverable" href="#">
                    Orders
                </a>

                <a class="navbar-item is-hoverable" href="{{ route('products.dashboard') }}">
                    Artikelen
                </a>

                <a class="navbar-item is-hoverable" href="{{ route('design.dashboard') }}">
                        Designs
                    </a>

                <a class="navbar-item is-hoverable" href="#">
                    Overzichten
                </a>
            </div>

            <div class="navbar-end">
                @if (Auth::check())
                    <div class="navbar-item has-dropdown is-hoverable">
                        <a class="navbar-link" href="#">
                            {{ Auth::user()->name }}
                        </a>
                        <div class="navbar-dropdown">
                            <a class="navbar-item" href="#">
                                Profiel
                            </a>
                            <hr class="navbar-divider">
                            <a class="dropdown-item" href="{{ route('logout') }}"
                                onclick="event.preventDefault();
                                    document.getElementById('logout-form').submit();">
                                {{ __('Logout') }}
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </div>
                    </div>
                @else
                    <div class="navbar-item has-dropdown is-hoverable">
                        <a class="navbar-link" href="#">
                            Niet Ingelogd
                        </a>
                        <div class="navbar-dropdown">
                            <a class="navbar-item" href="#">
                                Login
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </nav>
</div>
