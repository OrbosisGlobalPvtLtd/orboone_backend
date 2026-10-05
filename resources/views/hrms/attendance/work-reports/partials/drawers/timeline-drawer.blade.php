<!-- Slide-Out Employee Daily History Drawer -->
<div class="drawer-overlay" id="drawerOverlay" onclick="closeEmployeeTimelineDrawer()"></div>

<div class="timeline-drawer" id="employeeTimelineDrawer">
    <div class="drawer-header">
        <div class="d-flex align-items-center" style="gap: 12px;">
            <div class="emp-card-avatar border-white bg-white text-primary" id="drawerAvatar" style="width:44px; height:44px;">
                <span>E</span>
            </div>
            <div>
                <h5 class="mb-0 text-white font-weight-bold" id="drawerEmpName">Employee Name</h5>
                <div class="text-white-50 font-weight-bold" style="font-size:12px;" id="drawerEmpMeta">Code &bull; Department</div>
            </div>
        </div>
        <button type="button" class="close-btn" onclick="closeEmployeeTimelineDrawer()">&times;</button>
    </div>

    <div class="p-3 bg-white border-bottom d-flex align-items-center justify-content-between text-muted font-weight-bold" style="font-size:13px;">
        <span><i class="fas fa-calendar-alt text-primary mr-1"></i> Daily Work Log Timeline</span>
        <span class="badge badge-primary px-3 py-1" style="border-radius: 50px;" id="drawerTotalCount">0 Logs</span>
    </div>

    <div class="drawer-body">
        <div class="timeline-list" id="drawerTimelineList">
            <!-- Rendered dynamically -->
        </div>
    </div>
</div>
