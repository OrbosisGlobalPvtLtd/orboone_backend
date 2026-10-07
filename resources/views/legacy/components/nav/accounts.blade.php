<a href="#accountSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle nav-link sidebar-collapse-btn"> 
    <i class="fas fa-user-shield mr-2"></i>
    <span class="flex-grow-1">Roles & Permissions</span>
    <i class="chevron fas fa-chevron-down"></i>
</a>

<ul class="collapse list-unstyled" id="accountSubmenu" data-parent="#sidebarMenu">

    <li class="nav-item">
        <a href="{{ route('users') }}" class="nav-link">
            <i class="fa-solid fa-user mr-2"></i> Users
        </a>
    </li>

    <li class="nav-item">
        <a href="{{ route('roles.index') }}" class="nav-link">
            <i class="fa-solid fa-user-shield mr-2"></i> Roles
        </a>
    </li>

     

</ul>
