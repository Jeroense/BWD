<div class="side-menu" id="side-menu">
    <aside class="menu m-t-30 m-l-10">

        <p class="menu-label">
            Orders
        </p>
        <ul class="menu-list">
            <li><a href="{{route('products.dashboard')}}" class="{{ Nav::isRoute('products.dashboard') }}">Orders 1</a></a></li>
        </ul>

        <p class="menu-label">
            Andere Orders
        </p>

        <ul class="menu-list">
            <li><a href="{{route('products.dashboard')}}" class="{{ Nav::isRoute('products.dashboard') }}">Orders 2</a></li>
        </ul>
    </aside>
</div>
