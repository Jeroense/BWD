<div class="side-menu" id="side-menu">
    <aside class="menu">
        <div id="accordian">
            <ul>
                <li class="{{ Nav::isResource('manage') }}">
                    <h3><span class="fas fa-tasks"></span>Systeem Beheer</h3>
                    <ul>
                        <li class="{{ Nav::isRoute('system.index') }}"><a href="{{route('system.index')}}">Systeem Info</a></li>
                        <li class="{{ Nav::isRoute('users.index') }}
                                   {{ Nav::isRoute('users.create') }}
                                   {{ Nav::isRoute('users.edit') }}
                                   {{ Nav::isRoute('users.show') }}">
                                   <a href="{{route('users.index')}}">Gebruikers</a>
                        </li>
                        <li class="{{ Nav::isRoute('roles.index') }}
                                   {{ Nav::isRoute('roles.create') }}
                                   {{ Nav::isRoute('roles.edit') }}
                                   {{ Nav::isRoute('roles.show') }}">
                                   <a href="{{route('roles.index')}}">Rollen</a>
                        </li>
                        <li class="{{ Nav::isRoute('permissions.index') }}
                                   {{ Nav::isRoute('permissions.create') }}
                                   {{ Nav::isRoute('permissions.edit') }}
                                   {{ Nav::isRoute('permissions.show') }}">
                                   <a href="{{route('permissions.index')}}">Rechten</a>
                        </li>
                        <li class="{{ Nav::isRoute('products.download') }}"><a href="{{route('products.download')}}">download products</a></li>
                        <li class="{{ Nav::isRoute('products.uploadImage') }}"><a href="{{route('products.uploadImage')}}">Upload Tshirt foto's</a></li>
                    </ul>
                </li>
                <li class="{{ Nav::isResource('products') }}">
                    <h3><span class="far fa-edit"></span>Varianten</h3>
                    <ul>
                        <li class="{{ Nav::isRoute('#') }}"><a href="#">Variant Index</a></li>
                        <li class="{{ Nav::isRoute('products.create') }}"><a href="{{route('products.create')}}">Nieuwe Variant</a></li>

                    </ul>
                </li>
                <li class="{{ Nav::isResource('orders') }}">
                    <h3><span class="fas fa-cart-plus"></span>Orders</h3>
                    <ul>
                        <li><a href="#">Order Index</a></li>
                        <li><a href="#">Nieuwe Order</a></li>

                    </ul>
                </li>
                <li class="{{ Nav::isResource('summaries') }}">
                    <h3><span class="far fa-chart-bar"></span>Overzichten</h3>
                    <ul>
                        <li class="{{ Nav::isRoute('orders.index') }}"><a href="{{route('orders.index')}}">Openstaande Orders</a></li>
                        <li class="{{ Nav::isRoute('orders.index') }}"><a href="{{route('orders.index')}}">Orders per Periode</a></li>
                        <li class="{{ Nav::isRoute('orders.index') }}"><a href="{{route('orders.index')}}">Orders per Klanr</a></li>
                        <li class="{{ Nav::isRoute('orders.index') }}"><a href="{{route('orders.index')}}">Orders per Variant</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </aside>
</div>

<script src="http://thecodeplayer.com/uploads/js/prefixfree-1.0.7.js" type="text/javascript" type="text/javascript"></script>
<script src="http://thecodeplayer.com/uploads/js/jquery-1.7.1.min.js" type="text/javascript"></script>
