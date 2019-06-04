<div class="side-menu" id="side-menu">
    <aside class="menu">
        <div id="accordian">
            <ul>
                <li class="{{ Nav::isResource('manage') }}">
                    <h3><span class="icon-color fas fa-tasks"></span>Systeem Beheer</h3>
                    <ul>
                        <li class="{{ Nav::isRoute('system.index') }}"><a href="{{route('system.index')}}">Systeem Info</a></li>
                        <li class="{{ Nav::isRoute('users.index') }}
                                   {{ Nav::isRoute('users.create') }}
                                   {{ Nav::isRoute('users.edit') }}
                                   {{ Nav::isRoute('users.show') }}"><a href="{{route('users.index')}}">Gebruikers</a>
                        </li>
                        <li class="{{ Nav::isRoute('roles.index') }}
                                   {{ Nav::isRoute('roles.create') }}
                                   {{ Nav::isRoute('roles.edit') }}
                                   {{ Nav::isRoute('roles.show') }}"><a href="{{route('roles.index')}}">Rollen</a>
                        </li>
                        <li class="{{ Nav::isRoute('permissions.index') }}
                                   {{ Nav::isRoute('permissions.create') }}
                                   {{ Nav::isRoute('permissions.edit') }}
                                   {{ Nav::isRoute('permissions.show') }}"><a href="{{route('permissions.index')}}">Rechten</a>
                        </li>
                        <li class="{{ Nav::isRoute('bwdBolMapping.index') }}"><a href="{{route('bwdBolMapping.index')}}">Content Mappings</a></li>
                        <li class="{{ Nav::isRoute('manage.backup') }}"><a href="{{route('manage.backup')}}">Backup</a></li>
                        <li class="{{ Nav::isRoute('manage.restore') }}"><a href="{{route('manage.restore')}}">Restore</a></li>
                        <li class="{{ Nav::isRoute('metrics.index') }}
                                   {{ Nav::isRoute('metrics.create') }}
                                   {{ Nav::isRoute('metrics.edit') }}
                                   {{ Nav::isRoute('metrics.destroy') }}"><a href="{{route('metrics.index')}}">T-shirt Afmetingen</a></li>
                        <li class="{{ Nav::isRoute('system.uploadLogo') }}"><a href="{{route('system.uploadLogo')}}">Upload pakbon Logo</a></li>
                    </ul>
                </li>
                <li class="{{ Nav::isResource('customers') }}">
                    <h3><span class="icon-color fas fa-user-check"></span>Klanten</h3>
                    <ul>
                        <li class="{{ Nav::isRoute('customers.index') }}"><a href="{{route('customers.index')}}">Klant Index</a></li>
                        <li class="{{ Nav::isRoute('customers.create') }}"><a href="{{route('customers.create')}}">Klant Toevoegen</a></li>
                    </ul>
                </li>
                <li class="{{ Nav::isResource('products') }}">
                    <h3><span class="icon-color fas fa-industry"></span>Smake</h3>
                    <ul>
                        <li class="{{ Nav::isRoute('products.productDownload') }}
                                   {{ Nav::isRoute('products.download') }}"><a href="{{route('products.productDownload')}}">Producten Downloaden</a></li>
                        <li class="{{ Nav::isRoute('productAttributes.index') }}"><a href="{{route('productAttributes.index')}}">Product Attributen</a></li>
                    </ul>
                </li>

                <li class="{{ Nav::isResource('designs') }}">
                    <h3><span class="icon-color far fa-edit"></span>Designs</h3>
                    <ul>
                        <li class="{{ Nav::isRoute('designs.index') }}"><a href="{{route('designs.index')}}">Design Index</a></li>
                        <li class="{{ Nav::isRoute('designs.upload') }}"><a href="{{route('designs.upload')}}">Designs Toevoegen</a></li>
                    </ul>
                </li>
                <li class="{{ Nav::isResource('variants', 'variants') }}">
                    <h3><span class="icon-color fas fa-fill-drip"></span>Custom Varianten</h3>
                    <ul>
                        <li class="{{ Nav::isRoute('variants.index') }}"><a href="{{route('variants.index')}}">Overzicht Custom Varianten</a></li>
                        <li class="{{ Nav::isRoute('variants.create') }}"><a href="{{route('variants.create')}}">Nieuwe Variant</a></li>
                    </ul>
                </li>
                <li class="{{ Nav::isResource('customvariants', 'customvariants') }}">
                    <h3><span class="icon-color fas fa-file-invoice"></span>Bestellen en Publiceren</h3>
                    <ul>
                        <li class="{{ Nav::isRoute('customvariants.index') }}"><a href="{{route('customvariants.index')}}">Bestellen</a></li>
                        <li class="{{ Nav::isRoute('customvariants.publish') }}"><a href="{{route('customvariants.publish')}}">Publiceren op Bol.com</a></li>
                    </ul>
                </li>
                <li class="{{ Nav::isResource('orders') }}">
                    <h3><span class="icon-color fas fa-cart-plus"></span>Orders</h3>
                    <ul>
                        <li class="{{ Nav::isRoute('orders.index') }}"><a href="{{route('orders.index')}}">Order Index</a></li>
                        <li><a href="#">Nieuwe Order</a></li>
                    </ul>
                </li>
                <li class="{{ Nav::isResource('summaries') }}">
                    <h3><span class="icon-color far fa-chart-bar"></span>Overzichten</h3>
                    <ul>
                        <li class="{{ Nav::isRoute('orders.index') }}"><a href="{{route('orders.index')}}">Openstaande Bestellingen</a></li>
                        <li class="{{ Nav::isRoute('boloffers.index') }}"><a href="{{route('boloffers.index')}}">Gepubliceerd op BOL</a></li>
                        <li class="{{ Nav::isRoute('boloffers.offer.checkinitialstatus') }}"><a href="{{route('boloffers.offer.checkinitialstatus')}}">Offers in behandeling door BOL</a></li>
                        <li class="{{ Nav::isRoute('boloffers.publish.select') }}"><a href="{{route('boloffers.publish.select')}}">Publiceer op BOL</a></li>
                        <li class="{{ Nav::isRoute('orders.index') }}"><a href="{{route('orders.index')}}">Bestellingen per Periode</a></li>
                        <li class="{{ Nav::isRoute('orders.index') }}"><a href="{{route('orders.index')}}">Bestellingen per Klanr</a></li>
                        <li class="{{ Nav::isRoute('orders.index') }}"><a href="{{route('orders.index')}}">Bestellingen per Variant</a></li>
                        <li class="{{ Nav::isRoute('orders.index') }}"><a href="{{route('orders.index')}}">Gebruikte EAN nummers</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </aside>
</div>

<script src="http://thecodeplayer.com/uploads/js/jquery-1.7.1.min.js" type="text/javascript"></script>
