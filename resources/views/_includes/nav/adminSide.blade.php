<div class="side-menu" id="side-menu">
    <aside class="menu m-t-30 m-l-20">
        <p class="menu-label">
            Algemeen
        </p>
        <ul class="menu-list">
            <li><a href="{{route('system.index')}}" class="{{ Nav::isResource('system') }}">Systeem info</a></li>
        </ul>

        <p class="menu-label">
            Beheer
        </p>

        <ul class="menu-list">
            <li><a href="{{route('users.index')}}" class="{{ Nav::isResource('users') }}">Gebruiker beheer</a></li>
            <li>
                <a class="has-submenu {{ Nav::hasSegment(['roles', 'permissions'], 2) }}">Rollen &amp; Permissies</a>
                <ul class="submenu">
                    <li><a class="{{ Nav::isResource('roles') }}" href="{{route('roles.index')}}">Roles</a></li>
                    <li><a class="{{ Nav::isResource('permissions') }}" href="{{route('permissions.index')}}">Permissies</a></li>
                </ul>
            </li>
        </ul>
    </aside>
</div>
