@php
    $user = auth()->user();

    $canComplianceManage = $user->hasPermission('documents.compliance.manage');
    $canDocumentUpload = $user->hasPermission('documents.upload');
    $canCompanyDocumentsView = $user->hasPermission('documents.company.view');

    $showDocumentMenu =
        $canComplianceManage ||
        $canDocumentUpload ||
        $canCompanyDocumentsView;

    $docOpen =
        request()->routeIs('documents.hr.*') ||
        request()->routeIs('hrms.hrms.documents.self.index') ||
        request()->routeIs('hrms.hrms.documents.self.index') ||
        request()->routeIs('documents.policies.self');
@endphp

@if($showDocumentMenu)
<div class="sidebar-group {{ $docOpen ? 'open' : '' }}">
    <button
        type="button"
        class="sidebar-group-toggle {{ $docOpen ? '' : 'collapsed' }}"
        data-toggle="collapse"
        data-target="#docSubmenu"
        aria-expanded="{{ $docOpen ? 'true' : 'false' }}"
        aria-controls="docSubmenu"
    >
        <span class="menu-icon"><i class="fas fa-folder-open"></i></span>
        <span class="menu-text flex-grow-1">Document Management</span>
        <span class="group-chevron"><i class="fas fa-chevron-down"></i></span>
    </button>

    <div class="sidebar-submenu collapse {{ $docOpen ? 'show' : '' }}" id="docSubmenu" data-parent="#sidebarMenu">

        @if ($canComplianceManage)
            <a href="{{ route('documents.hr.index') }}"
               class="sub-link {{ request()->routeIs('documents.hr.index') ? 'active' : '' }}">
                <span class="sub-link-icon"><i class="fas fa-shield-alt"></i></span>
                <span class="sub-link-text">Compliance Management</span>
            </a>
        @endif

        @if ($canDocumentUpload)
            <a href="{{ route('hrms.documents.self.index') }}"
               class="sub-link {{ request()->routeIs('hrms.hrms.documents.self.index') ? 'active' : '' }}">
                <span class="sub-link-icon"><i class="fas fa-file-upload"></i></span>
                <span class="sub-link-text">Upload Documents</span>
            </a>
        @endif

        @if ($canCompanyDocumentsView)
            <a href="{{ route('documents.policies.self') }}"
               class="sub-link {{ request()->routeIs('documents.policies.self') ? 'active' : '' }}">
                <span class="sub-link-icon"><i class="fas fa-folder-open"></i></span>
                <span class="sub-link-text">Company Documents & Policies</span>
            </a>
        @endif

    </div>
</div>
@endif
