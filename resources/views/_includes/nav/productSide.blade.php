<div class="side-menu" id="side-menu">
    <aside class="menu m-t-30 m-l-10">

        <p class="menu-label">
            Borduurwerkdeal
        </p>
        <ul class="menu-list">
            <li><a href="{{route('products.index')}}" class="{{ Nav::isRoute('products.index') }}">Producten index</a></li>
            <li><a href="{{route('products.create')}}" class="{{ Nav::isRoute('products.create') }}">Nieuwe Variant</a></li>
            <li><a href="{{route('products.upload')}}" class="{{ Nav::isRoute('products.upload') }}">Upload product</a></li>
        </ul>
        <p class="menu-label">
            Smake
        </p>
        <ul class="menu-list">
            <li><a href="{{route('products.download')}}" class="{{ Nav::isRoute('products.upload') }}">download product</a></li>
        </ul>
    </aside>
</div>
