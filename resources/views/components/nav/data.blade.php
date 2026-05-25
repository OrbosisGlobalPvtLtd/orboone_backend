@php
    $isAdmin = auth()->user()->isAdmin();
    $docOpen = request()->routeIs('documents.hr.*') || request()->routeIs('hrms.hrms.documents.self.index') || request()->routeIs('hrms.hrms.documents.self.index');
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
        <a href="{{ route('documents.hr.index') }}" class="nav-link sub-nav-link {{ request()->routeIs('documents.hr.index') ? 'active' : '' }}">
            <i class="fas fa-shield-alt small mr-2 text-warning"></i> Compliance Management
        </a>
    </li>
    @endif

    {{-- Sub-module: My Documents (Employee/Both) --}}
    <li>
        <a href="{{ route('hrms.hrms.documents.self.index') }}" class="nav-link sub-nav-link {{ request()->routeIs('hrms.hrms.documents.self.index') ? 'active' : '' }}">
            <i class="fas fa-file-upload small mr-2"></i> Upload Documents
        </a>
    </li>

    {{-- Sub-module: HR Policies / Company Doc (Both) --}}
    <li>
        <a href="{{ route('documents.policies.self') }}" class="nav-link sub-nav-link {{ request()->routeIs('documents.policies.self') ? 'active' : '' }}">
            <i class="fas fa-folder-open small mr-2 text-info"></i> Company Documents & Policies
        </a>
    </li>
</ul>