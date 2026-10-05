@php
    $privateFileUrl = function ($path) {
        if (empty($path)) {
            return '#';
        }

        if (Route::has('hrms.documents.file')) {
            return route('hrms.documents.file', $path);
        }

        if (Route::has('hrms.employee.file')) {
            return route('hrms.documents.file', ['path' => $path]);
        }

        return route('hrms.documents.file', ['path' => $path]);
    };
@endphp

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const rawEmployees = @json($employees);

        // State selectors
        const searchInput = document.getElementById('filterSearch');
        const departmentFilter = document.getElementById('filterDepartment');
        const designationFilter = document.getElementById('filterDesignation');
        const applyBtn = document.getElementById('btnApplyFilters');
        const resetBtn = document.getElementById('btnResetFilters');
        
        const btnTreeView = document.getElementById('btnTreeView');
        const btnListView = document.getElementById('btnListView');
        
        const treeToolbar = document.getElementById('treeToolbar');
        const treeContainer = document.getElementById('treeViewContainer');
        const listContainer = document.getElementById('listViewContainer');
        const emptyState = document.getElementById('emptyStateContainer');
        
        const treeWrapper = document.getElementById('orgTreeWrapper');
        const listWrapper = document.getElementById('stackedListWrapper');

        const btnZoomIn = document.getElementById('btnZoomIn');
        const btnZoomOut = document.getElementById('btnZoomOut');
        const btnResetZoom = document.getElementById('btnResetZoom');
        const zoomLevelText = document.getElementById('zoomLevelText');
        const btnExpandAll = document.getElementById('btnExpandAll');
        const btnCollapseAll = document.getElementById('btnCollapseAll');

        let currentView = 'tree'; // 'tree' or 'list'
        let collapsedNodes = new Set();
        let currentZoom = 1.0;

        // Initialize Select2 if loaded
        if (window.jQuery && typeof $.fn.select2 !== 'undefined') {
            $('#filterDepartment, #filterDesignation').select2({
                width: '100%',
                dropdownAutoWidth: true
            });
        }

        // 1. Sanitize & check circular dependencies
        function checkCircularDependency(emp, visited = new Set()) {
            if (visited.has(emp.id)) return true;
            visited.add(emp.id);
            const managerId = emp.reporting_manager_employee_id;
            if (!managerId) return false;
            const manager = rawEmployees.find(e => e.id == managerId);
            if (!manager) return false;
            return checkCircularDependency(manager, visited);
        }

        const sanitizedEmployees = rawEmployees.map(emp => {
            const hasLoop = checkCircularDependency(emp);
            return {
                ...emp,
                reporting_manager_employee_id: hasLoop ? null : emp.reporting_manager_employee_id
            };
        });

        // 2. Pre-calculate children maps
        const childrenMap = new Map();
        sanitizedEmployees.forEach(emp => {
            const mId = emp.reporting_manager_employee_id;
            if (mId) {
                if (!childrenMap.has(mId)) {
                    childrenMap.set(mId, []);
                }
                childrenMap.get(mId).push(emp);
            }
        });

        // 3. Helper to calculate descendants count recursively
        function getDescendantsCount(empId) {
            const children = childrenMap.get(empId) || [];
            let count = children.length;
            children.forEach(child => {
                count += getDescendantsCount(child.id);
            });
            return count;
        }

        // 4. Render a single Node Card
        function renderNodeCard(emp, isMatch, hasChildren) {
            const initials = emp.name ? emp.name.split(' ').map(n => n[0]).slice(0,2).join('').toUpperCase() : '?';
            const baseFileUrl = '{{ $privateFileUrl("PLACEHOLDER") }}';
            const imgPath = emp.profile_image ? baseFileUrl.replace('PLACEHOLDER', emp.profile_image) : null;
            const avatarHtml = imgPath 
                ? `<img class="eo-node-avatar" src="${imgPath}" alt="${emp.name}" onerror="this.outerHTML='<div class=&quot;eo-node-avatar&quot;>${initials}</div>'">`
                : `<div class="eo-node-avatar">${initials}</div>`;

            const reports = childrenMap.get(emp.id) || [];
            const directCount = reports.length;
            const totalCount = getDescendantsCount(emp.id);

            const isCollapsed = collapsedNodes.has(emp.id);

            const toggleBtnHtml = hasChildren 
                ? `<button type="button" class="eo-toggle-branch" data-id="${emp.id}" title="${isCollapsed ? 'Expand branch' : 'Collapse branch'}">
                     <i class="fas ${isCollapsed ? 'fa-plus' : 'fa-minus'}"></i>
                   </button>`
                : '';

            const badgeHtml = directCount > 0 
                ? `<div class="eo-reportees-badge" title="${totalCount} total reports recursively">
                     <i class="fas fa-users mr-1"></i> ${directCount} / ${totalCount} Team
                   </div>`
                : `<div class="eo-reportees-badge" style="background:#F9FAFB;color:#98A2B3;">
                     <i class="fas fa-user-friends mr-1"></i> 0 Team
                   </div>`;

            const profileUrl = `{{ url('/hrms/employees') }}/${emp.id}/profile-view`;
            const highlightClass = isMatch ? 'highlighted' : '';
            const matchTagHtml = isMatch ? `<span class="eo-match-pill"><i class="fas fa-check-circle"></i> Match</span>` : '';

            return `
                <div class="eo-node-card ${highlightClass}" id="card-${emp.id}">
                    ${matchTagHtml}
                    <div class="eo-node-top">
                        ${avatarHtml}
                        <div class="eo-node-info">
                            <h5 class="eo-node-name" title="${emp.name || '-'}">${emp.name || '-'}</h5>
                            <span class="eo-node-code">${emp.employee_code || 'EMP-' + emp.id}</span>
                        </div>
                    </div>
                    <div class="eo-node-detail-line" title="${emp.designation_name || '-'}">
                        <i class="fas fa-briefcase"></i> ${emp.designation_name || 'No Designation'}
                    </div>
                    <div class="eo-node-detail-line" title="${emp.department_name || '-'}">
                        <i class="fas fa-building"></i> ${emp.department_name || 'No Department'}
                    </div>
                    <div class="eo-node-badges">
                        ${badgeHtml}
                        <a href="${profileUrl}" class="eo-profile-link">
                            Profile <i class="fas fa-chevron-right" style="font-size:8px;"></i>
                        </a>
                    </div>
                    ${toggleBtnHtml}
                </div>
            `;
        }

        // 5. Build dynamic Tree DOM recursively based on visible set
        function buildTreeHtml(managerId, visibleNodeIds, matchingNodeIds) {
            const employeesAtThisLevel = sanitizedEmployees.filter(emp => {
                if (!visibleNodeIds.has(emp.id)) return false;

                if (!managerId) {
                    // Roots are visible employees without a visible manager in current set
                    const managerInVisibleSet = emp.reporting_manager_employee_id && 
                        visibleNodeIds.has(parseInt(emp.reporting_manager_employee_id));
                    return !emp.reporting_manager_employee_id || !managerInVisibleSet;
                }
                return emp.reporting_manager_employee_id == managerId;
            });

            if (employeesAtThisLevel.length === 0) return '';

            let html = '<ul>';
            employeesAtThisLevel.forEach(emp => {
                const allChildren = childrenMap.get(emp.id) || [];
                const visibleChildren = allChildren.filter(c => visibleNodeIds.has(c.id));
                const isCollapsed = collapsedNodes.has(emp.id);

                const hasChildrenClass = (visibleChildren.length > 0 && !isCollapsed) ? 'has-children' : '';
                const isMatch = matchingNodeIds.has(emp.id);

                html += `<li class="${hasChildrenClass}">`;
                html += renderNodeCard(emp, isMatch, visibleChildren.length > 0);
                
                if (visibleChildren.length > 0 && !isCollapsed) {
                    html += buildTreeHtml(emp.id, visibleNodeIds, matchingNodeIds);
                }
                
                html += `</li>`;
            });
            html += '</ul>';
            return html;
        }

        // 6. Build list DOM with Formatted S.No & Optimized Layout
        function renderListView(filteredList) {
            if (!filteredList || filteredList.length === 0) return '';

            const baseFileUrl = '{{ $privateFileUrl("PLACEHOLDER") }}';
            const baseProfileUrl = '{{ url("/hrms/employees") }}';

            return filteredList.map((emp, index) => {
                const sNo = index + 1;
                const sNoFormatted = sNo < 10 ? `0${sNo}` : `${sNo}`;
                const initials = emp.name ? emp.name.split(' ').filter(Boolean).map(n => n[0]).slice(0, 2).join('').toUpperCase() : '?';
                const imgPath = emp.profile_image ? baseFileUrl.replace('PLACEHOLDER', emp.profile_image) : null;
                const avatarHtml = imgPath 
                    ? `<img class="eo-node-avatar" src="${imgPath}" alt="${emp.name}" onerror="this.outerHTML='<div class=&quot;eo-node-avatar&quot;>${initials}</div>'">`
                    : `<div class="eo-node-avatar">${initials}</div>`;

                const reports = childrenMap.get(emp.id) || [];
                const directCount = reports.length;
                const totalCount = getDescendantsCount(emp.id);

                const manager = sanitizedEmployees.find(m => m.id == emp.reporting_manager_employee_id);
                const managerName = manager ? manager.name : 'Unassigned / Top Root';
                const profileUrl = `${baseProfileUrl}/${emp.id}/profile-view`;

                return `
                    <div class="eo-list-item" id="list-card-${emp.id}">
                        <div class="eo-list-left">
                            <div class="eo-list-sno" title="Serial Number #${sNo}">${sNoFormatted}</div>
                            ${avatarHtml}
                            <div class="eo-list-emp-details">
                                <h5 class="eo-node-name" title="${emp.name || '-'}">${emp.name || '-'}</h5>
                                <div class="eo-node-code">
                                    <span>${emp.employee_code || 'EMP-' + emp.id}</span>
                                    <span>•</span>
                                    <span>${emp.designation_name || 'No Designation'}</span>
                                </div>
                            </div>
                        </div>
                        <div class="eo-list-right">
                            <span class="eo-list-badge badge-dept" title="Department">
                                <i class="fas fa-building"></i> ${emp.department_name || 'No Department'}
                            </span>
                            <span class="eo-list-badge badge-manager" title="Reporting Manager">
                                <i class="fas fa-user-tie"></i> Manager: ${managerName}
                            </span>
                            <span class="eo-list-badge badge-team" title="Direct / Total Team Size">
                                <i class="fas fa-users"></i> Team Size: ${directCount} / ${totalCount}
                            </span>
                            <a href="${profileUrl}" class="eo-list-btn-profile" title="View Profile">
                                View Profile <i class="fas fa-arrow-right" style="font-size:10px;"></i>
                            </a>
                        </div>
                    </div>
                `;
            }).join('');
        }

        // 7. Core filter & search evaluator
        function evaluateFilters() {
            const search = (searchInput ? searchInput.value : '').toLowerCase().trim();
            const dept = (window.jQuery ? ($('#filterDepartment').val() || '') : (departmentFilter ? departmentFilter.value : '')).toLowerCase().trim();
            const desg = (window.jQuery ? ($('#filterDesignation').val() || '') : (designationFilter ? designationFilter.value : '')).toLowerCase().trim();

            const isFilterActive = !!(search || dept || desg);

            const matchingNodeIds = new Set();
            const visibleNodeIds = new Set();

            sanitizedEmployees.forEach(emp => {
                const matchSearch = !search || 
                    (emp.name || '').toLowerCase().includes(search) || 
                    (emp.employee_code || '').toLowerCase().includes(search);
                const matchDept = !dept || (emp.department_name || '').toLowerCase().trim() === dept;
                const matchDesg = !desg || (emp.designation_name || '').toLowerCase().trim() === desg;

                if (matchSearch && matchDept && matchDesg) {
                    matchingNodeIds.add(emp.id);
                }
            });

            if (!isFilterActive) {
                // If no filter is active, show all employees
                sanitizedEmployees.forEach(emp => visibleNodeIds.add(emp.id));
            } else {
                // Include all matching nodes AND all their ancestors up to root
                matchingNodeIds.forEach(empId => {
                    let currentId = empId;
                    while (currentId) {
                        visibleNodeIds.add(parseInt(currentId));
                        const currentEmp = sanitizedEmployees.find(e => e.id == currentId);
                        currentId = currentEmp ? currentEmp.reporting_manager_employee_id : null;
                    }
                });

                // Auto-expand ancestors of matching nodes so they are visible
                matchingNodeIds.forEach(empId => {
                    let currentId = empId;
                    while (currentId) {
                        const currentEmp = sanitizedEmployees.find(e => e.id == currentId);
                        if (currentEmp && currentEmp.reporting_manager_employee_id) {
                            collapsedNodes.delete(parseInt(currentEmp.reporting_manager_employee_id));
                        }
                        currentId = currentEmp ? currentEmp.reporting_manager_employee_id : null;
                    }
                });
            }

            // Update counter badge
            const matchCounter = document.getElementById('matchCounterBadge');
            if (matchCounter) {
                if (isFilterActive) {
                    matchCounter.innerHTML = `<i class="fas fa-filter"></i> Showing ${matchingNodeIds.size} of ${sanitizedEmployees.length} Employees`;
                } else {
                    matchCounter.innerHTML = `<i class="fas fa-users"></i> Total ${sanitizedEmployees.length} Active Employees`;
                }
            }

            if (sanitizedEmployees.length === 0 || (isFilterActive && matchingNodeIds.size === 0)) {
                treeToolbar.style.display = 'none';
                treeContainer.style.display = 'none';
                listContainer.style.display = 'none';
                emptyState.style.display = 'block';
                return;
            }

            if (currentView === 'tree') {
                treeToolbar.style.display = 'flex';
                treeContainer.style.display = 'flex';
                listContainer.style.display = 'none';
                emptyState.style.display = 'none';

                treeWrapper.innerHTML = buildTreeHtml(null, visibleNodeIds, matchingNodeIds);
            } else {
                treeToolbar.style.display = 'none';
                treeContainer.style.display = 'none';
                listContainer.style.display = 'block';
                emptyState.style.display = 'none';

                const matchingEmployeesList = sanitizedEmployees.filter(emp => matchingNodeIds.has(emp.id));
                listWrapper.innerHTML = renderListView(matchingEmployeesList);
            }
        }

        // 8. Zoom Controls
        function updateZoom(newZoom) {
            currentZoom = Math.max(0.5, Math.min(1.5, newZoom));
            treeWrapper.style.transform = `scale(${currentZoom})`;
            zoomLevelText.innerText = `${Math.round(currentZoom * 100)}%`;
        }

        btnZoomIn.addEventListener('click', function() {
            updateZoom(currentZoom + 0.1);
        });

        btnZoomOut.addEventListener('click', function() {
            updateZoom(currentZoom - 0.1);
        });

        btnResetZoom.addEventListener('click', function() {
            updateZoom(1.0);
        });

        btnExpandAll.addEventListener('click', function() {
            collapsedNodes.clear();
            evaluateFilters();
        });

        btnCollapseAll.addEventListener('click', function() {
            sanitizedEmployees.forEach(emp => {
                const children = childrenMap.get(emp.id) || [];
                if (children.length > 0) {
                    collapsedNodes.add(emp.id);
                }
            });
            evaluateFilters();
        });

        // 9. Bind collapsible branch toggles
        document.addEventListener('click', function(e) {
            const toggleBtn = e.target.closest('.eo-toggle-branch');
            if (toggleBtn) {
                const nodeId = parseInt(toggleBtn.dataset.id);
                if (collapsedNodes.has(nodeId)) {
                    collapsedNodes.delete(nodeId);
                } else {
                    collapsedNodes.add(nodeId);
                }
                evaluateFilters();
            }
        });

        // 10. Filter and Search Bindings (Only executes on Filter button or Enter key)
        if (applyBtn) {
            applyBtn.addEventListener('click', evaluateFilters);
        }

        if (searchInput) {
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    evaluateFilters();
                }
            });
        }

        // 11. View selection bindings
        btnTreeView.addEventListener('click', function() {
            btnTreeView.classList.add('active');
            btnListView.classList.remove('active');
            currentView = 'tree';
            evaluateFilters();
        });

        btnListView.addEventListener('click', function() {
            btnListView.classList.add('active');
            btnTreeView.classList.remove('active');
            currentView = 'list';
            evaluateFilters();
        });

        // 12. Reset action
        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                if (searchInput) searchInput.value = '';
                if (window.jQuery && typeof $.fn.select2 !== 'undefined') {
                    $('#filterDepartment').val('').trigger('change.select2');
                    $('#filterDesignation').val('').trigger('change.select2');
                } else {
                    if (departmentFilter) departmentFilter.value = '';
                    if (designationFilter) designationFilter.value = '';
                }
                collapsedNodes.clear();
                updateZoom(1.0);
                evaluateFilters();
            });
        }

        // Initial draw
        evaluateFilters();
    });
</script>
