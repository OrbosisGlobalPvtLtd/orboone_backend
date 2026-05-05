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
        <a href="{{ route('hrms.departments.index') }}" class="nav-link">
            <i class="fa-solid fa-building mr-2"></i> Departments
        </a>
    </li>

    <li class="nav-item">
        <a href="{{ route('hrms.designations.index') }}" class="nav-link">
            <i class="fa-solid fa-user-tie mr-2"></i> Designation
        </a>
    </li>
     <li class="nav-item">
        <a href="{{ route('hrms.assets.index') }}" class="nav-link">
            <i class="fa-solid fa-laptop-code mr-2"></i> Asset Allocation
        </a>
    </li>

</ul>