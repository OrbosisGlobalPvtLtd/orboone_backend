@php
    $birthdayData = $dashboard['birthdays'] ?? ($birthdays ?? []);
    $todayBirthdays = $birthdayData['today'] ?? [];
    $upcomingBirthdays = $birthdayData['upcoming'] ?? [];
    $ownBirthday = $birthdayData['own'] ?? null;
    $hasToday = !empty($todayBirthdays);
    $hasUpcoming = !empty($upcomingBirthdays);
    $showUpcoming = isset($show_upcoming) ? (bool)$show_upcoming : ($showUpcoming ?? true);
    $isOwnBirthdayToday = !empty($ownBirthday['is_birthday_today']);
@endphp

@include('dashboard.partials.birthday-modal', ['dashboard' => $dashboard])

@if ($isOwnBirthdayToday || ($showUpcoming && ($hasToday || $hasUpcoming)))
<style>
    /* HRMS Primary & Secondary Gradient Theme */
    .orb-bday-own-hero {
        background: linear-gradient(135deg, var(--orb-primary, #4F46E5) 0%, var(--orb-secondary, #9333EA) 100%);
        border: 1.5px solid rgba(255, 255, 255, 0.25) !important;
        border-radius: 22px;
        padding: 22px 26px;
        color: #ffffff;
        box-shadow: 0 16px 40px rgba(79, 70, 229, 0.22);
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
    }
    .orb-bday-own-hero::before {
        content: "";
        position: absolute;
        right: -60px;
        top: -80px;
        width: 280px;
        height: 280px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.08);
        pointer-events: none;
    }
    .orb-bday-card {
        background: linear-gradient(135deg, #ffffff 0%, #fff1f2 40%, #faf5ff 100%);
        border: 1px solid #fbcfe8 !important;
        border-radius: 22px;
        box-shadow: 0 14px 35px rgba(225, 29, 72, 0.07);
        overflow: hidden;
        margin-bottom: 22px;
        position: relative;
    }
    .orb-bday-head {
        padding: 18px 22px;
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(12px);
        border-bottom: 1px solid #ffe4e6;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .orb-bday-icon {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        background: linear-gradient(135deg, #ffe4e6 0%, #fecdd3 100%);
        color: #e11d48;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        box-shadow: 0 4px 12px rgba(225, 29, 72, 0.15);
    }
    .orb-bday-today-card {
        background: #ffffff;
        border: 1.5px solid #fecdd3 !important;
        border-radius: 18px;
        padding: 16px;
        transition: all 0.25s ease-in-out;
        box-shadow: 0 6px 20px rgba(225, 29, 72, 0.05);
    }
    .orb-bday-today-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 28px rgba(225, 29, 72, 0.12);
        border-color: #fda4af !important;
    }
    .orb-bday-avatar-wrapper {
        position: relative;
        width: 56px;
        height: 56px;
        flex-shrink: 0;
    }
    .orb-bday-avatar-img {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #f43f5e;
        box-shadow: 0 4px 12px rgba(244, 63, 94, 0.3);
    }
    .orb-bday-avatar-fallback {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: linear-gradient(135deg, #e11d48 0%, #be123c 100%);
        color: #ffffff;
        font-size: 22px;
        font-weight: 900;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 3px solid #ffffff;
        box-shadow: 0 4px 12px rgba(225, 29, 72, 0.3);
    }
    .orb-bday-crown {
        position: absolute;
        bottom: -3px;
        right: -3px;
        font-size: 15px;
        filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
    }
    .orb-bday-badge-today {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 5px 12px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        background: linear-gradient(135deg, #e11d48 0%, #be123c 100%);
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(225, 29, 72, 0.25);
    }
    .orb-bday-upcoming-item {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 10px 14px;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex: 1 1 calc(33.333% - 10px);
        min-width: 240px;
    }
    .orb-bday-upcoming-item:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.04);
    }
    .orb-bday-upcoming-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        color: #ffffff;
        font-size: 15px;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border: 2px solid #ffffff;
        box-shadow: 0 2px 8px rgba(99, 102, 241, 0.25);
    }
    .orb-bday-btn-wa:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(37, 211, 102, 0.4) !important;
        color: #ffffff !important;
    }
    .orb-bday-btn-share:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(79, 70, 229, 0.45) !important;
        color: #ffffff !important;
    }
</style>

{{-- PARTICULAR EMPLOYEE BIRTHDAY WISH BANNER (EMPLOYEE DASHBOARD ONLY WHEN IT IS THEIR BIRTHDAY) --}}
@if ($isOwnBirthdayToday)
    @php
        $empName = $ownBirthday['name'] ?? 'Team Member';
        $empDept = $ownBirthday['department'] ?? 'Orbosis Global';
        $empDesig = $ownBirthday['designation'] ?? '';
        $empImg = $ownBirthday['image_url'] ?? null;
        $empShareUrl = $ownBirthday['share_url'] ?? url('/birthday');

        $empShareMessage = "🎉 Celebrating my Birthday with Orbosis Global Pvt. Ltd.! 🎂✨\n\n" .
            "Thank you Team Orbosis Global for the wonderful birthday wishes, encouragement, and support! 🚀\n\n" .
            "🥳 Wishing for another wonderful year filled with happiness, success, and great memories!\n\n" .
            "— Team Orbosis Global\n\n" .
            "👉 View My Birthday Wish Card:\n" .
            $empShareUrl;

        $empShareText = rawurlencode($empShareMessage);
    @endphp

    <div class="orb-bday-own-hero">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 position-relative" style="z-index: 2;">
            <div class="d-flex align-items-center gap-3 min-w-0">
                <div class="position-relative flex-shrink-0 mr-3" style="width: 72px; height: 72px;">
                    @if (!empty($empImg))
                        <img src="{{ $empImg }}" alt="{{ $empName }}" class="rounded-circle shadow-lg" style="width: 72px; height: 72px; object-fit: cover; border: 3px solid #FFD54F;" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                        <div class="rounded-circle align-items-center justify-content-center text-white font-weight-black shadow-lg" style="width: 72px; height: 72px; background: rgba(255,255,255,0.2); border: 3px solid #FFD54F; font-size: 28px; display: none;">
                            {{ strtoupper(substr($empName, 0, 1)) }}
                        </div>
                    @else
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white font-weight-black shadow-lg" style="width: 72px; height: 72px; background: rgba(255,255,255,0.2); border: 3px solid #FFD54F; font-size: 28px;">
                            {{ strtoupper(substr($empName, 0, 1)) }}
                        </div>
                    @endif
                    <span class="position-absolute" style="bottom: -2px; right: -2px; font-size: 20px;">👑</span>
                </div>

                <div>
                    <div class="d-inline-flex align-items-center px-3 py-0.5 mb-1" style="background: rgba(255, 213, 79, 0.22); border: 1px solid rgba(255, 213, 79, 0.6); border-radius: 50px; font-size: 11px; font-weight: 800; color: #FFD54F;">
                        🎉 HAPPY BIRTHDAY TO YOU! 🎂
                    </div>
                    <h3 class="m-0 font-weight-black text-white" style="font-size: 22px; font-weight: 900; letter-spacing: -0.3px;">
                        Wishing you a Fantastic Birthday, {{ $empName }}!
                    </h3>
                    <p class="m-0 text-white-50" style="font-size: 13px; font-weight: 600;">
                        Wishing you joy, success &amp; a prosperous year ahead from everyone at <strong>Orbosis Global Pvt. Ltd.</strong> ✨
                    </p>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" onclick="openCelebrationModal()" class="btn px-4 py-2 text-white font-weight-bold shadow-sm" style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.35); border-radius: 50px; font-size: 13px; font-weight: 800; backdrop-filter: blur(8px);">
                    <i class="fas fa-sparkles mr-1 text-warning"></i> Replay Celebration Popup
                </button>
                <a href="https://api.whatsapp.com/send?text={{ $empShareText }}" target="_blank" class="btn px-3 py-2 text-white font-weight-bold shadow-sm" style="background: #25D366; border-radius: 50px; font-size: 13px; border: none;">
                    <i class="fab fa-whatsapp mr-1"></i> Share
                </a>
            </div>
        </div>
    </div>

    <script>
        function openCelebrationModal() {
            const modal = document.getElementById('ownBirthdayCelebrationModal');
            if (modal) {
                modal.style.opacity = '1';
                modal.style.display = 'block';
                modal.classList.add('show');
                if (typeof fireBdayConfetti === 'function') {
                    fireBdayConfetti();
                }
            }
        }
    </script>
@endif

{{-- ADMIN DASHBOARDS ONLY: BIRTHDAYS & CELEBRATIONS TEAM LIST CARD --}}
@if ($showUpcoming && ($hasToday || $hasUpcoming))
<div class="orb-bday-card">
    <div class="orb-bday-head">
        <div class="d-flex align-items-center gap-3">
            <div class="orb-bday-icon mr-3">
                <i class="fas fa-birthday-cake"></i>
            </div>
            <div>
                <h4 class="m-0 font-weight-bold" style="font-size: 17px; color: #881337; letter-spacing: -0.2px;">Birthdays & Celebrations</h4>
                <p class="m-0 text-muted" style="font-size: 12px; font-weight: 600;">Celebrating our team members</p>
            </div>
        </div>
        @if ($hasToday)
            <span class="orb-bday-badge-today">
                🎂 {{ count($todayBirthdays) }} {{ count($todayBirthdays) === 1 ? 'Birthday Today' : 'Birthdays Today' }}
            </span>
        @endif
    </div>

    <div class="p-3">
        {{-- TODAY'S BIRTHDAYS --}}
        @if ($hasToday)
            <div class="mb-3">
                <div class="d-flex align-items-center mb-2" style="font-size: 11px; font-weight: 900; color: #be123c; text-transform: uppercase; letter-spacing: 0.8px;">
                    <i class="fas fa-sparkles mr-1 text-warning"></i> Celebrating Today
                </div>
                <div class="row no-gutters" style="margin: -6px;">
                    @foreach ($todayBirthdays as $emp)
                        <div class="col-12 col-md-6 col-lg-4 p-1">
                            <div class="orb-bday-today-card">
                                <div class="d-flex align-items-center">
                                    <div class="orb-bday-avatar-wrapper mr-3">
                                        @if (!empty($emp['image_url']))
                                            <img src="{{ $emp['image_url'] }}" alt="{{ $emp['name'] }}" class="orb-bday-avatar-img" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                                            <div class="orb-bday-avatar-fallback" style="display: none;">
                                                {{ strtoupper(substr($emp['name'], 0, 1)) }}
                                            </div>
                                        @else
                                            <div class="orb-bday-avatar-fallback">
                                                {{ strtoupper(substr($emp['name'], 0, 1)) }}
                                            </div>
                                        @endif
                                        <span class="orb-bday-crown">👑</span>
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <h5 class="mb-1 text-truncate font-weight-bold" style="font-size: 15px; color: #1e293b;" title="{{ $emp['name'] }}">
                                            {{ $emp['name'] }}
                                        </h5>
                                        <div class="d-flex align-items-center flex-wrap gap-1 mb-1">
                                            @if (!empty($emp['department']))
                                                <span class="badge px-2 py-1 mr-1" style="background: #ffe4e6; color: #9f1239; font-size: 10.5px; font-weight: 700; border-radius: 6px;">
                                                    {{ $emp['department'] }}
                                                </span>
                                            @endif
                                            @if (!empty($emp['designation']))
                                                <span class="badge px-2 py-1" style="background: #f3e8ff; color: #6b21a8; font-size: 10.5px; font-weight: 700; border-radius: 6px;">
                                                    {{ $emp['designation'] }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between flex-wrap" style="margin-top: 4px; margin-bottom: 4px;">
                                            <span class="badge" style="font-size: 10px; font-weight: 800; color: #e11d48; background: #fff1f2; border: 1px solid #fecdd3; border-radius: 50px; padding: 3px 10px;">
                                                🎉 Happy Birthday!
                                            </span>
                                        </div>

                                        @php
                                            $empShareToken = $emp['share_token'] ?? (!empty($emp['employee_code']) ? app(\App\Services\HRMS\Birthday\BirthdayShareService::class)->generateToken(new \App\Models\HRMS\Employee\EmployeeM(['id' => $emp['id'] ?? 0, 'employee_code' => $emp['employee_code']])) : null);
                                            $empShareUrl = $emp['share_url'] ?? ($empShareToken ? url('/birthday/' . $empShareToken) : url('/birthday'));
                                            $adminWishMessage = "🎉 Happy Birthday " . $emp['name'] . "! 🎂✨\n\n" .
                                                "On behalf of Management & the entire team at Orbosis Global Pvt. Ltd., wishing you a fantastic birthday, happiness, success, and great achievements in the year ahead! 🚀\n\n" .
                                                "We are proud to have you as part of our team. Have a wonderful day! 🥳\n\n" .
                                                "— Team Orbosis Global\n\n" .
                                                "👉 View Your Birthday Wish Card:\n" .
                                                $empShareUrl;
                                            $adminWishText = rawurlencode($adminWishMessage);
                                        @endphp

                                        <script id="bdayEmpData-{{ $emp['id'] }}" type="application/json">{!! json_encode($emp, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}</script>

                                        <div class="d-flex align-items-center justify-content-between gap-2" style="margin-top: 14px; padding-top: 12px; border-top: 1px dashed rgba(244, 63, 94, 0.22);">
                                            <a href="https://api.whatsapp.com/send?text={{ $adminWishText }}" target="_blank" class="btn btn-sm text-white font-weight-bold d-inline-flex align-items-center justify-content-center orb-bday-btn-wa" style="background: linear-gradient(135deg, #25D366 0%, #128C7E 100%); border-radius: 12px; font-size: 11.5px; font-weight: 800; padding: 7px 14px; text-decoration: none; border: none; box-shadow: 0 4px 12px rgba(37, 211, 102, 0.28); transition: all 0.2s ease;">
                                                <i class="fab fa-whatsapp mr-1.5" style="font-size: 13px;"></i> WhatsApp Wish
                                            </a>
                                            
                                            <button type="button" data-bday-id="{{ $emp['id'] }}" class="btn btn-sm text-white font-weight-bold d-inline-flex align-items-center justify-content-center orb-bday-btn-share btn-trigger-bday-card" style="background: linear-gradient(135deg, var(--orb-primary, #4F46E5) 0%, var(--orb-secondary, #9333EA) 100%); border-radius: 12px; font-size: 11.5px; font-weight: 800; padding: 7px 14px; border: none; box-shadow: 0 4px 14px rgba(79, 70, 229, 0.32); transition: all 0.2s ease;">
                                                <i class="fas fa-sparkles mr-1.5" style="color: #FFD54F; font-size: 12px;"></i> Share Card 📸
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- UPCOMING BIRTHDAYS --}}
        @if ($hasUpcoming)
            <div class="{{ $hasToday ? 'mt-3 pt-3 border-top' : '' }}" style="border-color: #ffe4e6 !important;">
                <div class="d-flex align-items-center mb-2" style="font-size: 11px; font-weight: 900; color: #475569; text-transform: uppercase; letter-spacing: 0.8px;">
                    <i class="far fa-calendar-alt mr-1 text-primary"></i> Upcoming Birthdays (Next 7 Days)
                </div>
                <div class="d-flex flex-wrap" style="gap: 10px;">
                    @foreach ($upcomingBirthdays as $emp)
                        <div class="orb-bday-upcoming-item">
                            <div class="d-flex align-items-center min-w-0 mr-2">
                                @if (!empty($emp['image_url']))
                                    <img src="{{ $emp['image_url'] }}" alt="{{ $emp['name'] }}" style="width: 38px; height: 38px; border-radius: 50%; object-fit: cover; border: 2px solid #6366f1;" class="mr-2" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                                    <div class="orb-bday-upcoming-avatar mr-2" style="display: none;">
                                        {{ strtoupper(substr($emp['name'], 0, 1)) }}
                                    </div>
                                @else
                                    <div class="orb-bday-upcoming-avatar mr-2">
                                        {{ strtoupper(substr($emp['name'], 0, 1)) }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <strong class="d-block text-truncate" style="font-size: 13px; color: #1e293b;" title="{{ $emp['name'] }}">{{ $emp['name'] }}</strong>
                                    <small class="text-muted d-block text-truncate" style="font-size: 11px;">
                                        {{ $emp['department'] ?? 'Orbosis Team' }}
                                    </small>
                                </div>
                            </div>
                            <span class="badge px-2 py-1 flex-shrink-0" style="background: #e0e7ff; color: #3730a3; font-size: 11px; font-weight: 800; border-radius: 8px;">
                                <i class="far fa-calendar mr-1"></i>{{ $emp['date_of_birth'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endif
@endif
