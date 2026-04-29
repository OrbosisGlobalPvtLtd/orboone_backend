@php
    $isAdmin = auth()->user()->isAdmin();
    $docOpen = request()->routeIs('hr.documents*') || request()->routeIs('employee.documents-index') || request()->routeIs('employee.hr-documents');
@endphp

{{-- ========== SECTION: 5. DOCUMENT MANAGEMENT ========== --}}
<a href="#docSubmenu" data-toggle="collapse" aria-expanded="{{ $docOpen ? 'true' : 'false' }}" 
   class="nav-link sidebar-collapse-btn {{ $docOpen ? '' : 'collapsed' }}">
    <i class="fas fa-folder-open mr-2"></i>
    <span class="flex-grow-1">5. Document Management</span>
    <i class="fas fa-chevron-down chevron"></i>
</a>

<ul class="collapse list-unstyled {{ $docOpen ? 'show' : '' }}" id="docSubmenu" data-parent="#sidebarMenu">
    
    @if ($isAdmin)
    {{-- Sub-module: Document Approval (Admin) --}}
    <li>
        <a href="{{ route('hr.documents.index') }}" class="nav-link sub-nav-link {{ request()->routeIs('hr.documents.index') ? 'active' : '' }}">
            <i class="fas fa-shield-alt small mr-2 text-warning"></i> Compliance Management
        </a>
    </li>
    @endif

    {{-- Sub-module: My Documents (Employee/Both) --}}
    <li>
        <a href="{{ route('employee.documents-index') }}" class="nav-link sub-nav-link {{ request()->routeIs('employee.documents-index') ? 'active' : '' }}">
            <i class="fas fa-file-upload small mr-2"></i> Upload Documents
        </a>
    </li>

    {{-- Sub-module: HR Policies / Company Doc (Both) --}}
    <li>
        <a href="{{ route('employee.hr-policies') }}" class="nav-link sub-nav-link {{ request()->routeIs('employee.hr-policies') ? 'active' : '' }}">
            <i class="fas fa-file-contract small mr-2 text-info"></i> Company Documents
        </a>
    </li>
</ul>