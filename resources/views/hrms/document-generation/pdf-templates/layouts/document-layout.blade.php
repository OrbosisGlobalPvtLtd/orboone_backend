<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Document')</title>

    @php
    $companySettings = \Illuminate\Support\Facades\DB::table('company_settings')->first();
    $companyPhone = $companySettings->phone ?? $company_phone ?? '+91-8770702092';
    $companyEmail = $companySettings->email ?? $company_email ?? 'info@orbosis.com';
    $companyGstin = $company_gstin ?? '23 AAECO8032D1ZU';
    $companyLocation = !empty($companySettings->address) ? $companySettings->address : trim(($company_city ?? 'Indore') . ', ' . ($company_state ?? 'Madhya Pradesh') . ', ' . ($company_country ?? 'India'), ', ');
    $companyWebsite = !empty($companySettings->website) ? preg_replace('#^https?://#i', '', $companySettings->website) : ($company_website ?? 'www.orbosis.com');

    // Dynamic header/logo path
    $headerSrc = null;
    if (!empty($companySettings->logo)) {
        $logoPath = storage_path('app/public/' . $companySettings->logo);
        if (is_file($logoPath)) {
            $headerSrc = ($isPreview ?? false)
                ? asset('storage/' . $companySettings->logo)
                : $logoPath;
        }
    }
    if (!$headerSrc) {
        $headerSrc = ($isPreview ?? false)
            ? asset('assets/hrms/document-letterhead/header.png')
            : public_path('assets/hrms/document-letterhead/header.png');
    }

    $footerSrc = ($isPreview ?? false)
        ? asset('assets/hrms/document-letterhead/footer.png')
        : public_path('assets/hrms/document-letterhead/footer.png');
    @endphp

    @if($isPreview ?? false)
    <style>
        body {
            background-color: #f1f5f9;
            margin: 0;
            padding: 24px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            font-family: 'DejaVu Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #111827;
            line-height: 1.55;
            overflow-x: hidden;
        }

        .preview-pages-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 24px;
            width: 100%;
            transition: transform 0.2s ease;
        }

        .a4-page {
            background: white;
            width: 210mm;
            min-height: 297mm;
            padding: 100px 42px 78px 42px;
            box-sizing: border-box;
            position: relative;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid #cbd5e1;
            border-radius: 4px;
        }

        .a4-page:empty {
            display: none !important;
        }

        .a4-header {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 95px;
        }

        .a4-header img {
            width: 100%;
            height: auto;
        }

        .a4-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 72px;
        }

        .a4-footer-strip img {
            width: 100%;
            height: auto;
            display: block;
        }

        .footer-contact {
            width: 100%;
            padding: 5px 38px 0 38px;
            box-sizing: border-box;
            color: #1B84A6;
            font-size: 10.5px;
            line-height: 1.35;
            font-weight: 500;
        }

        .footer-contact-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }

        .footer-contact-table td {
            border: none;
            padding: 0;
            vertical-align: top;
        }

        .footer-left {
            width: 33%;
            text-align: left;
        }

        .footer-center {
            width: 34%;
            text-align: center;
            font-size: 12px;
            padding-top: 8px !important;
            letter-spacing: .5px;
        }

        .footer-right {
            width: 33%;
            text-align: left;
        }

        .footer-row {
            white-space: nowrap;
        }

        .footer-icon {
            color: #1B84A6;
            font-weight: bold;
            display: inline-block;
            width: 16px;
        }

        .a4-content {
            width: 100%;
            position: relative;
        }

        .page-break:first-child {
            page-break-before: auto !important;
            break-before: auto !important;
        }

        .pdf-content>.page-break:first-child {
            page-break-before: auto !important;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 15px;
        }

        .table th,
        .table td {
            border: 1px solid #d1d5db;
            padding: 7px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-justify {
            text-align: justify;
        }

        .font-bold {
            font-weight: bold;
        }

        .mt-2 {
            margin-top: 8px;
        }

        .mt-4 {
            margin-top: 16px;
        }

        .mt-8 {
            margin-top: 32px;
        }

        .mb-2 {
            margin-bottom: 8px;
        }

        .mb-4 {
            margin-bottom: 16px;
        }

        .signature-section {
            margin-top: 40px;
            width: 100%;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }

        .signature-table td {
            border: none;
            padding: 0;
            width: 50%;
            vertical-align: bottom;
        }
    </style>
    @else
    <style>
        @page {
            size: A4;
            margin: 100px 42px 78px 42px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111827;
            line-height: 1.55;
        }

        .pdf-header {
            position: fixed;
            top: -100px;
            left: -42px;
            right: -42px;
            height: 95px;
        }

        .pdf-header img {
            width: 100%;
            height: auto;
        }

        .pdf-footer {
            position: fixed;
            bottom: -78px;
            left: -42px;
            right: -42px;
            height: 72px;
        }

        .pdf-footer-strip img {
            width: 100%;
            height: auto;
            display: block;
        }

        .footer-contact {
            width: 100%;
            padding: 5px 38px 0 38px;
            box-sizing: border-box;
            color: #1B84A6;
            font-size: 10.5px;
            line-height: 1.35;
            font-weight: 500;
        }

        .footer-contact-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }

        .footer-contact-table td {
            border: none;
            padding: 0;
            vertical-align: top;
        }

        .footer-left {
            width: 33%;
            text-align: left;
        }

        .footer-center {
            width: 34%;
            text-align: center;
            font-size: 12px;
            padding-top: 8px !important;
            letter-spacing: .5px;
        }

        .footer-right {
            width: 33%;
            text-align: left;
        }

        .footer-row {
            white-space: nowrap;
        }

        .footer-icon {
            color: #1B84A6;
            font-weight: bold;
            display: inline-block;
            width: 16px;
        }

        .pdf-content {
            margin-top: 0;
        }

        .page-break {
            page-break-before: always;
        }

        .page-break:first-child {
            page-break-before: auto !important;
            break-before: auto !important;
        }

        .pdf-content>.page-break:first-child {
            page-break-before: auto !important;
        }

        .avoid-break {
            page-break-inside: avoid;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 15px;
        }

        .table th,
        .table td {
            border: 1px solid #d1d5db;
            padding: 7px;
        }

        h1,
        h2,
        h3,
        h4 {
            color: #111827;
            margin-top: 0;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-justify {
            text-align: justify;
        }

        .font-bold {
            font-weight: bold;
        }

        .mt-2 {
            margin-top: 8px;
        }

        .mt-4 {
            margin-top: 16px;
        }

        .mt-8 {
            margin-top: 32px;
        }

        .mb-2 {
            margin-bottom: 8px;
        }

        .mb-4 {
            margin-bottom: 16px;
        }

        .signature-section {
            margin-top: 40px;
            width: 100%;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }

        .signature-table td {
            border: none;
            padding: 0;
            width: 50%;
            vertical-align: bottom;
        }
    </style>
    @endif

    @yield('styles')
</head>

<body>
    @if($isPreview ?? false)
    <div id="raw-content" style="display: none;">
        @yield('content')
    </div>

    <div id="pages-container" class="preview-pages-container"></div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const raw = document.getElementById("raw-content");
            const container = document.getElementById("pages-container");
            const headerSrc = "{{ $headerSrc }}";
            const footerSrc = "{{ $footerSrc }}";

            const footerHtml = `
                    <div class="a4-footer">
                        <div class="a4-footer-strip">
                            <img src="${footerSrc}" alt="Footer">
                        </div>
                        <div class="footer-contact">
                            <table class="footer-contact-table">
                                <tr>
                                    <td class="footer-left">
                                        <div class="footer-row"><span class="footer-icon">☎</span> {{ $companyPhone }}</div>
                                        <div class="footer-row"><span class="footer-icon">✉</span> {{ $companyEmail }}</div>
                                    </td>
                                    <td class="footer-center">
                                        {{ $companyGstin }}
                                    </td>
                                    <td class="footer-right">
                                        <div class="footer-row"><span class="footer-icon">📍</span> {{ $companyLocation }}</div>
                                        <div class="footer-row"><span class="footer-icon">🌐</span> {{ $companyWebsite }}</div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                `;

            let currentPage = createNewPage();
            let currentContent = currentPage.querySelector(".a4-content");

            let children = [];
            if (raw.children.length === 1 && raw.children[0].className === "letter-body") {
                children = Array.from(raw.children[0].children);
            } else {
                children = Array.from(raw.children);
            }

            children.forEach(el => {
                if (el.classList.contains("page-break")) {
                    if (currentContent.children.length > 0) {
                        currentPage = createNewPage();
                        currentContent = currentPage.querySelector(".a4-content");
                    }
                    return;
                }

                currentContent.appendChild(el);
            });

            Array.from(container.children).forEach(page => {
                const content = page.querySelector(".a4-content");
                if (!content || content.children.length === 0) {
                    page.remove();
                }
            });

            function createNewPage() {
                const page = document.createElement("div");
                page.className = "a4-page";
                page.innerHTML = `
                        <div class="a4-header">
                            <img src="${headerSrc}" alt="Header">
                        </div>
                        <div class="a4-content"></div>
                        ${footerHtml}
                    `;
                container.appendChild(page);
                return page;
            }

            function adjustScale() {
                const width = window.innerWidth;
                const pageEl = document.querySelector(".a4-page");
                if (!pageEl) return;

                const pageWidth = 794;
                if (width < pageWidth + 32) {
                    const scale = (width - 32) / pageWidth;
                    container.style.transform = `scale(${scale})`;
                    container.style.transformOrigin = "top center";

                    const origHeight = container.getBoundingClientRect().height / scale;
                    const lostHeight = (1 - scale) * origHeight;
                    container.style.marginBottom = `-${lostHeight}px`;
                } else {
                    container.style.transform = "";
                    container.style.transformOrigin = "";
                    container.style.marginBottom = "";
                }
            }

            setTimeout(adjustScale, 50);
            window.addEventListener("resize", adjustScale);
        });
    </script>
    @else
    <div class="pdf-header">
        <img src="{{ $headerSrc }}" alt="Header">
    </div>

    <div class="pdf-footer">
        <div class="pdf-footer-strip">
            <img src="{{ $footerSrc }}" alt="Footer">
        </div>

        <div class="footer-contact">
            <table class="footer-contact-table">
                <tr>
                    <td class="footer-left">
                        <div class="footer-row"><span class="footer-icon">☎</span> {{ $companyPhone }}</div>
                        <div class="footer-row"><span class="footer-icon">✉</span> {{ $companyEmail }}</div>
                    </td>

                    <td class="footer-center">
                        {{ $companyGstin }}
                    </td>

                    <td class="footer-right">
                        <div class="footer-row"><span class="footer-icon">📍</span> {{ $companyLocation }}</div>
                        <div class="footer-row"><span class="footer-icon">🌐</span> {{ $companyWebsite }}</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="pdf-content">
        @yield('content')
    </div>
    @endif
</body>

</html>