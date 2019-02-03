<div class="is-paddingless">
    <nav class="navbar has-text-white is-fixed-top">
        <div class="navbar-brand">
            <a class="m-t-5 m-r-20 m-l-5" href="{{ route('home') }}" >
                <img src="{{ asset('systemImages/logo-borduurwerkdeal.png') }}" width="219" alt="bwd logo"/>
            </a>

            {{-- @if((Request::segment(1) == 'manage')|(Request::segment(1) == 'products')) --}}
                <a href="#" class="navbar-item is-hidden-desktop" id="slideout-button">
                    <span class="icon"><i class="fa fa-arrow-circle-o-right"></i></span>
                </a>
            {{-- @endif --}}

            <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false">
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
              </a>
        </div>

        <div id="navMenu" class="navbar-menu">
            <div class="navbar-start">
                <a class="navbar-item navbar-title m-l-40" href="#">
                    @yield('pageTitle')
                </a>
            </div>
            <div class="navbar-item is-pulled-right is-invisible" id="shoppingCart">
                <span><i class="fas fa-cart-plus"></i></span>&nbsp;&nbsp;
                <span class="button is-small is-light" id="shoppingCartValue">0</span>&nbsp;&nbsp;
                <a class="button is-danger is-small" onclick='finalizeOrder()'>Bestelling afronden</a>
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
                            <a class="navbar-item dropdown-item" href="{{ route('logout') }}"
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
