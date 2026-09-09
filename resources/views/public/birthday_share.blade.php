<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $employee['name'] }}'s Birthday Celebration | Orbosis Global Pvt. Ltd.</title>

    <!-- Primary Meta Tags -->
    <meta name="title" content="{{ $employee['name'] }}'s Birthday Celebration 🎉">
    <meta name="description" content="Celebrating a wonderful birthday with Orbosis Global Pvt. Ltd.! Wishing {{ $employee['name'] }} a year ahead filled with joy, happiness, and success. 🎂✨">

    <!-- Open Graph / Facebook / WhatsApp -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $shareUrl }}">
    <meta property="og:title" content="{{ $employee['name'] }}'s Birthday Celebration 🎉">
    <meta property="og:description" content="Celebrating a wonderful birthday with Orbosis Global Pvt. Ltd.! Wishing {{ $employee['name'] }} a fantastic year ahead. 🎂✨">
    <meta property="og:image" content="{{ $imageUrl }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="Orbosis Global Pvt. Ltd.">

    <!-- Twitter / X -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ $shareUrl }}">
    <meta name="twitter:title" content="{{ $employee['name'] }}'s Birthday Celebration 🎉">
    <meta name="twitter:description" content="Celebrating a wonderful birthday with Orbosis Global Pvt. Ltd.! 🎂✨">
    <meta name="twitter:image" content="{{ $imageUrl }}">

    <!-- FontAwesome & Google Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        body {
            min-height: 100vh;
            background: #0f0c2a;
            background: radial-gradient(circle at center, #2e1065 0%, #0f0c2a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            color: #FFFFFF;
            position: relative;
            overflow-x: hidden;
        }

        .card-container {
            max-width: 440px;
            width: 100%;
            margin: 20px auto;
            background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 50%, #C026D3 100%);
            border: 1.5px solid rgba(255, 255, 255, 0.28);
            border-radius: 32px;
            padding: 36px 26px 30px;
            text-align: center;
            box-shadow: 0 30px 70px rgba(124, 58, 237, 0.45);
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }

        .company-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 213, 79, 0.22);
            border: 1px solid rgba(255, 213, 79, 0.65);
            padding: 6px 18px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.8px;
            color: #FFD54F;
            text-transform: uppercase;
            margin-bottom: 20px;
        }

        .emoji-banner {
            font-size: 36px;
            margin-bottom: 6px;
            filter: drop-shadow(0 4px 10px rgba(0,0,0,0.3));
        }

        .title {
            font-size: 32px;
            font-weight: 900;
            line-height: 1.15;
            color: #FFFFFF;
            margin-bottom: 4px;
            text-shadow: 0 4px 14px rgba(0, 0, 0, 0.3);
            letter-spacing: -0.5px;
        }

        .employee-name {
            font-size: 24px;
            font-weight: 800;
            color: #FFD54F;
            margin-bottom: 4px;
            text-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }

        .department {
            font-size: 13px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.88);
            margin-bottom: 22px;
        }

        .avatar-box {
            position: relative;
            width: 105px !important;
            height: 105px !important;
            margin: 0 auto 16px !important;
        }

        .avatar-img {
            width: 105px !important;
            height: 105px !important;
            border-radius: 50% !important;
            object-fit: cover !important;
            border: 3.5px solid #FFD54F !important;
            box-shadow: 0 8px 25px rgba(255, 213, 79, 0.45);
        }

        .avatar-fallback {
            width: 105px !important;
            height: 105px !important;
            border-radius: 50% !important;
            background: rgba(255, 255, 255, 0.22);
            border: 3.5px solid #FFD54F !important;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 38px;
            font-weight: 900;
            color: #FFFFFF;
            box-shadow: 0 8px 25px rgba(255, 213, 79, 0.45);
        }

        .crown-badge {
            position: absolute;
            bottom: -2px;
            right: -2px;
            font-size: 18px;
            background: #FFB101;
            border: 2px solid #FFFFFF;
            border-radius: 50%;
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        }

        .cake-emoji {
            font-size: 42px;
            margin-bottom: 12px;
            filter: drop-shadow(0 4px 10px rgba(0,0,0,0.3));
        }

        .message {
            font-size: 14.5px;
            line-height: 1.5;
            color: rgba(255, 255, 255, 0.95);
            font-weight: 500;
            margin-bottom: 12px;
        }

        .team-signoff {
            font-size: 14.5px;
            font-weight: 800;
            color: #FFD54F;
            margin-bottom: 24px;
        }

        /* Share Section */
        .share-section {
            border-top: 1px solid rgba(255, 255, 255, 0.18);
            padding-top: 20px;
            margin-bottom: 20px;
        }

        .share-title {
            font-size: 10.5px;
            font-weight: 800;
            letter-spacing: 1.2px;
            color: rgba(255, 255, 255, 0.8);
            text-transform: uppercase;
            margin-bottom: 14px;
        }

        .share-grid {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        .share-btn-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            text-decoration: none;
            color: #ffffff;
            width: 54px;
            background: none;
            border: none;
            cursor: pointer;
        }

        .share-icon-circle {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            transition: transform 0.2s ease, background 0.2s ease;
        }

        .share-btn-wrapper:hover .share-icon-circle {
            transform: translateY(-2px);
            background: rgba(255, 255, 255, 0.3);
        }

        .share-label {
            font-size: 10px;
            font-weight: 600;
            opacity: 0.85;
        }

        .action-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 20px;
        }

        .btn-download-hd {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            width: 100%;
            padding: 14px 20px;
            background: #FFFFFF;
            color: #3A00B5;
            font-size: 15px;
            font-weight: 800;
            border-radius: 18px;
            border: none;
            cursor: pointer;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-download-hd:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 30px rgba(0, 0, 0, 0.35);
        }

        .btn-visit-company {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 13px 20px;
            background: rgba(255, 255, 255, 0.18);
            color: #FFFFFF;
            font-size: 14px;
            font-weight: 700;
            border-radius: 18px;
            border: 1.5px solid rgba(255, 255, 255, 0.35);
            text-decoration: none;
            transition: transform 0.2s ease, background 0.2s ease;
        }

        .btn-visit-company:hover {
            transform: translateY(-2px);
            background: rgba(255, 255, 255, 0.28);
            color: #FFFFFF;
            text-decoration: none;
        }

        .toast {
            display: none;
            position: fixed;
            top: 24px;
            left: 50%;
            transform: translateX(-50%);
            background: #10B981;
            color: #ffffff;
            font-size: 13px;
            font-weight: 800;
            padding: 10px 22px;
            border-radius: 50px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.35);
            z-index: 9999;
        }
    </style>
</head>
<body>

    <div id="toast" class="toast">
        <i class="fas fa-check-circle mr-1"></i> Wish Card Link Copied!
    </div>

    <div class="card-container position-relative">
        <canvas id="confettiCanvas" style="position: absolute; top:0; left:0; width:100%; height:100%; pointer-events:none; z-index:1;"></canvas>

        <div style="position: relative; z-index: 2;">
            <div class="company-badge">
                ⚡ ORBOSIS GLOBAL PVT. LTD.
            </div>

            <div class="emoji-banner">🎉 🎁 🎊</div>

            <h1 class="title">Happy Birthday</h1>
            <div class="employee-name">{{ $employee['name'] }}</div>
            <div class="department">
                {{ $employee['department'] ?? 'Orbosis Global' }}
                @if(!empty($employee['designation'])) &bull; {{ $employee['designation'] }} @endif
            </div>

            <div class="avatar-box">
                @if(!empty($employee['image_url']))
                    <img class="avatar-img" src="{{ $employee['image_url'] }}" alt="{{ $employee['name'] }}" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                    <div class="avatar-fallback" style="display: none;">{{ strtoupper(substr($employee['name'], 0, 1)) }}</div>
                @else
                    <div class="avatar-fallback">{{ strtoupper(substr($employee['name'], 0, 1)) }}</div>
                @endif
                <div class="crown-badge">👑</div>
            </div>

            <div class="cake-emoji">🎂</div>

            <p class="message">
                Wishing you joy, success and a fantastic year ahead from everyone at <strong>Orbosis Global Pvt. Ltd.</strong>! 🎉
            </p>

            <div class="team-signoff">— Team Orbosis Global</div>

            <!-- SHARE WISH CARD SECTION -->
            <div class="share-section">
                <div class="share-title">
                    <i class="fas fa-share-alt mr-1"></i> SHARE WISH CARD
                </div>

                @php
                    $shareMessage = "🎉 Happy Birthday " . $employee['name'] . "! 🎂✨\n\n" .
                        "On behalf of Management & the entire team at Orbosis Global Pvt. Ltd., wishing you a fantastic birthday, happiness, success, and great achievements in the year ahead! 🚀\n\n" .
                        "We are proud to have you as part of our team. Have a wonderful day! 🥳\n\n" .
                        "— Team Orbosis Global\n\n" .
                        "👉 View Your Birthday Wish Card:\n" .
                        $shareUrl;

                    $shareText = rawurlencode($shareMessage);
                @endphp

                <div class="share-grid">
                    <!-- WhatsApp -->
                    <a href="https://api.whatsapp.com/send?text={{ $shareText }}" target="_blank" class="share-btn-wrapper">
                        <div class="share-icon-circle">
                            <i class="fab fa-whatsapp" style="color: #25D366;"></i>
                        </div>
                        <span class="share-label">WhatsApp</span>
                    </a>

                    <!-- LinkedIn -->
                    <a href="https://www.linkedin.com/feed/?shareActive=true&text={{ $shareText }}" target="_blank" class="share-btn-wrapper">
                        <div class="share-icon-circle">
                            <i class="fab fa-linkedin-in" style="color: #0A66C2;"></i>
                        </div>
                        <span class="share-label">LinkedIn</span>
                    </a>

                    <!-- Facebook -->
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}&quote={{ $shareText }}" target="_blank" class="share-btn-wrapper">
                        <div class="share-icon-circle">
                            <i class="fab fa-facebook-f" style="color: #1877F2;"></i>
                        </div>
                        <span class="share-label">Facebook</span>
                    </a>

                    <!-- Instagram -->
                    <button type="button" onclick="shareToInstagram()" class="share-btn-wrapper">
                        <div class="share-icon-circle">
                            <i class="fab fa-instagram" style="color: #E4405F;"></i>
                        </div>
                        <span class="share-label">Instagram</span>
                    </button>

                    <!-- Native Share -->
                    <button type="button" onclick="exportCardImage()" class="share-btn-wrapper">
                        <div class="share-icon-circle">
                            <i class="fas fa-share-alt" style="color: #F43F5E;"></i>
                        </div>
                        <span class="share-label">More</span>
                    </button>
                </div>
            </div>

            <!-- Action Buttons Group -->
            <div class="action-group">
                <button type="button" onclick="exportCardImage()" class="btn-download-hd">
                    <i class="fas fa-image" style="color: #FFB101;"></i> Download / Share HD Card Image 📸
                </button>

                <a href="{{ $companyWebsite }}" class="btn-visit-company" target="_blank">
                    Visit Orbosis Global 🚀
                </a>
            </div>
        </div>
    </div>

    <script id="publicBdayDataConfig" type="application/json">
        {!! json_encode([
            'name' => $employee['name'] ?? '',
            'message' => $shareMessage ?? '',
            'url' => $shareUrl ?? '',
            'text' => $shareText ?? ''
        ]) !!}
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        function getPublicBdayConfig() {
            try {
                const configElem = document.getElementById('publicBdayDataConfig');
                if (configElem && configElem.textContent) {
                    return JSON.parse(configElem.textContent);
                }
            } catch (e) {}
            return { name: '', message: '', url: '', text: '' };
        }

        function showToast(msg) {
            const toast = document.getElementById('toast');
            if (toast) {
                if (msg) toast.innerHTML = '<i class="fas fa-check-circle mr-1"></i> ' + msg;
                toast.style.display = 'block';
                setTimeout(() => {
                    toast.style.display = 'none';
                }, 3500);
            }
        }

        function copyUrl(text, customMsg) {
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(() => {
                    showToast(customMsg || 'Wish Card Link Copied!');
                }).catch(() => fallbackCopy(text, customMsg));
            } else {
                fallbackCopy(text, customMsg);
            }
        }

        function fallbackCopy(text, customMsg) {
            try {
                const textarea = document.createElement('textarea');
                textarea.value = text;
                textarea.style.position = 'fixed';
                textarea.style.left = '-9999px';
                document.body.appendChild(textarea);
                textarea.focus();
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                showToast(customMsg || 'Wish Card Link Copied!');
            } catch (e) {
                prompt('Copy Birthday Wish Details:', text);
            }
        }

        function shareToInstagram() {
            const config = getPublicBdayConfig();
            copyUrl(config.message, 'Wish Details & Link Copied! Open Instagram to paste.');
            setTimeout(() => {
                window.open('https://www.instagram.com', '_blank');
            }, 1200);
        }

        function exportCardImage() {
            const config = getPublicBdayConfig();
            exportAndShareCardImage('.card-container', config.name, config.message, config.url);
        }

        function triggerNativeShare(name, text, url) {
            const config = getPublicBdayConfig();
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
                copyUrl(shareText);
            }
        }

        function exportAndShareCardImage(selector, name, text, url) {
            showToast('Generating HD Card Image... 🎨');
            const elem = document.querySelector(selector || '.card-container');
            if (!elem) {
                triggerNativeShare(name, text, url);
                return;
            }

            if (typeof html2canvas === 'undefined') {
                triggerNativeShare(name, text, url);
                return;
            }

            // Create off-screen clone with exact 420px card width
            const clone = elem.cloneNode(true);
            clone.id = 'publicBdayExportClone';
            clone.style.position = 'fixed';
            clone.style.left = '-9999px';
            clone.style.top = '-9999px';
            clone.style.width = '420px';
            clone.style.maxWidth = '420px';
            clone.style.boxSizing = 'border-box';
            clone.style.borderRadius = '32px';
            clone.style.padding = '32px 24px';
            clone.style.background = 'linear-gradient(135deg, #4F46E5 0%, #7C3AED 50%, #C026D3 100%)';

            const shareSec = clone.querySelector('.share-section');
            if (shareSec) shareSec.remove();
            const actionBtns = clone.querySelectorAll('.action-btn, .mb-3');
            actionBtns.forEach(btn => btn.remove());

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
                            showToast('Card Image Shared Successfully! 🎉');
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
            copyUrl(text, 'HD Card Image Downloaded & Wish Message Copied! 📸');
        }

        // Confetti Animation
        document.addEventListener('DOMContentLoaded', function() {
            const canvas = document.getElementById('confettiCanvas');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            canvas.width = canvas.offsetWidth;
            canvas.height = canvas.offsetHeight;

            const pieces = [];
            const colors = ['#FFD54F', '#FF416C', '#25D366', '#00D2FF', '#9B51E0', '#FF8008'];

            for (let i = 0; i < 90; i++) {
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
                requestAnimationFrame(update);
            }
            update();
        });
    </script>
</body>
</html>

