@php
    $ownBirthday = $dashboard['birthdays']['own'] ?? null;
@endphp

@php
    $todayBirthdaysList = $dashboard['birthdays']['today'] ?? [];
    $isOwnBirthdayToday = !empty($ownBirthday['is_birthday_today']);
@endphp

@if ($isOwnBirthdayToday || (!empty($todayBirthdaysList) && count($todayBirthdaysList) > 0))
@php
    $employeeName = $ownBirthday['name'] ?? ($todayBirthdaysList[0]['name'] ?? 'Team Member');
    $department = $ownBirthday['department'] ?? ($todayBirthdaysList[0]['department'] ?? 'Orbosis Global');
    $designation = $ownBirthday['designation'] ?? ($todayBirthdaysList[0]['designation'] ?? '');
    $imageUrl = $ownBirthday['image_url'] ?? ($todayBirthdaysList[0]['image_url'] ?? null);
    $shareUrl = $ownBirthday['share_url'] ?? ($todayBirthdaysList[0]['share_url'] ?? url('/birthday'));

    $shareMessage = $isOwnBirthdayToday 
        ? "🎉 Celebrating my Birthday with Orbosis Global Pvt. Ltd.! 🎂✨\n\n" .
          "Thank you Team Orbosis Global for the wonderful birthday wishes, encouragement, and support! 🚀\n\n" .
          "🥳 Wishing for another wonderful year filled with happiness, success, and great memories!\n\n" .
          "— Team Orbosis Global\n\n" .
          "👉 View My Birthday Wish Card:\n" .
          $shareUrl
        : "🎉 Happy Birthday " . $employeeName . "! 🎂✨\n\n" .
          "On behalf of Management & the entire team at Orbosis Global Pvt. Ltd., wishing you a fantastic birthday, happiness, success, and great achievements in the year ahead! 🚀\n\n" .
          "We are proud to have you as part of our team. Have a wonderful day! 🥳\n\n" .
          "— Team Orbosis Global\n\n" .
          "👉 View Your Birthday Wish Card:\n" .
          $shareUrl;

    $shareText = rawurlencode($shareMessage);
    $modalShowClass = $isOwnBirthdayToday ? 'show' : '';
@endphp

<style>
    /* HRMS Theme Responsive Birthday Celebration Modal */
    .bday-modal-backdrop {
        display: none;
        opacity: 0;
        background: rgba(16, 12, 42, 0.88) !important;
        backdrop-filter: blur(16px) !important;
        -webkit-backdrop-filter: blur(16px) !important;
        z-index: 10050 !important;
        padding: 0.75rem !important;
        transition: opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .bday-modal-backdrop.show {
        display: block !important;
        opacity: 1 !important;
    }
    .bday-modal-dialog {
        max-width: 460px;
        width: 100%;
        margin: 0.5rem auto !important;
        max-height: calc(100vh - 1rem);
        display: flex;
        align-items: center;
    }
    .bday-modal-content {
        border-radius: 28px !important;
        /* HRMS Theme Primary & Secondary Gradient */
        background: linear-gradient(135deg, var(--orb-primary, #4F46E5) 0%, var(--orb-secondary, #9333EA) 100%) !important;
        color: #ffffff !important;
        border: 1.5px solid rgba(255, 255, 255, 0.28) !important;
        box-shadow: 0 25px 65px rgba(79, 70, 229, 0.45) !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        max-height: calc(100vh - 1.5rem) !important;
        width: 100%;
        position: relative;
        -webkit-overflow-scrolling: touch;
    }
    /* Custom Scrollbar */
    .bday-modal-content::-webkit-scrollbar {
        width: 5px;
    }
    .bday-modal-content::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.05);
    }
    .bday-modal-content::-webkit-scrollbar-thumb {
        background: rgba(255, 213, 79, 0.5);
        border-radius: 10px;
    }

    .bday-title {
        font-size: clamp(22px, 5.5vw, 32px);
        font-weight: 900;
        letter-spacing: -0.5px;
        color: #FFFFFF;
        text-shadow: 0 4px 14px rgba(0,0,0,0.3);
    }
    .bday-name {
        font-size: clamp(18px, 4.5vw, 24px);
        color: #FFD54F;
        text-shadow: 0 2px 8px rgba(0,0,0,0.3);
    }
    .bday-dept {
        font-size: clamp(11.5px, 3vw, 13px);
        font-weight: 600;
    }
    .bday-avatar-box {
        width: clamp(80px, 20vw, 110px);
        height: clamp(80px, 20vw, 110px);
        position: relative;
        margin: 0 auto;
    }
    .bday-avatar-img, .bday-avatar-fallback {
        width: clamp(80px, 20vw, 110px);
        height: clamp(80px, 20vw, 110px);
        border-radius: 50% !important;
        object-fit: cover !important;
        font-size: clamp(30px, 8vw, 42px);
    }
    .bday-crown {
        width: clamp(26px, 6vw, 34px);
        height: clamp(26px, 6vw, 34px);
        border-radius: 50% !important;
        font-size: clamp(13px, 3.5vw, 18px);
        bottom: -2px;
        right: -2px;
    }
    .bday-banner-emojis {
        font-size: clamp(26px, 6vw, 36px);
        margin-bottom: 6px;
    }
    .bday-cake-emoji {
        font-size: clamp(28px, 7vw, 42px);
        margin-bottom: 8px;
    }
    .bday-wish-text {
        font-size: clamp(12.5px, 3.2vw, 14.5px);
        line-height: 1.5;
    }
    .bday-share-btn {
        width: clamp(40px, 10vw, 48px) !important;
        height: clamp(40px, 10vw, 48px) !important;
        font-size: clamp(16px, 4vw, 20px) !important;
        cursor: pointer;
    }
    .bday-share-btn:hover {
        transform: translateY(-2px);
        background: rgba(255,255,255,0.3) !important;
    }
    .bday-action-btn {
        padding: clamp(10px, 2.5vw, 14px) 20px !important;
        font-size: clamp(14px, 3.5vw, 16px) !important;
        border-radius: 18px !important;
        cursor: pointer;
        background: #FFFFFF !important;
        color: var(--orb-primary, #4F46E5) !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .bday-action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25) !important;
    }
    .bday-toast {
        display: none;
        position: absolute;
        top: 60px;
        left: 50%;
        transform: translateX(-50%);
        background: #10B981;
        color: #ffffff;
        font-size: 12px;
        font-weight: 800;
        padding: 8px 18px;
        border-radius: 50px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.3);
        z-index: 10;
    }

    @media (max-height: 740px) {
        .bday-modal-dialog {
            margin: 0.25rem auto !important;
        }
        .bday-modal-header-padding {
            padding: 12px 16px 0 16px !important;
        }
        .bday-modal-body-padding {
            padding: 12px 16px !important;
        }
        .bday-space-y {
            margin-bottom: 6px !important;
        }
        .bday-share-container {
            margin-top: 10px !important;
            margin-bottom: 12px !important;
            padding-top: 10px !important;
        }
    }
    @media (max-width: 480px) {
        .bday-modal-dialog {
            width: 96% !important;
            margin: 0.25rem auto !important;
        }
        .bday-share-item {
            width: 50px !important;
        }
    }
</style>

<!-- Celebration Modal Backdrop -->
<div class="modal fade {{ $modalShowClass }} bday-modal-backdrop" id="ownBirthdayCelebrationModal" tabindex="-1" role="dialog" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered bday-modal-dialog" role="document">
        <div class="modal-content border-0 position-relative bday-modal-content">
            
            <canvas id="bdayConfettiCanvas" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 1;"></canvas>

            {{-- Toast Notification --}}
            <div id="bdayShareToast" class="bday-toast">
                <i class="fas fa-check-circle mr-1"></i> Wish Card Link Copied!
            </div>

            {{-- Exportable Card Container (Only the Card itself, without buttons/controls) --}}
            <div id="bdayExportableCard" style="background: linear-gradient(135deg, var(--orb-primary, #4F46E5) 0%, var(--orb-secondary, #9333EA) 100%); border-radius: 28px; padding: 24px 20px 20px; position: relative; z-index: 2;">
                
                {{-- Header Badge --}}
                <div class="d-flex align-items-center justify-content-between mb-2 bday-modal-header-padding">
                    <div class="d-inline-flex align-items-center px-2.5 py-1" style="background: rgba(255, 213, 79, 0.22); border: 1px solid rgba(255, 213, 79, 0.65); border-radius: 50px; font-size: 10px; font-weight: 800; letter-spacing: 0.8px; color: #FFD54F; text-transform: uppercase;">
                        ⚡ ORBOSIS GLOBAL PVT. LTD.
                    </div>
                    <button type="button" class="btn btn-sm px-3 py-1 text-white opacity-80 bday-skip-btn" onclick="closeBirthdayModal()" style="background: rgba(255, 255, 255, 0.18); border: 1px solid rgba(255, 255, 255, 0.3); border-radius: 50px; font-size: 11px; font-weight: 700;">
                        Skip &times;
                    </button>
                </div>

                {{-- Body Content --}}
                <div class="text-center bday-modal-body-padding">
                    
                    {{-- Emojis Banner --}}
                    <div class="bday-banner-emojis" style="line-height: 1; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));">
                        🎉 🎁 🎊
                    </div>

                    <h2 class="bday-title mb-1">
                        Happy Birthday
                    </h2>
                    
                    <h4 class="bday-name font-weight-bold mb-1">
                        {{ $employeeName }}
                    </h4>
                    
                    <p class="bday-dept mb-2 mb-sm-3 text-white-50 bday-space-y">
                        {{ $department }} @if($designation) &bull; {{ $designation }} @endif
                    </p>

                    {{-- Avatar Box --}}
                    <div class="position-relative mx-auto mb-2 mb-sm-3 bday-avatar-box bday-space-y" style="width: 105px; height: 105px;">
                        @if (!empty($imageUrl))
                            <img src="{{ $imageUrl }}" alt="{{ $employeeName }}" class="shadow-lg bday-avatar-img" style="width: 105px !important; height: 105px !important; border-radius: 50% !important; object-fit: cover !important; border: 3.5px solid #FFD54F !important; box-shadow: 0 8px 25px rgba(255, 213, 79, 0.45);" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                            <div class="align-items-center justify-content-center text-white font-weight-black shadow-lg bday-avatar-fallback" style="width: 105px !important; height: 105px !important; border-radius: 50% !important; background: rgba(255,255,255,0.22); border: 3.5px solid #FFD54F !important; display: none;">
                                {{ strtoupper(substr($employeeName, 0, 1)) }}
                            </div>
                        @else
                            <div class="d-flex align-items-center justify-content-center text-white font-weight-black shadow-lg bday-avatar-fallback" style="width: 105px !important; height: 105px !important; border-radius: 50% !important; background: rgba(255,255,255,0.22); border: 3.5px solid #FFD54F !important;">
                                {{ strtoupper(substr($employeeName, 0, 1)) }}
                            </div>
                        @endif
                        <div class="position-absolute d-flex align-items-center justify-content-center bday-crown" style="width: 32px !important; height: 32px !important; border-radius: 50% !important; background: #FFB101; border: 2px solid #FFFFFF; box-shadow: 0 4px 10px rgba(0,0,0,0.3); bottom: -2px; right: -2px;">
                            👑
                        </div>
                    </div>

                    {{-- Cake Icon --}}
                    <div class="bday-cake-emoji" style="line-height: 1; filter: drop-shadow(0 4px 10px rgba(0,0,0,0.3));">
                        🎂
                    </div>

                    {{-- Wish Text --}}
                    <p class="px-1 px-sm-2 mb-2 bday-wish-text bday-space-y" style="color: rgba(255, 255, 255, 0.95); font-weight: 500;">
                        Wishing you joy, success and a fantastic year ahead from everyone at <strong>Orbosis Global Pvt. Ltd.</strong>! 🎉
                    </p>

                    <div class="font-weight-bold mb-1 bday-space-y" style="font-size: clamp(12px, 3vw, 14.5px); color: #FFD54F;">
                        &mdash; Team Orbosis Global
                    </div>
                </div>
            </div>

            <div class="px-3 px-sm-4 pb-3 text-center position-relative" style="z-index: 2;">
                {{-- SHARE WISH CARD SECTION --}}
                <div class="pt-2.5 pt-sm-3 mb-3 mb-sm-4 bday-share-container" style="border-top: 1px solid rgba(255, 255, 255, 0.18);">
                    
                    {{-- Download / Share HD Image Card Button --}}
                    <div class="mb-2.5">
                        <button type="button" onclick="exportBdayCardImage('#bdayExportableCard')" class="btn btn-sm px-3.5 py-1.5 font-weight-bold shadow-sm" style="border-radius: 50px; font-size: 11.5px; color: #3A00B5; background: #FFFFFF; border: none; transition: transform 0.2s ease;">
                            <i class="fas fa-image mr-1" style="color: #FFB101;"></i> Download / Share HD Image Card 📸
                        </button>
                    </div>

                    <div class="text-uppercase font-weight-bold mb-2.5" style="font-size: 10px; letter-spacing: 1.2px; color: rgba(255, 255, 255, 0.78);">
                        <i class="fas fa-share-alt mr-1"></i> DIRECT SOCIAL SHARE
                    </div>

                    <div class="d-flex align-items-center justify-content-center flex-wrap" style="gap: clamp(8px, 2.5vw, 12px);">
                        {{-- WhatsApp --}}
                        <a href="https://api.whatsapp.com/send?text={{ $shareText }}" target="_blank" class="d-flex flex-column align-items-center text-white text-decoration-none bday-share-item" style="width: 54px;">
                            <div class="d-flex align-items-center justify-content-center shadow-sm mb-1 bday-share-btn" style="border-radius: 50%; background: rgba(255,255,255,0.18); border: 1px solid rgba(255,255,255,0.3); transition: transform 0.2s ease;">
                                <i class="fab fa-whatsapp" style="color: #25D366;"></i>
                            </div>
                            <span style="font-size: 10px; font-weight: 600; opacity: 0.85;">WhatsApp</span>
                        </a>

                        {{-- LinkedIn --}}
                        <a href="https://www.linkedin.com/feed/?shareActive=true&text={{ $shareText }}" target="_blank" class="d-flex flex-column align-items-center text-white text-decoration-none bday-share-item" style="width: 54px;">
                            <div class="d-flex align-items-center justify-content-center shadow-sm mb-1 bday-share-btn" style="border-radius: 50%; background: rgba(255,255,255,0.18); border: 1px solid rgba(255,255,255,0.3);">
                                <i class="fab fa-linkedin-in" style="color: #0A66C2;"></i>
                            </div>
                            <span style="font-size: 10px; font-weight: 600; opacity: 0.85;">LinkedIn</span>
                        </a>

                        {{-- Facebook --}}
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}&quote={{ $shareText }}" target="_blank" class="d-flex flex-column align-items-center text-white text-decoration-none bday-share-item" style="width: 54px;">
                            <div class="d-flex align-items-center justify-content-center shadow-sm mb-1 bday-share-btn" style="border-radius: 50%; background: rgba(255,255,255,0.18); border: 1px solid rgba(255,255,255,0.3);">
                                <i class="fab fa-facebook-f" style="color: #1877F2;"></i>
                            </div>
                            <span style="font-size: 10px; font-weight: 600; opacity: 0.85;">Facebook</span>
                        </a>

                        {{-- Instagram --}}
                        <button type="button" onclick="shareBdayToInstagram()" class="btn p-0 d-flex flex-column align-items-center text-white text-decoration-none bday-share-item" style="width: 54px; background: none; border: none;">
                            <div class="d-flex align-items-center justify-content-center shadow-sm mb-1 bday-share-btn" style="border-radius: 50%; background: rgba(255,255,255,0.18); border: 1px solid rgba(255,255,255,0.3);">
                                <i class="fab fa-instagram" style="color: #E4405F;"></i>
                            </div>
                            <span style="font-size: 10px; font-weight: 600; opacity: 0.85;">Instagram</span>
                        </button>

                        {{-- Native Share / More --}}
                        <button type="button" onclick="exportBdayCardImage('#bdayExportableCard')" class="btn p-0 d-flex flex-column align-items-center text-white text-decoration-none bday-share-item" style="width: 54px; background: none; border: none;">
                            <div class="d-flex align-items-center justify-content-center shadow-sm mb-1 bday-share-btn" style="border-radius: 50%; background: rgba(255,255,255,0.18); border: 1px solid rgba(255,255,255,0.3);">
                                <i class="fas fa-share-alt" style="color: #F43F5E;"></i>
                            </div>
                            <span style="font-size: 10px; font-weight: 600; opacity: 0.85;">More</span>
                        </button>
                    </div>
                </div>

                {{-- Action Button --}}
                <button type="button" onclick="closeBirthdayModal()" class="btn btn-block font-weight-bold shadow-lg bday-action-btn">
                    Celebrate &amp; Continue 🎉
                </button>

            </div>
        </div>
    </div>
</div>

<script id="bdayShareDataConfig" type="application/json">
    {!! json_encode([
        'isOwnBday' => (bool)$isOwnBirthdayToday,
        'name' => $employeeName,
        'message' => $shareMessage,
        'url' => $shareUrl,
        'text' => $shareText
    ]) !!}
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
    let bdayAutoCloseTimer = null;

    function getBdayConfig() {
        try {
            const configElem = document.getElementById('bdayShareDataConfig');
            if (configElem && configElem.textContent) {
                return JSON.parse(configElem.textContent);
            }
        } catch (e) {}
        return { name: '', message: '', url: '', text: '' };
    }

    function closeBirthdayModal() {
        if (bdayAutoCloseTimer) {
            clearTimeout(bdayAutoCloseTimer);
            bdayAutoCloseTimer = null;
        }
        const modal = document.getElementById('ownBirthdayCelebrationModal');
        if (modal) {
            modal.style.opacity = '0';
            setTimeout(() => {
                modal.style.display = 'none';
                modal.classList.remove('show');
            }, 350);
        }
    }

    function showBdayToast(msg) {
        const toast = document.getElementById('bdayShareToast');
        if (toast) {
            if (msg) {
                toast.innerHTML = '<i class="fas fa-check-circle mr-1"></i> ' + msg;
            }
            toast.style.display = 'block';
            setTimeout(() => {
                toast.style.display = 'none';
            }, 3500);
        }
    }

    function copyBirthdayShareUrl(text, customMsg) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(() => {
                showBdayToast(customMsg || 'Wish Card Link Copied!');
            }).catch(err => {
                fallbackCopyText(text, customMsg);
            });
        } else {
            fallbackCopyText(text, customMsg);
        }
    }

    function fallbackCopyText(text, customMsg) {
        try {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.left = '-9999px';
            textarea.style.top = '-9999px';
            document.body.appendChild(textarea);
            textarea.focus();
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            showBdayToast(customMsg || 'Wish Card Link Copied!');
        } catch (e) {
            prompt('Copy Birthday Wish Card details:', text);
        }
    }

    function shareBdayToInstagram() {
        const config = getBdayConfig();
        copyBirthdayShareUrl(config.message, 'Wish Card Details & Link Copied! Open Instagram to paste.');
        setTimeout(() => {
            window.open('https://www.instagram.com', '_blank');
        }, 1200);
    }

    function exportBdayCardImage(selector) {
        const config = getBdayConfig();
        exportAndShareCardImage(selector || '.bday-modal-content', config.name, config.message, config.url);
    }

    function triggerNativeShare(name, text, url) {
        const config = getBdayConfig();
        const shareName = name || config.name;
        const shareText = text || config.message;
        const shareUrl = url || config.url;

        if (navigator.share) {
            navigator.share({
                title: shareName + "'s Birthday Celebration 🎉",
                text: shareText,
                url: shareUrl
            }).catch(() => {});
        } else {
            copyBirthdayShareUrl(shareText);
        }
    }

    function exportAndShareCardImage(selector, name, text, url) {
        showBdayToast('Generating HD Card Image... 🎨');
        const elem = document.querySelector(selector || '#bdayExportableCard');
        if (!elem) {
            triggerNativeShare(name, text, url);
            return;
        }

        if (typeof html2canvas === 'undefined') {
            triggerNativeShare(name, text, url);
            return;
        }

        // Create off-screen clone with exact 420px card width for pristine image download
        const clone = elem.cloneNode(true);
        clone.id = 'bdayExportClone';
        clone.style.position = 'fixed';
        clone.style.left = '-9999px';
        clone.style.top = '-9999px';
        clone.style.width = '420px';
        clone.style.maxWidth = '420px';
        clone.style.boxSizing = 'border-box';
        clone.style.borderRadius = '28px';
        clone.style.padding = '28px 24px 24px';
        clone.style.background = 'linear-gradient(135deg, #4F46E5 0%, #7C3AED 50%, #C026D3 100%)';
        
        // Strip out skip button, toasts, and share buttons from clone
        const skipBtn = clone.querySelector('.bday-skip-btn');
        if (skipBtn) skipBtn.remove();
        const toastElem = clone.querySelector('#bdayShareToast');
        if (toastElem) toastElem.remove();
        const shareCont = clone.querySelector('.bday-share-container');
        if (shareCont) shareCont.remove();
        const actionBtn = clone.querySelector('.bday-action-btn');
        if (actionBtn) actionBtn.remove();

        document.body.appendChild(clone);

        html2canvas(clone, {
            scale: 2,
            useCORS: true,
            allowTaint: true,
            backgroundColor: null,
            ignoreElements: (el) => el.tagName === 'CANVAS'
        }).then(canvas => {
            if (document.body.contains(clone)) document.body.removeChild(clone);
            canvas.toBlob(blob => {
                if (!blob) {
                    triggerNativeShare(name, text, url);
                    return;
                }
                const fileName = (name || 'Birthday_Wish').replace(/\s+/g, '_') + '_Card.png';
                const file = new File([blob], fileName, { type: 'image/png' });

                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                    navigator.share({
                        title: name + "'s Birthday Wish Card 🎉",
                        text: text,
                        files: [file]
                    }).then(() => {
                        showBdayToast('Card Image Shared Successfully! 🎉');
                    }).catch(() => {
                        downloadBlobFile(blob, fileName, text);
                    });
                } else {
                    downloadBlobFile(blob, fileName, text);
                }
            }, 'image/png');
        }).catch(err => {
            if (document.body.contains(clone)) document.body.removeChild(clone);
            console.error('Card image export failed:', err);
            triggerNativeShare(name, text, url);
        });
    }

    function downloadBlobFile(blob, fileName, text) {
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = fileName;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        showBdayToast('HD Card Image Downloaded & Wish Message Copied! 📸');
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text);
        }
    }

    function triggerAdminBdayModal(target) {
        if (!target) return;
        try {
            let empData = null;
            if (typeof target === 'object' && target !== null) {
                let raw = target.dataset ? (target.dataset.emp || target.getAttribute('data-emp')) : target;
                if (typeof raw === 'string') {
                    empData = JSON.parse(raw);
                } else {
                    empData = target;
                }
            } else {
                const elem = document.getElementById('bdayEmpData-' + target);
                if (elem && elem.textContent) {
                    empData = JSON.parse(elem.textContent);
                }
            }
            if (empData) {
                openAdminShareModal(empData);
            }
        } catch (e) {
            console.error('Trigger admin bday modal failed:', e);
        }
    }

    function openAdminShareModal(emp) {
        if (!emp) return;
        const modal = document.getElementById('ownBirthdayCelebrationModal');
        if (!modal) return;

        const nameElem = modal.querySelector('.bday-name');
        if (nameElem) nameElem.textContent = emp.name || 'Team Member';

        const deptElem = modal.querySelector('.bday-dept');
        if (deptElem) {
            let deptText = emp.department || 'Orbosis Global';
            if (emp.designation) deptText += ' • ' + emp.designation;
            deptElem.textContent = deptText;
        }

        const imgElem = modal.querySelector('.bday-avatar-img');
        const fallbackElem = modal.querySelector('.bday-avatar-fallback');
        if (imgElem) {
            if (emp.image_url) {
                imgElem.src = emp.image_url;
                imgElem.style.display = 'block';
                if (fallbackElem) fallbackElem.style.display = 'none';
            } else {
                imgElem.style.display = 'none';
                if (fallbackElem) {
                    fallbackElem.textContent = (emp.name || 'T').substring(0, 1).toUpperCase();
                    fallbackElem.style.display = 'flex';
                }
            }
        }

        let shareUrl = emp.share_url;
        if (!shareUrl && emp.share_token) {
            shareUrl = window.location.origin + '/birthday/' + emp.share_token;
        } else if (!shareUrl) {
            shareUrl = window.location.origin + '/birthday';
        }

        const shareMsg = "🎉 Happy Birthday " + (emp.name || 'Team Member') + "! 🎂✨\n\n" +
            "On behalf of Management & the entire team at Orbosis Global Pvt. Ltd., wishing you a fantastic birthday, happiness, success, and great achievements in the year ahead! 🚀\n\n" +
            "We are proud to have you as part of our team. Have a wonderful day! 🥳\n\n" +
            "— Team Orbosis Global\n\n" +
            "👉 View Your Birthday Wish Card:\n" + shareUrl;
        const shareTextEncoded = encodeURIComponent(shareMsg);

        const configElem = document.getElementById('bdayShareDataConfig');
        if (configElem) {
            configElem.textContent = JSON.stringify({
                name: emp.name || '',
                message: shareMsg,
                url: shareUrl,
                text: shareTextEncoded
            });
        }

        const waBtn = modal.querySelector('a[href*="whatsapp"]');
        if (waBtn) waBtn.href = "https://api.whatsapp.com/send?text=" + shareTextEncoded;

        const liBtn = modal.querySelector('a[href*="linkedin"]');
        if (liBtn) liBtn.href = "https://www.linkedin.com/feed/?shareActive=true&text=" + shareTextEncoded;

        const fbBtn = modal.querySelector('a[href*="facebook"]');
        if (fbBtn) fbBtn.href = "https://www.facebook.com/sharer/sharer.php?u=" + encodeURIComponent(shareUrl) + "&quote=" + shareTextEncoded;

        modal.style.display = 'block';
        modal.classList.add('show');
        modal.style.opacity = '1';

        if (typeof fireBdayConfetti === 'function') {
            fireBdayConfetti();
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        let isOwnBday = false;
        try {
            const configElem = document.getElementById('bdayShareDataConfig');
            if (configElem && configElem.textContent) {
                const cfg = JSON.parse(configElem.textContent);
                isOwnBday = !!cfg.isOwnBday;
            }
        } catch (e) {}

        if (isOwnBday) {
            fireBdayConfetti();

            bdayAutoCloseTimer = setTimeout(function() {
                closeBirthdayModal();
            }, 8000);
        }
    });

    document.addEventListener('click', function(e) {
        const btn = e.target ? e.target.closest('.btn-trigger-bday-card') : null;
        if (btn) {
            e.preventDefault();
            const bdayId = btn.getAttribute('data-bday-id');
            if (bdayId) {
                triggerAdminBdayModal(bdayId);
            }
        }
    });

    function fireBdayConfetti() {
        const canvas = document.getElementById('bdayConfettiCanvas');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        canvas.width = canvas.offsetWidth;
        canvas.height = canvas.offsetHeight;

        const pieces = [];
        const colors = ['#FFD54F', '#FF416C', '#25D366', '#00D2FF', '#9B51E0', '#FF8008'];

        for (let i = 0; i < 80; i++) {
            pieces.push({
                x: Math.random() * canvas.width,
                y: Math.random() * canvas.height - canvas.height,
                size: Math.random() * 8 + 4,
                color: colors[Math.floor(Math.random() * colors.length)],
                speed: Math.random() * 3 + 2,
                rotation: Math.random() * 360,
                rotSpeed: Math.random() * 6 - 3
            });
        }

        let animationFrame;
        function update() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            pieces.forEach(p => {
                p.y += p.speed;
                p.rotation += p.rotSpeed;
                if (p.y > canvas.height) {
                    p.y = -10;
                    p.x = Math.random() * canvas.width;
                }
                ctx.save();
                ctx.translate(p.x, p.y);
                ctx.rotate((p.rotation * Math.PI) / 180);
                ctx.fillStyle = p.color;
                ctx.fillRect(-p.size / 2, -p.size / 2, p.size, p.size);
                ctx.restore();
            });
            animationFrame = requestAnimationFrame(update);
        }
        update();

        setTimeout(() => {
            if (animationFrame) cancelAnimationFrame(animationFrame);
        }, 8000);
    }
</script>
@endif
