@extends('layouts.panel', ['active' => 'employees'])

@section('page_title', 'Department & Designation')

@section('_content')
<style>
:root{
    --orb-pink:#D400D5;
    --orb-bg:#F6F7FB;
    --orb-card:#FFFFFF;
    --orb-border:#E7EAF3;
    --orb-text:#101828;
    --orb-muted:#667085;
    --orb-soft:#F4F2FF;
    --orb-shadow:0 10px 28px rgba(16,24,40,.06);
}

.org-page{
    min-height:calc(100vh - 90px);
    padding:16px 10px 30px;
    background:var(--orb-bg);
}

.org-container{
    max-width:1320px;
    margin:0 auto;
}

.org-header-premium {
    background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
    border-radius: 26px;
    padding: 26px 30px;
    margin-bottom: 24px;
    box-shadow: 0 14px 35px rgba(75, 0, 232, 0.15);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    color: #fff;
}

.org-header-kicker {
    font-size: 11px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: rgba(255, 255, 255, 0.85);
    margin-bottom: 8px;
}

.org-header-title {
    font-size: 28px;
    font-weight: 950;
    margin: 0 0 8px 0;
    color: #fff;
}

.org-header-subtitle {
    font-size: 14px;
    font-weight: 650;
    color: rgba(255, 255, 255, 0.85);
    margin: 0;
}

.btn-white-pill {
    background: #fff !important;
    color: var(--orb-primary) !important;
    border: 0 !important;
    border-radius: 999px !important;
    padding: 10px 22px !important;
    font-size: 13px !important;
    font-weight: 900 !important;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05) !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 6px !important;
    cursor: pointer !important;
    transition: 0.15s ease !important;
}

.btn-white-pill:hover {
    background: #FAFAFA !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08) !important;
}

.org-layout{
    display:grid;
    grid-template-columns:360px minmax(0,1fr);
    gap:14px;
}

.org-card{
    background:#fff;
    border:1px solid var(--orb-border);
    border-radius:18px;
    box-shadow:var(--orb-shadow);
    overflow:hidden;
}

.org-card-head{
    padding:14px 16px;
    border-bottom:1px solid #EEF1F6;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    background:#fff;
}

.org-title{
    display:flex;
    align-items:center;
    gap:10px;
    min-width:0;
}

.org-icon{
    width:36px;
    height:36px;
    border-radius:12px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:var(--orb-soft);
    color:var(--orb-primary);
    flex:0 0 auto;
}

.org-title h5{
    margin:0;
    font-size:15px;
    font-weight:900;
    color:var(--orb-text);
}

.org-title p{
    margin:2px 0 0;
    font-size:12px;
    font-weight:600;
    color:var(--orb-muted);
}

.org-body{
    padding:14px;
}

.btn-orb{
    border:0;
    border-radius:12px;
    padding:9px 14px;
    font-size:13px;
    font-weight:900;
    background:linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
    color:#fff!important;
    box-shadow:0 8px 18px rgba(75,0,232,.16);
    white-space:nowrap;
}

.btn-soft{
    border-radius:12px;
    padding:9px 13px;
    font-size:13px;
    font-weight:900;
    background:#F4F6FB;
    border:1px solid #E5E7EB;
    color:#111827!important;
}

.btn-edit,
.btn-delete{
    border:0;
    border-radius:11px;
    width:34px;
    height:34px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    font-size:13px;
    font-weight:900;
}

.btn-edit{
    background:#FFF7E6;
    color:#B54708!important;
}

.btn-delete{
    background:#FEE2E2;
    color:#991B1B!important;
}

.dept-list{
    display:flex;
    flex-direction:column;
    gap:10px;
    max-height:calc(100vh - 245px);
    overflow-y:auto;
    padding-right:2px;
}

.dept-list::-webkit-scrollbar{
    width:5px;
}

.dept-list::-webkit-scrollbar-thumb{
    background:#D9DDE8;
    border-radius:999px;
}

.dept-item{
    width:100%;
    border:1px solid #EEF1F6;
    background:#fff;
    border-radius:16px;
    padding:12px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
    cursor:pointer;
    transition:.18s ease;
    text-align:left;
}

.dept-item:hover,
.dept-item.active{
    border-color:rgba(75,0,232,.25);
    background:#FBFAFF;
    box-shadow:0 8px 20px rgba(16,24,40,.05);
}

.dept-main{
    display:flex;
    align-items:center;
    gap:10px;
    min-width:0;
}

.dept-avatar{
    width:38px;
    height:38px;
    border-radius:13px;
    background:#F4F2FF;
    color:var(--orb-primary);
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:900;
    flex:0 0 auto;
}

.dept-name{
    font-size:13px;
    font-weight:900;
    color:var(--orb-text);
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}

.dept-meta{
    margin-top:2px;
    font-size:11px;
    font-weight:700;
    color:var(--orb-muted);
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
    max-width:210px;
}

.dept-code{
    border-radius:999px;
    padding:6px 9px;
    background:#F4F2FF;
    color:var(--orb-primary);
    font-size:11px;
    font-weight:900;
    flex:0 0 auto;
}

.selected-box{
    border-radius:16px;
    padding:14px;
    background:#FBFAFF;
    border:1px solid rgba(75,0,232,.12);
    margin-bottom:14px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
}

.selected-box h4{
    margin:0;
    color:var(--orb-text);
    font-size:17px;
    font-weight:900;
}

.selected-box p{
    margin:4px 0 0;
    color:var(--orb-muted);
    font-size:12px;
    font-weight:650;
}

.table-wrap{
    border:1px solid #EEF1F6;
    border-radius:16px;
    overflow:auto;
}

.org-table{
    margin:0;
    min-width:760px;
}

.org-table thead th{
    border-top:0;
    background:#F8FAFC;
    color:#667085;
    font-size:11px;
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:.4px;
    padding:12px 14px;
    white-space:nowrap;
}

.org-table tbody td{
    border-top:1px solid #F0F2F7;
    padding:12px 14px;
    vertical-align:middle;
    font-size:13px;
    font-weight:650;
    color:#344054;
}

.org-table tbody tr:hover{
    background:#FCFAFF;
}

.status-pill{
    display:inline-flex;
    border-radius:999px;
    padding:6px 9px;
    font-size:11px;
    font-weight:900;
    white-space:nowrap;
}

.status-active{
    background:#DCFCE7;
    color:#166534;
}

.status-inactive{
    background:#FEE2E2;
    color:#991B1B;
}

.empty-state{
    padding:40px 18px;
    text-align:center;
}

.empty-icon{
    width:64px;
    height:64px;
    border-radius:22px;
    background:#F4F2FF;
    color:var(--orb-primary);
    display:flex;
    align-items:center;
    justify-content:center;
    margin:0 auto 12px;
    font-size:24px;
}

.empty-state h4{
    margin:0;
    color:var(--orb-text);
    font-weight:900;
    font-size:18px;
}

.empty-state p{
    margin:7px 0 0;
    color:var(--orb-muted);
    font-size:13px;
    font-weight:650;
}

.modal-content{
    border:0;
    border-radius:20px;
    box-shadow:0 25px 70px rgba(15,23,42,.18);
    overflow:hidden;
}

.modal-header{
    border-bottom:1px solid #EEF1F6;
    padding:16px 18px;
    background:#fff;
}

.modal-title{
    font-weight:900;
    color:var(--orb-text);
}

.modal-body{
    padding:18px;
}

.modal-footer{
    border-top:1px solid #EEF1F6;
    padding:14px 18px;
    background:#FCFCFD;
}

.form-label{
    font-size:12px;
    font-weight:900;
    color:#344054;
}

.form-control,
.form-select{
    min-height:42px;
    border-radius:12px;
    border:1px solid #DDE3EE;
    font-size:13px;
    font-weight:650;
}

textarea.form-control{
    min-height:86px;
}

.form-control:focus,
.form-select:focus{
    border-color:var(--orb-secondary);
    box-shadow:0 0 0 .16rem rgba(134,0,238,.10);
}

.readonly-field{
    background:#F8F5FF!important;
    border-color:rgba(75,0,232,.14)!important;
    color:var(--orb-primary)!important;
    font-weight:900!important;
}

.alert{
    border:0;
    border-radius:16px;
    box-shadow:var(--orb-shadow);
    font-weight:650;
}

@media(max-width:991px){
    .org-layout{
        grid-template-columns:1fr;
    }

    .dept-list{
        max-height:none;
    }

    .org-header-premium{
        flex-direction:column;
        align-items:flex-start;
    }

    .org-header-premium .btn-white-pill{
        width:auto;
    }
}

@media(max-width:575px){
    .org-page{
        padding:10px 8px 24px;
    }

    .org-header-premium,
    .org-card{
        border-radius:16px;
    }

    .org-header-premium h3{
        font-size:21px;
    }

    .org-card-head{
        flex-direction:column;
        align-items:flex-start;
    }

    .org-card-head .btn-orb,
    .org-header-premium .btn-white-pill{
        width:100% !important;
    }

    .selected-box{
        flex-direction:column;
        align-items:flex-start;
    }

    .selected-box .d-flex{
        width:100%;
    }

    .selected-box .btn-edit,
    .selected-box .btn-delete{
        flex:1;
        width:auto;
    }

    .dept-meta{
        max-width:190px;
    }

    .modal-dialog{
        margin:10px;
    }
}
</style>

<div class="org-page">
    <div class="org-container">

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <strong>Please fix these errors:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="org-header-premium">
            <div>
                <div class="org-header-kicker">HRMS • ORGANIZATION</div>
                <h1 class="org-header-title">Department & Designation</h1>
                <p class="org-header-subtitle">Manage departments and designation mapping for employee onboarding.</p>
            </div>

            <button type="button" class="btn-white-pill" onclick="openDeptModal()">
                <i class="fas fa-plus mr-1"></i> Add Department
            </button>
        </div>

        <div class="org-layout">

            <div class="org-card">
                <div class="org-card-head">
                    <div class="org-title">
                        <div class="org-icon"><i class="fas fa-building"></i></div>
                        <div>
                            <h5>Departments</h5>
                            <p>Select department</p>
                        </div>
                    </div>
                </div>

                <div class="org-body">
                    <div class="dept-list">
                        @forelse($departments as $dept)
                            @php
                                $count = $designations->where('department_id', $dept->id)->count();
                                $initial = strtoupper(substr($dept->name ?? 'D', 0, 1));
                            @endphp

                            <div class="dept-item"
                                 id="deptItem{{ $dept->id }}"
                                 onclick='selectDepartment(@json($dept))'>
                                <div class="dept-main">
                                    <div class="dept-avatar">{{ $initial }}</div>
                                    <div>
                                        <div class="dept-name">{{ $dept->name }}</div>
                                        <div class="dept-meta">{{ $count }} Designations • {{ $dept->address }}</div>
                                    </div>
                                </div>

                                <span class="dept-code">{{ $dept->code }}</span>
                            </div>
                        @empty
                            <div class="empty-state">
                                <div class="empty-icon"><i class="fas fa-building"></i></div>
                                <h4>No departments</h4>
                                <p>Add first department to start.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="org-card">
                <div class="org-card-head">
                    <div class="org-title">
                        <div class="org-icon"><i class="fas fa-id-badge"></i></div>
                        <div>
                            <h5>Designations</h5>
                            <p id="designationSubTitle">Select department first</p>
                        </div>
                    </div>

                    <button type="button" class="btn-orb" id="addDesignationBtn" onclick="openDesModal()" disabled>
                        <i class="fas fa-plus mr-1"></i> Add Designation
                    </button>
                </div>

                <div class="org-body">
                    <div id="noDeptState" class="empty-state">
                        <div class="empty-icon"><i class="fas fa-hand-pointer"></i></div>
                        <h4>Select a department</h4>
                        <p>Click any department to manage its designations.</p>
                    </div>

                    <div id="designationPanel" style="display:none;">
                        <div class="selected-box">
                            <div>
                                <h4 id="selectedDeptName">-</h4>
                                <p id="selectedDeptMeta">-</p>
                            </div>

                            <div class="d-flex gap-2 flex-wrap">
                                <button type="button" class="btn-edit" onclick="openSelectedDeptEdit()" title="Edit Department">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <form id="deleteDeptForm" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn-delete" onclick="confirmDelete(this.form)" title="Delete Department">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="table-wrap">
                            <table class="table org-table">
                                <thead>
                                    <tr>
                                        <th>Designation</th>
                                        <th>Code</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th width="110">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="designationList"></tbody>
                            </table>
                        </div>

                        <div id="noDesignationState" class="empty-state" style="display:none;">
                            <div class="empty-icon"><i class="fas fa-id-card"></i></div>
                            <h4>No designations</h4>
                            <p>Add first designation for this department.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="deptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="deptForm" method="POST" class="modal-content orb-modal">
            @csrf
            <div id="deptMethodBox"></div>

            <div class="orb-modal-header">
                <div>
                    <h5 class="modal-title" id="deptModalTitle">Add Department</h5>
                    <p class="orb-modal-subtitle">Define a new department unit inside the organization</p>
                </div>
                <button type="button" class="close btn-close btn-close-white" data-dismiss="modal" aria-label="Close" style="color:#fff; opacity:1; border:0; background:transparent; font-size:24px; padding:0; outline:none; line-height:1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body orb-modal-body">
                <div class="orb-form-section">
                    <div class="orb-form-grid" style="grid-template-columns: 1fr;">
                        <div>
                            <label class="orb-form-label">Department Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="dept_name" class="form-control" placeholder="Example: HR Department" required>
                        </div>

                        <div>
                            <label class="orb-form-label">Code</label>
                            <input type="text" id="dept_code" class="form-control readonly-field" value="Auto Generated" readonly>
                            <div class="small text-muted mt-1">Auto generated like DEP-001.</div>
                        </div>

                        <div>
                            <label class="orb-form-label">Address <span class="text-danger">*</span></label>
                            <input type="text" name="address" id="dept_address" class="form-control" placeholder="Department location / office" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer orb-modal-footer">
                <button type="button" class="orb-btn-light" data-dismiss="modal">Cancel</button>
                <button type="submit" class="orb-btn-primary">Save Department</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="desModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="desForm" method="POST" class="modal-content orb-modal">
            @csrf
            <div id="desMethodBox"></div>
            <input type="hidden" name="department_id" id="des_dept">

            <div class="orb-modal-header">
                <div>
                    <h5 class="modal-title" id="desModalTitle">Add Designation</h5>
                    <p class="orb-modal-subtitle">Configure designation mapping and details</p>
                </div>
                <button type="button" class="close btn-close btn-close-white" data-dismiss="modal" aria-label="Close" style="color:#fff; opacity:1; border:0; background:transparent; font-size:24px; padding:0; outline:none; line-height:1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body orb-modal-body">
                <div class="orb-form-section">
                    <div class="orb-form-grid" style="grid-template-columns: 1fr;">
                        <div>
                            <label class="orb-form-label">Selected Department</label>
                            <input type="text" id="des_dept_name" class="form-control readonly-field" readonly>
                        </div>

                        <div>
                            <label class="orb-form-label">Designation Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="des_name" class="form-control" placeholder="Example: HR Executive" required>
                        </div>

                        <div>
                            <label class="orb-form-label">Code</label>
                            <input type="text" id="des_code" class="form-control readonly-field" value="Auto Generated" readonly>
                            <div class="small text-muted mt-1">Auto generated based on department.</div>
                        </div>

                        <div>
                            <label class="orb-form-label">Description</label>
                            <textarea name="description" id="des_description" class="form-control" rows="3" placeholder="Designation description"></textarea>
                        </div>

                        <div>
                            <label class="orb-form-label">Status <span class="text-danger">*</span></label>
                            <select name="is_active" id="des_is_active" class="form-control">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer orb-modal-footer">
                <button type="button" class="orb-btn-light" data-dismiss="modal">Cancel</button>
                <button type="submit" class="orb-btn-primary">Save Designation</button>
            </div>
        </form>
    </div>
</div>

<script>
const departments = @json($departments);
const allDesignations = @json($designations);

let selectedDept = null;

function showModal(id) {
    if (window.jQuery && typeof $('#' + id).modal === 'function') {
        $('#' + id).modal('show');
        return;
    }

    document.getElementById(id).classList.add('show');
    document.getElementById(id).style.display = 'block';
    document.body.classList.add('modal-open');
}

function getNextDeptPreviewCode() {
    const depCodes = departments
        .map(d => d.code || '')
        .filter(code => code.startsWith('DEP-'))
        .map(code => parseInt(code.split('-').pop(), 10))
        .filter(num => !isNaN(num));

    const next = depCodes.length ? Math.max(...depCodes) + 1 : departments.length + 1;

    return 'DEP-' + String(next).padStart(3, '0');
}

function getDeptPrefix(deptName) {
    const name = (deptName || '').toLowerCase();

    const map = [
        ['engineering', 'ENG'], ['web', 'WEB'], ['mobile', 'APP'],
        ['quality', 'QA'], ['ui', 'DES'], ['ux', 'DES'], ['design', 'DES'],
        ['human', 'HR'], ['hr', 'HR'], ['finance', 'FIN'], ['accounts', 'ACC'],
        ['sales', 'SAL'], ['business', 'BD'], ['marketing', 'MKT'],
        ['product', 'PROD'], ['project', 'PM'], ['devops', 'DEV'], ['operations', 'OPS'],
    ];

    for (const item of map) {
        if (name.includes(item[0])) return item[1];
    }

    const clean = (deptName || 'DES').replace(/[^A-Za-z]/g, '').toUpperCase();

    return clean.substring(0, 3) || 'DES';
}

function getNextDesPreviewCode() {
    if (!selectedDept) return 'AUTO';

    const prefix = getDeptPrefix(selectedDept.name);

    const nums = allDesignations
        .filter(d => Number(d.department_id) === Number(selectedDept.id))
        .map(d => d.code || '')
        .filter(code => code.startsWith(prefix + '-'))
        .map(code => parseInt(code.split('-').pop(), 10))
        .filter(num => !isNaN(num));

    const next = nums.length ? Math.max(...nums) + 1 : 1;

    return prefix + '-' + String(next).padStart(3, '0');
}

function openDeptModal(){
    document.getElementById('deptModalTitle').innerText = 'Add Department';
    document.getElementById('deptForm').action = "{{ route('hrms.organization.departments.store') }}";
    document.getElementById('deptMethodBox').innerHTML = '';

    document.getElementById('dept_name').value = '';
    document.getElementById('dept_code').value = getNextDeptPreviewCode();
    document.getElementById('dept_address').value = '';

    showModal('deptModal');
}

function editDept(dept){
    document.getElementById('deptModalTitle').innerText = 'Edit Department';
    document.getElementById('deptForm').action = "{{ url('/hrms/organization/departments') }}/" + dept.id;
    document.getElementById('deptMethodBox').innerHTML = '<input type="hidden" name="_method" value="PUT">';

    document.getElementById('dept_name').value = dept.name || '';
    document.getElementById('dept_code').value = dept.code || '';
    document.getElementById('dept_address').value = dept.address || '';

    showModal('deptModal');
}

function openSelectedDeptEdit(){
    if (!selectedDept) return;
    editDept(selectedDept);
}

function selectDepartment(dept){
    selectedDept = dept;

    document.querySelectorAll('.dept-item').forEach(item => item.classList.remove('active'));

    const activeItem = document.getElementById('deptItem' + dept.id);
    if (activeItem) activeItem.classList.add('active');

    document.getElementById('noDeptState').style.display = 'none';
    document.getElementById('designationPanel').style.display = 'block';
    document.getElementById('addDesignationBtn').disabled = false;

    document.getElementById('selectedDeptName').innerText = dept.name || '-';
    document.getElementById('selectedDeptMeta').innerText = 'Code: ' + (dept.code || '-') + ' • ' + (dept.address || '-');
    document.getElementById('designationSubTitle').innerText = 'Managing designations for ' + (dept.name || '-');
    document.getElementById('deleteDeptForm').action = "{{ url('/hrms/organization/departments') }}/" + dept.id;

    renderDesignations();
}

function renderDesignations(){
    const rows = allDesignations.filter(item => Number(item.department_id) === Number(selectedDept.id));
    const tbody = document.getElementById('designationList');
    const empty = document.getElementById('noDesignationState');

    tbody.innerHTML = '';

    if (!rows.length) {
        empty.style.display = 'block';
        return;
    }

    empty.style.display = 'none';

    rows.forEach(function(d){
        const statusClass = Number(d.is_active) === 1 ? 'status-active' : 'status-inactive';
        const statusText = Number(d.is_active) === 1 ? 'Active' : 'Inactive';

        tbody.innerHTML += `
            <tr>
                <td><strong>${escapeHtml(d.name || '-')}</strong></td>
                <td>${escapeHtml(d.code || '-')}</td>
                <td>${escapeHtml(d.description || '-')}</td>
                <td><span class="status-pill ${statusClass}">${statusText}</span></td>
                <td>
                    <div class="d-flex gap-1 flex-wrap">
                        <button type="button" class="btn-edit" onclick='editDes(${JSON.stringify(d).replace(/'/g, '&#039;')})'>
                            <i class="fas fa-edit"></i>
                        </button>

                        <form method="POST" action="{{ url('/hrms/organization/designations') }}/${d.id}">
                            @csrf
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="button" class="btn-delete" onclick="confirmDelete(this.form)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        `;
    });
}

function openDesModal(){
    if (!selectedDept) {
        alert('Please select department first.');
        return;
    }

    document.getElementById('desModalTitle').innerText = 'Add Designation';
    document.getElementById('desForm').action = "{{ route('hrms.organization.designations.store') }}";
    document.getElementById('desMethodBox').innerHTML = '';

    document.getElementById('des_dept').value = selectedDept.id;
    document.getElementById('des_dept_name').value = selectedDept.name || '';
    document.getElementById('des_name').value = '';
    document.getElementById('des_code').value = getNextDesPreviewCode();
    document.getElementById('des_description').value = '';
    document.getElementById('des_is_active').value = '1';

    showModal('desModal');
}

function editDes(d){
    document.getElementById('desModalTitle').innerText = 'Edit Designation';
    document.getElementById('desForm').action = "{{ url('/hrms/organization/designations') }}/" + d.id;
    document.getElementById('desMethodBox').innerHTML = '<input type="hidden" name="_method" value="PUT">';

    document.getElementById('des_dept').value = d.department_id || selectedDept.id;
    document.getElementById('des_dept_name').value = selectedDept ? selectedDept.name : '';
    document.getElementById('des_name').value = d.name || '';
    document.getElementById('des_code').value = d.code || '';
    document.getElementById('des_description').value = d.description || '';
    document.getElementById('des_is_active').value = Number(d.is_active) === 1 ? '1' : '0';

    showModal('desModal');
}

function confirmDelete(form){
    if(confirm('Are you sure you want to delete this record?')) {
        form.submit();
    }
}

function escapeHtml(text) {
    return String(text)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

document.addEventListener('DOMContentLoaded', function(){
    if (departments.length) {
        selectDepartment(departments[0]);
    }
});
</script>
@endsection
