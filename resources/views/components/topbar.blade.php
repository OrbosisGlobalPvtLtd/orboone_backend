<nav class="navbar navbar-expand-lg bg-white w-100"
     style="
        border-bottom: 1px solid #e5e7eb;
        min-height: 72px;
        position: sticky;
        top: 0;
        z-index: 1050;
        backdrop-filter: blur(8px);
        padding: 0 20px;
     ">

    <div class="container-fluid p-0">
        <div class="d-flex align-items-center justify-content-between w-100">

            <!-- LEFT SIDE -->
            <div class="d-flex align-items-center" style="min-width: 0;">
                <button type="button" class="sidebar-toggle" onclick="toggleSidebar()" style="
                            width: 42px;
                            height: 42px;
                            border-radius: 12px;
                            border: 1px solid #e5e7eb;
                            background: #fff;
                            margin-right: 14px;
                            transition: 0.2s;
                        "  onmouseover="this.style.background='#f5f3ff'"
                        onmouseout="this.style.background='#fff'">
            <i class="fa-solid fa-bars-staggered" style="color:#4B00E8;"></i>
        </button>

               

                <!-- TITLE -->
                <div style="min-width: 0;">
                    <h5 class="mb-0 fw-bold text-dark" style="line-height: 1.2;">
                        {{ ucfirst($active ?? 'dashboard') }}
                    </h5>
                    <small class="text-muted d-block" style="font-size: 12px;">
                        Orbosis HRMS Panel
                    </small>
                </div>

            </div>

            <!-- RIGHT SIDE -->
            <div class="d-flex align-items-center" style="gap: 10px;">

                <!-- SEARCH -->
                <button type="button"
                        style="
                            width: 40px;
                            height: 40px;
                            border-radius: 12px;
                            border: 1px solid #e5e7eb;
                            background: #fff;
                        ">
                    <i class="fas fa-search text-muted"></i>
                </button>

                <!-- NOTIFICATION -->
                <button type="button"
                        style="
                            width: 40px;
                            height: 40px;
                            border-radius: 12px;
                            border: 1px solid #e5e7eb;
                            background: #fff;
                            position: relative;
                        ">
                    <i class="fas fa-bell text-muted"></i>

                    <!-- badge -->
                    <span style="
                        position: absolute;
                        top: 6px;
                        right: 6px;
                        width: 8px;
                        height: 8px;
                        background: #ec4e74;
                        border-radius: 50%;
                    "></span>
                </button>

                <!-- PROFILE -->
                @auth
                @php
                    $topbarUser = Auth::user();
                    $topbarEmployee = $topbarUser?->employee;
                    $topbarUserImage = trim((string) data_get($topbarUser, 'profile_image'));
                    $topbarEmployeeImage = trim((string) data_get($topbarEmployee, 'profile.profile_image'));
                    $topbarImage = $topbarUserImage !== '' ? $topbarUserImage : $topbarEmployeeImage;

                    if ($topbarImage !== '' && preg_match('/^https?:\/\//i', $topbarImage)) {
                        $topbarAvatar = $topbarImage;
                    } elseif ($topbarImage !== '' && str_starts_with($topbarImage, '/')) {
                        $topbarAvatar = $topbarImage;
                    } elseif ($topbarImage !== '' && str_starts_with($topbarImage, 'storage/')) {
                        $topbarAvatar = asset($topbarImage);
                    } elseif ($topbarImage !== '') {
                        $topbarAvatar = asset('storage/'.$topbarImage);
                    } else {
                        $topbarAvatar = null;
                    }

                    $topbarInitial = strtoupper(substr(trim($topbarUser?->name ?? ''), 0, 1));
                @endphp
                <div class="dropdown">

                    <div class="d-flex align-items-center"
                         data-toggle="dropdown"
                         style="
                            cursor: pointer;
                            border: 1px solid #e5e7eb;
                            border-radius: 30px;
                            padding: 5px 10px;
                            background: #fff;
                            transition: 0.2s;
                         "
                         onmouseover="this.style.background='#f9f9ff'"
                         onmouseout="this.style.background='#fff'">

                        @if(!empty($topbarAvatar))
                            <img src="{{ $topbarAvatar }}"
                                 alt="{{ $topbarUser?->name ?? 'User' }}"
                                 style="
                                    width: 36px;
                                    height: 36px;
                                    border-radius: 50%;
                                    object-fit: cover;
                                    margin-right: 8px;
                                 ">
                            <div class="topbar-avatar-fallback" style="
                                width:36px;
                                height:36px;
                                border-radius:50%;
                                background:#F4F2FF;
                                color:#4B00E8;
                                display:none;
                                align-items:center;
                                justify-content:center;
                                font-size:13px;
                                font-weight:900;
                                margin-right:8px;
                            ">
                                @if($topbarInitial)
                                    {{ $topbarInitial }}
                                @else
                                    <i class="fas fa-user"></i>
                                @endif
                            </div>
                        @else
                            <div class="topbar-avatar-fallback" style="
                                width:36px;
                                height:36px;
                                border-radius:50%;
                                background:#F4F2FF;
                                color:#4B00E8;
                                display:flex;
                                align-items:center;
                                justify-content:center;
                                font-size:13px;
                                font-weight:900;
                                margin-right:8px;
                            ">
                                @if($topbarInitial)
                                    {{ $topbarInitial }}
                                @else
                                    <i class="fas fa-user"></i>
                                @endif
                            </div>
                        @endif

                        <div class="d-none d-md-block" style="line-height:1.1;">
                            <div style="font-size: 14px; font-weight: 600; color:#111;">
                                {{ $topbarUser->name }}
                            </div>
                            <!-- <small style="color:#6b7280; font-size:11px;">
                                HRMS User
                            </small> -->
                        </div>

                        <i class="fas fa-chevron-down ml-2 text-muted" style="font-size:10px;"></i>
                    </div>

                    <!-- DROPDOWN -->
                    <div class="dropdown-menu dropdown-menu-right shadow border-0"
                         style="border-radius: 12px; padding: 10px; min-width: 180px;">

                        <a class="dropdown-item py-2 rounded" href="{{ route('profile.index') }}">
                            <i class="fas fa-user mr-2 text-muted"></i> My Profile
                        </a>

                        <a class="dropdown-item py-2 rounded" href="{{ route('profile.index') }}#change-password">
                            <i class="fas fa-lock mr-2 text-muted"></i> Change Password
                        </a>

                        <div class="dropdown-divider"></div>

                        <a class="dropdown-item py-2 text-danger rounded"
                           href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>

                </div>
                @endauth

            </div>
        </div>
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.dropdown img').forEach(function (image) {
            image.addEventListener('error', function () {
                var fallback = image.nextElementSibling;

                image.style.display = 'none';

                if (fallback && fallback.classList.contains('topbar-avatar-fallback')) {
                    fallback.style.display = 'flex';
                }
            });
        });
    });
</script>
