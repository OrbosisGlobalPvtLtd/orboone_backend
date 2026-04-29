@extends('layouts.panel', ['active' => 'employees'])

@section('page_title', 'Department & Designation')

@section('_content')
<style>
:root{
    --orb-primary:#4B00E8;
    --orb-secondary:#8600EE;
    --orb-pink:#D400D5;
    --orb-bg:#F7F8FC;
    --orb-card:#fff;
    --orb-border:#E7EAF3;
    --orb-text:#101828;
    --orb-muted:#667085;
    --orb-soft:#F4F2FF;
    --orb-shadow:0 14px 34px rgba(16,24,40,.07);
}

.org-page{
    min-height:calc(100vh - 90px);
    padding:16px 10px 32px;
    background:
        radial-gradient(circle at top left, rgba(75,0,232,.07), transparent 28%),
        radial-gradient(circle at top right, rgba(212,0,213,.06), transparent 28%),
        var(--orb-bg);
}

.org-container{
    max-width:1320px;
    margin:0 auto;
}

.org-hero{
    border-radius:24px;
    padding:22px;
    color:#fff;
    margin-bottom:16px;
    background:linear-gradient(135deg,#4B00E8,#8600EE,#D400D5);
    box-shadow:0 18px 42px rgba(75,0,232,.18);
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:16px;
}

.org-hero h3{
    margin:0;
    font-size:1.45rem;
    font-weight:950;
}

.org-hero p{
    margin:7px 0 0;
    color:rgba(255,255,255,.82);
    font-size:.88rem;
    font-weight:650;
}

.org-layout{
    display:grid;
    grid-template-columns:390px minmax(0,1fr);
    gap:16px;
}

.org-card{
    background:#fff;
    border:1px solid var(--orb-border);
    border-radius:22px;
    box-shadow:var(--orb-shadow);
    overflow:hidden;
}

.org-card-head{
    padding:16px 18px;
    border-bottom:1px solid #EEF1F6;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    background:linear-gradient(180deg,#fff,#FCFCFF);
}

.org-title{
    display:flex;
    align-items:center;
    gap:11px;
}

.org-icon{
    width:42px;
    height:42px;
    border-radius:15px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:var(--orb-soft);
    color:var(--orb-primary);
}

.org-title h5{
    margin:0;
    font-size:1rem;
    font-weight:950;
    color:var(--orb-text);
}

.org-title p{
    margin:3px 0 0;
    font-size:.75rem;
    font-weight:650;
    color:var(--orb-muted);
}

.org-body{
    padding:16px;
}

.btn-orb{
    border:0;
    border-radius:13px;
    padding:9px 13px;
    font-size:.82rem;
    font-weight:950;
    background:linear-gradient(135deg,#4B00E8,#8600EE);
    color:#fff!important;
    box-shadow:0 8px 18px rgba(75,0,232,.16);
}

.btn-soft{
    border-radius:11px;
    padding:8px 11px;
    font-size:.76rem;
    font-weight:900;
    background:#F4F6FB;
    border:1px solid #E5E7EB;
    color:#111827!important;
}

.btn-edit{
    border:0;
    border-radius:11px;
    padding:8px 11px;
    font-size:.76rem;
    font-weight:900;
    background:#FFF7E6;
    color:#B54708!important;
}

.btn-delete{
    border:0;
    border-radius:11px;
    padding:8px 11px;
    font-size:.76rem;
    font-weight:900;
    background:#FEE2E2;
    color:#991B1B!important;
}

.dept-list{
    display:flex;
    flex-direction:column;
    gap:10px;
}

.dept-item{
    width:100%;
    border:1px solid #EEF1F6;
    background:#fff;
    border-radius:17px;
    padding:13px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    cursor:pointer;
    transition:.2s ease;
    text-align:left;
}

.dept-item:hover,
.dept-item.active{
    border-color:rgba(75,0,232,.22);
    background:linear-gradient(135deg,#F7F4FF,#FFF9FD);
    transform:translateY(-1px);
}

.dept-main{
    display:flex;
    align-items:center;
    gap:11px;
    min-width:0;
}

.dept-avatar{
    width:42px;
    height:42px;
    border-radius:15px;
    background:linear-gradient(135deg,#4B00E8,#8600EE);
    color:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:950;
    flex:0 0 auto;
}

.dept-name{
    font-size:.9rem;
    font-weight:950;
    color:var(--orb-text);
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}

.dept-meta{
    margin-top:2px;
    font-size:.72rem;
    font-weight:700;
    color:var(--orb-muted);
}

.dept-code{
    border-radius:999px;
    padding:6px 9px;
    background:#F4F2FF;
    color:var(--orb-primary);
    font-size:.72rem;
    font-weight:950;
    flex:0 0 auto;
}

.selected-box{
    border-radius:18px;
    padding:15px;
    background:linear-gradient(135deg,#F4F2FF,#FFF7FB);
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
    font-size:1.1rem;
    font-weight:950;
}

.selected-box p{
    margin:4px 0 0;
    color:var(--orb-muted);
    font-size:.78rem;
    font-weight:650;
}

.table-wrap{
    border:1px solid #EEF1F6;
    border-radius:18px;
    overflow:hidden;
}

.org-table{
    margin:0;
}

.org-table thead th{
    border-top:0;
    background:#FBFCFF;
    color:#667085;
    font-size:.72rem;
    font-weight:950;
    text-transform:uppercase;
    padding:13px 15px;
}

.org-table tbody td{
    border-top:1px solid #F0F2F7;
    padding:14px 15px;
    vertical-align:middle;
    font-size:.84rem;
    font-weight:700;
}

.status-pill{
    display:inline-flex;
    border-radius:999px;
    padding:7px 10px;
    font-size:.72rem;
    font-weight:950;
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
    padding:44px 18px;
    text-align:center;
}

.empty-icon{
    width:68px;
    height:68px;
    border-radius:24px;
    background:#F4F2FF;
    color:var(--orb-primary);
    display:flex;
    align-items:center;
    justify-content:center;
    margin:0 auto 13px;
    font-size:1.55rem;
}

.empty-state h4{
    margin:0;
    color:var(--orb-text);
    font-weight:950;
}

.empty-state p{
    margin:7px 0 0;
    color:var(--orb-muted);
    font-size:.86rem;
    font-weight:650;
}

.modal-content{
    border:0;
    border-radius:22px;
    box-shadow:0 25px 70px rgba(15,23,42,.18);
}

.modal-header{
    border-bottom:1px solid #EEF1F6;
    padding:16px 18px;
}

.modal-title{
    font-weight:950;
    color:var(--orb-text);
}

.modal-body{
    padding:18px;
}

.form-label{
    font-size:.78rem;
    font-weight:900;
    color:#344054;
}

.form-control,
.form-select{
    min-height:44px;
    border-radius:13px;
    border:1px solid #DDE3EE;
    font-size:.86rem;
    font-weight:650;
}

.form-control:focus,
.form-select:focus{
    border-color:var(--orb-secondary);
    box-shadow:0 0 0 .16rem rgba(134,0,238,.10);
}

@media(max-width:991px){
    .org-layout{
        grid-template-columns:1fr;
    }
    .org-hero{
        flex-direction:column;
        align-items:flex-start;
    }
}
</style>

<div class="org-page">
    <div class="org-container">

        @if(session('success'))
            <div class="alert alert-success rounded-4">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger rounded-4">
                <strong>Please fix these errors:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="org-hero">
            <div>
                <h3>Department & Designation Setup</h3>
                <p>Manage company departments and map designations department-wise for clean employee onboarding.</p>
            </div>

            <button class="btn btn-light fw-bold rounded-pill px-4" onclick="openDeptModal()">
                <i class="fas fa-plus mr-1"></i> Add Department
            </button>
        </div>

        <div class="org-layout">

            {{-- LEFT: DEPARTMENTS --}}
            <div class="org-card">
                <div class="org-card-head">
                    <div class="org-title">
                        <div class="org-icon"><i class="fas fa-building"></i></div>
                        <div>
                            <h5>Departments</h5>
                            <p>Select department to manage designations</p>
                        </div>
                    </div>
                </div>

                <div class="org-body">
                    <div class="dept-list">
                        @foreach($departments as $dept)
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
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- RIGHT: DESIGNATIONS --}}
            <div class="org-card">
                <div class="org-card-head">
                    <div class="org-title">
                        <div class="org-icon"><i class="fas fa-id-badge"></i></div>
                        <div>
                            <h5>Designations</h5>
                            <p id="designationSubTitle">Select department from left side</p>
                        </div>
                    </div>

                    <button class="btn-orb" id="addDesignationBtn" onclick="openDesModal()" disabled>
                        <i class="fas fa-plus mr-1"></i> Add Designation
                    </button>
                </div>

                <div class="org-body">
                    <div id="noDeptState" class="empty-state">
                        <div class="empty-icon"><i class="fas fa-hand-pointer"></i></div>
                        <h4>Select a department</h4>
                        <p>Click any department from left side to see related designations.</p>
                    </div>

                    <div id="designationPanel" style="display:none;">
                        <div class="selected-box">
                            <div>
                                <h4 id="selectedDeptName">-</h4>
                                <p id="selectedDeptMeta">-</p>
                            </div>

                            <div class="d-flex gap-2 flex-wrap">
                                <button class="btn-edit" onclick="openSelectedDeptEdit()">
                                    <i class="fas fa-edit mr-1"></i> Edit Dept
                                </button>

                                <form id="deleteDeptForm" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn-delete" onclick="confirmDelete(this.form)">
                                        <i class="fas fa-trash mr-1"></i> Delete Dept
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
                                        <th width="150">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="designationList"></tbody>
                            </table>
                        </div>

                        <div id="noDesignationState" class="empty-state" style="display:none;">
                            <div class="empty-icon"><i class="fas fa-id-card"></i></div>
                            <h4>No designations added</h4>
                            <p>Add first designation for this department.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- DEPARTMENT MODAL --}}
<div class="modal fade" id="deptModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form id="deptForm" method="POST" class="modal-content">
            @csrf
            <div id="deptMethodBox"></div>

            <div class="modal-header">
                <h5 class="modal-title" id="deptModalTitle">Add Department</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Department Name</label>
                    <input type="text" name="name" id="dept_name" class="form-control" placeholder="Example: HR Department" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Code</label>
                    <input type="text" name="code" id="dept_code" class="form-control" placeholder="HR" maxlength="3" required>
                </div>

                <div class="mb-0">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" id="dept_address" class="form-control" placeholder="Department location / office" required>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-soft" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-orb">Save Department</button>
            </div>
        </form>
    </div>
</div>

{{-- DESIGNATION MODAL --}}
<div class="modal fade" id="desModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form id="desForm" method="POST" class="modal-content">
            @csrf
            <div id="desMethodBox"></div>
            <input type="hidden" name="department_id" id="des_dept">

            <div class="modal-header">
                <h5 class="modal-title" id="desModalTitle">Add Designation</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Selected Department</label>
                    <input type="text" id="des_dept_name" class="form-control" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Designation Name</label>
                    <input type="text" name="name" id="des_name" class="form-control" placeholder="Example: HR Executive" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Code</label>
                    <input type="text" name="code" id="des_code" class="form-control" placeholder="Optional code">
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="des_description" class="form-control" rows="3" placeholder="Designation description"></textarea>
                </div>

                <div class="mb-0">
                    <label class="form-label">Status</label>
                    <select name="is_active" id="des_is_active" class="form-select">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-soft" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-orb">Save Designation</button>
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
    } else {
        document.getElementById(id).classList.add('show');
        document.getElementById(id).style.display = 'block';
        document.body.classList.add('modal-open');
    }
}

function hideModal(id) {
    if (window.jQuery && typeof $('#' + id).modal === 'function') {
        $('#' + id).modal('hide');
    }
}

function openDeptModal(){
    document.getElementById('deptModalTitle').innerText = 'Add Department';
    document.getElementById('deptForm').action = "{{ route('departments.store') }}";
    document.getElementById('deptMethodBox').innerHTML = '';
    document.getElementById('dept_name').value = '';
    document.getElementById('dept_code').value = '';
    document.getElementById('dept_address').value = '';
    showModal('deptModal');
}

function editDept(dept){
    document.getElementById('deptModalTitle').innerText = 'Edit Department';
    document.getElementById('deptForm').action = "{{ url('/hrms/departments') }}/" + dept.id;
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
    document.getElementById('deleteDeptForm').action = "{{ url('/hrms/departments') }}/" + dept.id;

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
                        <button type="button" class="btn-edit" onclick='editDes(${JSON.stringify(d)})'>
                            <i class="fas fa-edit"></i>
                        </button>

                        <form method="POST" action="{{ url('/hrms/designations') }}/${d.id}">
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
    document.getElementById('desForm').action = "{{ route('designations.store') }}";
    document.getElementById('desMethodBox').innerHTML = '';
    document.getElementById('des_dept').value = selectedDept.id;
    document.getElementById('des_dept_name').value = selectedDept.name || '';
    document.getElementById('des_name').value = '';
    document.getElementById('des_code').value = '';
    document.getElementById('des_description').value = '';
    document.getElementById('des_is_active').value = '1';
    showModal('desModal');
}

function editDes(d){
    document.getElementById('desModalTitle').innerText = 'Edit Designation';
    document.getElementById('desForm').action = "{{ url('/hrms/designations') }}/" + d.id;
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