<div class="side-menu" id="side-menu">
    <aside class="menu m-t-30 m-l-10">

        <p class="menu-label">
            Borduurwerkdeal
        </p>
        <ul class="menu-list">
            <li><a href="{{route('test')}}">Artikel index</a></li>
        </ul>

        <p class="menu-label">
            Smake
        </p>

        <ul class="menu-list">
            <li><a href="{{route('variants.index')}}" class="{{ Nav::isRoute('variants.index') }}">Artikel index</a></li>
            <li><a href="{{route('variants.media')}}" class="{{ Nav::isRoute('variants.media') }}">Media index</a></li>
        </ul>
    </aside>
</div>
