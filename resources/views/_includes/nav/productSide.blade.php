<div class="side-menu" id="side-menu">
    <aside class="menu m-t-30 m-l-10">

        <p class="menu-label">
            Borduurwerkdeal
        </p>
        <ul class="menu-list">
            <li><a href="{{route('products.index')}}" class="{{ Nav::isRoute('products.index') }}">Producten index</a></li>
            <li><a href="{{route('products.create')}}" class="{{ Nav::isRoute('products.create') }}">Nieuw product</a></li>
        </ul>
    </aside>
</div>
