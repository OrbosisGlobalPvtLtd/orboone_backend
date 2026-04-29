<a href="#userSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle nav-link sidebar-collapse-btn">                    
    <i class="fas fa-database mr-2"></i>
    <span class="flex-grow-1">Master Data</span>
    <i class="chevron fas fa-chevron-down"></i>
</a>

<ul class="collapse list-unstyled" id="userSubmenu" data-parent="#sidebarMenu">

    <li class="nav-item">
       
    </li>

      {{-- DATA MASTER --}}
    <li class="nav-item">
        <a href="{{ route('departments-data') }}" class="nav-link">
            <i class="fa-solid fa-building mr-2"></i> Departments
        </a>
    </li>

    <li class="nav-item">
        <a href="{{ route('positions-data') }}" class="nav-link">
            <i class="fa-solid fa-user-tie mr-2"></i> Designation
        </a>
    </li>
     <li class="nav-item">
        <a href="{{ route('asset-allocations.index') }}" class="nav-link">
            <i class="fa-solid fa-laptop-code mr-2"></i> Asset Allocation
        </a>
    </li>

</ul>